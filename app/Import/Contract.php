<?php

namespace App\Import;


use App\Jobs\GetContractCredentials;
use App\Model\Award;
use App\Model\ContractChange;
use App\Model\ContractDocuments;
use App\Model\RationaleType;
use App\Model\Tender;
use Carbon\Carbon;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class Contract
{
    use DispatchesJobs;

    protected $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    public function process()
    {
        $data = $this->_data;
        try {
            DB::beginTransaction();
            $tender = Tender::where('cbd_id', $data['tender_id'])->first();
            if ($tender) {
                $contract = \App\Model\Contract::where('cbd_id', $data['id'])->where('tender_id', $tender->id)->first();
                if ($tender->source == 1 && $contract->access_token == '') {
                    $this->dispatch(new GetContractCredentials($contract->cbd_id));
                }
                $contractData = [
                    'cbd_id' => $data['id'],
                    'award_id' => Award::where('cbd_id', $data['awardID'])->where('tender_id', $tender->id)->first()->id,
                    'tender_id' => $tender->id,
                    'contractID' => $data['contractID'],
                    'contract_number' => $data['contractNumber'],
                    'status' => $data['status'],
                    'amount' => isset($data['amount']) ? $data['amount'] : 0,
                    'amount_paid' => isset($data['amountPaid']) ? $data['amountPaid']['amount'] : 0,
                    'period_date_start' => isset($data['period']['startDate']) ? $data['period']['startDate'] : null,
                    'period_date_end' => isset($data['period']['endDate']) ? $data['period']['endDate'] : null,
                    'date_signed' => isset($data['dateSigned']) ? $data['dateSigned'] : null,
                    'termination_details' => isset($data['termination_details']) ? $data['termination_details'] : '',
                    'date' => $data['dateModified']
                ];
                if ($contract) {
                    $contract->update($contractData);
                } else {
                    $contract = new \App\Model\Contract($contractData);
                    $contract->save();
                }

                $changeModels = [];

                if (isset($data['changes'])) {
                    foreach ($data['changes'] as $change) {
                        $changeData = [
                            'tender_id' => $tender->id,
                            'cbd_id' => $change['id'],
                            'status' => $change['status'],
                            'rationale' => $change['rationale'],
                            'rationale_type_id' => RationaleType::where('name', $change['rationaleTypes'][0])->first(),
                            'date_signed' => $change['dateSigned'],
                            'date' => $change['date'],
                        ];
                        $changeModel = ContractChange::where('cbd_id', $change['id'])->where('contract_id', $contract->id)->first();
                        if (!$changeModel) {
                            $changeModel = new ContractChange($changeData);
                            $contract->changes()->save($changeModel);
                        } else {
                            $changeModel->update($changeData);
                        }
                        $changeModels[$change['id']] = $changeModel;
                    }
                }

                if (isset($data['documents'])) {
                    foreach ($data['documents'] as $document) {
                        $doc = $contract->documents()->where('orig_id', $document['id'])->first();
                        $params = [
                            'format' => $document['format'],
                            'orig_id' => $document['id'],
                            'title' => $document['title'],
                            'url' => $document['url'],
                            'document_of' => $document['documentOf'] == 'change' ? 'change' : '',
                            'change_id' => $document['documentOf'] == 'change' ? $changeModels[$document['documentOf']] : 0,
                            'date_published' => Carbon::parse($document['datePublished'])->format('Y-m-d H:i:s'),
                            'date_modified' => Carbon::parse($document['dateModified'])->format('Y-m-d H:i:s'),
                        ];

                        if (!$doc) {
                            $contract->documents()->save(new ContractDocuments($params));
                        } else {
                            $doc->update($params);
                        }

                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            var_dump($e->getMessage());
        }

        return false;
    }
}