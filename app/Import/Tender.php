<?php

namespace App\Import;

use App\Events\BidDocUploadEvent;
use App\Events\TenderChangeStatusEvent;
use App\Jobs\SyncBid;
use App\Jobs\SyncTender;
use App\Model\Award;
use App\Model\AwardDocuments;
use App\Model\Bid;
use App\Model\BidDocuments;
use App\Model\CancellationDocuments;
use App\Model\Complaint;
use App\Model\ComplaintDocument;
use App\Model\Contract;
use App\Model\ContractDocuments;
use App\Model\Currencies;
use App\Model\Document;
use App\Model\Feature;
use App\Model\FeatureValue;
use App\Model\Item;
use App\Model\Lot;
use App\Model\Notification;
use App\Model\ProcedureTypes;
use App\Model\Qualification;
use App\Model\QualificationDocuments;
use App\Model\Question;
use App\Model\TenderContacts;
use App\Model\TenderStages;
use App\Model\Units;
use App\Services\NotificationService\Model\NotificationTemplate;
use App\Services\NotificationService\NotificationService;
use App\Services\NotificationService\Tags;
use Carbon\Carbon;
use Event;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class Tender
{
    const DEFAULT_LANGUAGE = 'ua';
    use DispatchesJobs;

    protected $_data;

    public function __construct($data)
    {
        $this->_data = $data;
    }

    protected function _importAwards($tenderModel)
    {
        foreach ($this->_data['awards'] as $award) {
            if (isset($award['bid_id'])) {
                if (isset($award['lotID'])) {
                    $bid = $tenderModel->lots()->where('cbd_id', $award['lotID'])->first()->bids()->where('cbd_id', $award['bid_id'])->first();
                } else {
                    $bid = $tenderModel->bids()->where('cbd_id', $award['bid_id'])->first();
                }
            }

            $awardModel = Award::where('cbd_id', $award['id'])->where('tender_id', $tenderModel->id)->first();
            $awardData = [
                'cbd_id' => $award['id'],
                'tender_id' => $tenderModel->id,
                'amount' => $award['value']['amount'],
                'tax_included' => $award['value']['valueAddedTaxIncluded'],
                'status' => $award['status'],
                'currency_id' => Currencies::where('currency_code', $award['value']['currency'])->first()->id,
                'created_at' => date('Y-m-d H:i:s', strtotime($award['date'])),
                'complaint_date_start' => isset($award['complaintPeriod']['startDate']) ? date('Y-m-d H:i:s', strtotime($award['complaintPeriod']['startDate'])) : null,
                'complaint_date_end' => isset($award['complaintPeriod']['endDate']) ? $award['complaintPeriod']['endDate'] : null,
                'date' => isset($award['date']) ? Carbon::parse($award['date'])->format('Y-m-d H:i:s') : null,
            ];
            if (isset($bid)) {
                $awardData['bid_id'] = $bid->id;
            }

            /**
             *  у звита нет предложений поэтому в эворд нужно записать хотя бы  организацию так как  условие
             * в /resources/views/pages/contract/detail.blade.php на строке 43, а именно
             * $organization = $contract->award->bid ? $contract->award->bid->organization : $contract->award->organization)
             * не дает возможность просмотреть победителя
             */
            if($tenderModel->type_id == 4) {

                $organization  = \App\Import\Organization::getModel($award['suppliers'][0]);

                if($organization) {
                    $awardData['organization_id'] = $organization->id;
                }
            }

            if (!$awardModel) {
                $awardModel = new Award($awardData);
                $tenderModel->award()->save($awardModel);
                if (isset($bid)) {
                    foreach ($bid->documents as $document) {
                        if (empty($document->url)) {
                            Event::fire(new BidDocUploadEvent($document));
                        }
                    }
                }

            } else {
                $awardModel->update($awardData);
            }
            if (isset($award['complaints'])) {

                $this->_importComplaints($award['complaints'], $tenderModel, $awardModel);
                foreach ($award['complaints'] as $complaint) {
                    $complaintModel = $awardModel->complaint;
                    $params = [
                        'status' => $complaint['status']
                    ];
                    if ($complaintModel) {
                        $complaintModel->update($params);
                    }
                }
            }


            if (!empty($award['documents'])) {
                foreach ($award['documents'] as $awardDocumentData) {
                    $doc = $awardModel->documents()->where('orig_id', $awardDocumentData['id'])->first();
                    $params = [
                        'document_parent_id' => 0,
                        'format' => $awardDocumentData['format'],
                        'orig_id' => $awardDocumentData['id'],
                        'title' => $awardDocumentData['title'],
                        'url' => $awardDocumentData['url'],
                        'date_published' => Carbon::parse($awardDocumentData['datePublished'])->format('Y-m-d H:i:s'),
                        'date_modified' => Carbon::parse($awardDocumentData['dateModified'])->format('Y-m-d H:i:s'),
                    ];

                    if (!$doc) {
                        $awardModel->documents()->save(new AwardDocuments($params));
                    } else {
                        $doc->update($params);
                    }

                    $searchByLen = (strlen($awardDocumentData['url']) > 255) ? true : false;
                    $awardModel->documents()->where('orig_id', $awardDocumentData['id'])
                        ->where(function ($query) use ($searchByLen) {
                            $query->where('url', 'LIKE', 'https://lb.api%');
                            if ($searchByLen)
                                $query->orWhereRaw('LENGTH(url) = 255');
                        })
                        ->delete();
                }
            }

        }
    }

    protected function _importContracts($tenderModel)
    {
        foreach ($this->_data['contracts'] as $contract) {
            $awardModel = Award::where('cbd_id', $contract['awardID'])->where('tender_id', $tenderModel->id)->first();
            $contractData = [
                'title' => isset($contract['title']) ? $contract['title'] : '',
                'cbd_id' => $contract['id'],
                'contractID' => isset($contract['contractID']) ? $contract['contractID'] : '',
                'award_id' => $awardModel->id,
                'tender_id' => $tenderModel->id,
                'status' => $contract['status'],
                'amount' => isset($contract['value']['amount']) ? $contract['value']['amount'] : 0,
                'period_date_start' => isset($award['period']['startDate']) ? $award['period']['startDate'] : null,
                'period_date_end' => isset($award['period']['endDate']) ? $award['period']['endDate'] : null,
                'date' => isset($contract['date']) ? Carbon::parse($contract['date'])->format('Y-m-d H:i:s') : null,
            ];

            $contractModel = $awardModel->contract()->where('cbd_id', $contract['id'])->first();
            if (!$contractModel) {
                $contractModel = new Contract($contractData);
                $awardModel->contract()->save($contractModel);
            } else {
                if ($contractModel->status != 'terminated') {
                    $contractModel->update($contractData);
                } else {
                    continue;
                }
            }

            if (!empty($contract['documents'])) {
                foreach ($contract['documents'] as $contractDocumentData) {
                    $doc = $contractModel->documents()->where('orig_id', $contractDocumentData['id'])->first();
                    $params = [
                        'document_parent_id' => 0,
                        'format' => $contractDocumentData['format'],
                        'orig_id' => $contractDocumentData['id'],
                        'title' => $contractDocumentData['title'],
                        'url' => $contractDocumentData['url'],
                        'date_published' => Carbon::parse($contractDocumentData['datePublished'])->format('Y-m-d H:i:s'),
                        'date_modified' => Carbon::parse($contractDocumentData['dateModified'])->format('Y-m-d H:i:s'),
                    ];
                    if (!$doc) {
                        $contractModel->documents()->save(new ContractDocuments($params));
                    } else {
                        $doc->update($params);
                    }

                    $searchByLen = (strlen($contractDocumentData['url']) > 255) ? true : false;
                    $contractModel->documents()->where('orig_id', $contractDocumentData['id'])
                        ->where(function ($query) use ($searchByLen) {
                            $query->where('url', 'LIKE', 'https://lb.api%');
                            if ($searchByLen)
                                $query->orWhereRaw('LENGTH(url) = 255');
                        })
                        ->delete();
                }
            }
        }
    }

    protected function _importBids($tender)
    {
        foreach ($this->_data['bids'] as $bid) {
            if (!isset($bid['tenderers'][0])) {
                continue;
            }
            $organization = Organization::getModel($bid['tenderers'][0]);

            $bidData = [
                'status' => isset($bid['status']) ? $bid['status'] : '',
                'participation_url' => isset($bid['participationUrl']) ? $bid['participationUrl'] : '',
                'cbd_id' => $bid['id'],
                'tender_id' => $tender->id
            ];

            $paramIds = [];
            if (isset($bid['parameters'])) {
                foreach ($bid['parameters'] as $bidParameter) {
                    if ($feature = Feature::with('values')->where('cbd_id', $bidParameter['code'])->where('tender_id', $tender->id)->first())
                        if ($value = $feature->values()->where('value', round($bidParameter['value'] * 10000))->first())
                            $paramIds[] = $value->id;
                }
            }

            if (isset($bid['lotValues'])) {
                foreach ($bid['lotValues'] as $lotValue) {
                    if (isset($lotValue['value'])) {
                        $bidData['amount'] = $lotValue['value']['amount'];
                        $bidData['tax_included'] = $lotValue['value']['valueAddedTaxIncluded'];
                        $bidData['currency_id'] = Currencies::where('currency_code', $lotValue['value']['currency'])->first()->id;
                    }
                    $bidData['participation_url'] = isset($lotValue['participationUrl']) ? $lotValue['participationUrl'] : '';

                    $lotModel = Lot::where('cbd_id', $lotValue['relatedLot'])->where('tender_id', $tender->id)->first();
                    $bidModel = $lotModel->bids()->where('cbd_id', $bid['id'])->first();

                    if (!$bidModel) {
                        $bidModel = new Bid($bidData);
                        $lotModel->bids()->save($bidModel);
                        $organization->bids()->save($bidModel);
                    } else {
                        $bidModel->update($bidData);
                    }
                    if (!empty($paramIds)) {
                        $bidModel->values()->sync($paramIds);
                    }
                }
            } else {
                if (isset($bid['value'])) {
                    $bidData['amount'] = $bid['value']['amount'];
                    $bidData['tax_included'] = $bid['value']['valueAddedTaxIncluded'];
                    $bidData['currency_id'] = Currencies::where('currency_code', $bid['value']['currency'])->first()->id;
                }

                $bidModel = Bid::where('cbd_id', $bid['id'])->first();
                if (!$bidModel) {
                    $bidModel = new Bid($bidData);
                    $tender->bids()->save($bidModel);
                    $organization->bids()->save($bidModel);
                } else {
                    $bidModel->update($bidData);
                }
                if (!empty($paramIds)) {
                    $bidModel->values()->sync($paramIds);
                }
            }


            if (!empty($bid['documents'])) {
                foreach ($bid['documents'] as $bidDocumentData) {
                    $doc = $bidModel->documents()->where('orig_id', $bidDocumentData['id'])->first();
                    $params = [
                        'document_parent_id' => 0,
                        'format' => $bidDocumentData['format'],
                        'orig_id' => $bidDocumentData['id'],
                        'title' => $bidDocumentData['title'],
                        'url' => $bidDocumentData['url'],
                        'date_published' => Carbon::parse($bidDocumentData['datePublished'])->format('Y-m-d H:i:s'),
                        'date_modified' => Carbon::parse($bidDocumentData['dateModified'])->format('Y-m-d H:i:s'),
                    ];
                    if (isset($bidDocumentData['confidentiality']) && $bidDocumentData['confidentiality'] == 'buyerOnly') {
                        $params['confidential'] = 1;
                        $params['confidential_cause'] = (array_key_exists('confidentialityRationale', $bidDocumentData)) ? $bidDocumentData['confidentialityRationale'] : '';
                    } else {
                        $params['confidential'] = 0;
                        $params['confidential_cause'] = null;
                    }
                    if (!$doc) {
                        $bidModel->documents()->save(new BidDocuments($params));
                    } else {
                        $doc->update($params);
                    }

                    $searchByLen = (strlen($bidDocumentData['url']) > 255) ? true : false;
                    $bidModel->documents()->where('orig_id', $bidDocumentData['id'])
                        ->where(function ($query) use ($searchByLen) {
                            $query->where('url', 'LIKE', 'https://lb.api%');
                            if ($searchByLen)
                                $query->orWhereRaw('LENGTH(url) = 255');
                        })
                        ->delete();
                }
            }
        }
    }

    protected function _importQualifications($tender)
    {
        foreach ($this->_data['qualifications'] as $qualification) {
            $qualificationModel = Qualification::where('cbd_id', $qualification['id'])->where('tender_id', $tender->id)->first();

            $qualificationData = [
                'status' => isset($qualification['status']) ? $qualification['status'] : '',
                'eligible' => ($qualification['eligible']) ? 1 : 0,
                'qualified' => ($qualification['qualified']) ? 1 : 0,
                'cbd_id' => $qualification['id'],
                'bid_id' => intval(Bid::where('cbd_id', $qualification['bidID'])->pluck('id')),
                'tender_id' => $tender->id,
            ];
            if (array_key_exists('lotID', $qualification)) {
                $qualificationData['lot_id'] = Lot::where('cbd_id', $qualification['lotID'])->pluck('id');
            }

            if (!$qualificationModel) {
                $qualificationModel = new Qualification($qualificationData);
                $qualificationModel->save();
            } else {
                $qualificationModel->update($qualificationData);
            }

            if (!empty($qualification['documents'])) {
                foreach ($qualification['documents'] as $qualificationDocumentData) {
                    $doc = $qualificationModel->documents()->where('orig_id', $qualificationDocumentData['id'])->first();
                    $params = [
                        'document_parent_id' => 0,
                        'format' => $qualificationDocumentData['format'],
                        'orig_id' => $qualificationDocumentData['id'],
                        'title' => $qualificationDocumentData['title'],
                        'url' => $qualificationDocumentData['url'],
                        'date_published' => Carbon::parse($qualificationDocumentData['datePublished'])->format('Y-m-d H:i:s'),
                        'date_modified' => Carbon::parse($qualificationDocumentData['dateModified'])->format('Y-m-d H:i:s'),
                    ];

                    if (!$doc) {
                        $qualificationModel->documents()->save(new QualificationDocuments($params));
                    } else {
                        $doc->update($params);
                    }

                    $searchByLen = (strlen($qualificationDocumentData['url']) > 255) ? true : false;
                    $qualificationModel->documents()->where('orig_id', $qualificationDocumentData['id'])
                        ->where(function ($query) use ($searchByLen) {
                            $query->where('url', 'LIKE', 'https://lb.api%');
                            if ($searchByLen)
                                $query->orWhereRaw('LENGTH(url) = 255');
                        })
                        ->delete();
                }
            }

            /*$api = new Api();
            $response = $api->postRaw('/tenders/' . $tender->cbd_id . '/qualifications/' . $qualificationModel->cbd_id . '/documents', '');
            if($response->responseCode == 200 || $response->responseCode == 201) {
                if(!empty($response['data'])) {
                    foreach ($response['data'] as $qualificationDocumentData) {
                        $doc = $qualificationModel->documents()->where('orig_id', $qualificationDocumentData['id'])->first();
                        $params = [
                            'document_parent_id' => 0,
                            'format' => $qualificationDocumentData['format'],
                            'orig_id' => $qualificationDocumentData['id'],
                            'title' => $qualificationDocumentData['title'],
                            'url' => $qualificationDocumentData['url'],
                        ];
                        if (!$doc) {
                            $qualificationModel->documents()->save(new QualificationDocuments($params));
                        } else {
                            $doc->update($params);
                        }
                    }
                }
            }*/
        }
    }

    protected function _importDocuments($tenderModel)
    {
    	if ($tenderModel->source==0) $signed = 0;
        foreach ($this->_data['documents'] as $document) {
            
        	if ($tenderModel->source==0) {
        		if ($document['format'] == 'application/pkcs7-signature' && $document['title'] == 'sign.p7s' && !$signed) {
            		$signed = 1;
            	}
        	}

            $documentModel = Document::where('orig_id', $document['id'])->where('tender_id', $tenderModel->id)->where('url', $document['url'])->first();
            $params = [
                'document_parent_id' => 0,
                'format' => $document['format'],
                'orig_id' => $document['id'],
                'title' => $document['title'],
                'url' => $document['url'],
                'tender_id' => $tenderModel->id,
                'status' => 'new',
                'date_published' => Carbon::parse($document['datePublished'])->format('Y-m-d H:i:s'),
                'date_modified' => Carbon::parse($document['dateModified'])->format('Y-m-d H:i:s'),
            ];
            if (!$documentModel) {
                $relatedItemId = isset($document['relatedItem']) && $document['documentOf'] != 'tender' ? $document['relatedItem'] : $tenderModel->cbd_id;
                $entityName = 'App\Model\\'.ucfirst($document['documentOf']);
                if ($document['documentOf'] == 'tender') {
                    $entity = $tenderModel;
                } else {
                    $entity = $entityName::where('cbd_id', $relatedItemId)->first();
                }
                if ($entity) {
                    Document::where('orig_id', $document['id'])->where('tender_id', $tenderModel->id)->update(['status' => 'old']);
                    $entity->documents()->save(new Document($params));
                }
            } else {
                $documentModel->update($params);
            }

            $searchByLen = (strlen($document['url']) > 255) ? true : false;
            Document::where('orig_id', $document['id'])
                    ->where('tender_id', $tenderModel->id)
                    ->where(function ($query) use ($searchByLen) {
                        $query->where('url', 'LIKE', 'https://lb.api%');
                        if ($searchByLen)
                            $query->orWhereRaw('LENGTH(url) = 255');
                    })
                    ->delete();
        }
        if ($tenderModel->source==0) $tenderModel->update(['signed' => $signed]);
    }

    protected function _importComplaints($complaints, $tenderModel, $awardModel = null)
    {
        if (!empty($complaints)) {


            foreach ($complaints as $complaint) {
                $complaintModel = Complaint::where('cbd_id', $complaint['id'])->where('tender_id', $tenderModel->id)->first();


                $complaintData = [
                    'cbd_id' => $complaint['id'],
                    'title' => $complaint['title'],
                    'created_at' => \Carbon\Carbon::parse($complaint['date'])->format('Y-m-d H:i:s'),
                    'description' => isset($complaint['description']) ? $complaint['description'] : '',
                    'tender_id' => $tenderModel->id,
                    'tender_organization_id' => $tenderModel->organization_id,
                    'type' => $complaint['type'],
                    'status' => $complaint['status'],
                    'resolution' => (isset($complaint['resolution'])) ? $complaint['resolution'] : '',
                    'resolution_type' => (isset($complaint['resolutionType'])) ? $complaint['resolutionType'] : ''
                ];
                if (isset($complaint['dateDecision'])) {
                    $complaintData['date_decision'] = $complaint['dateDecision'];
                }
                if (isset($complaint['tendererActionDate'])) {
                    $complaintData['date_action'] = $complaint['tendererActionDate'];
                }

                if (!$complaintModel) {
                    if (isset($complaint['author'])) {
                        $organization = Organization::getModel($complaint['author']);
                        $organizationId = $organization->id;
                    } else {
                        $organizationId = 0;
                    }
                    $complaintData['organization_id'] = $organizationId;

                    if (is_null($awardModel)) {
                        if (isset($complaint['relatedLot'])) {
                            $entity = Lot::where('cbd_id', $complaint['relatedLot'])->where('tender_id', $tenderModel->id)->first();
                        } else {
                            $entity = $tenderModel;
                        }
                    } else {
                        $entity = Award::where('cbd_id', $awardModel->cbd_id)->where('tender_id', $tenderModel->id)->first();
                    }
                    if ($entity) {
                        $complaintModel = new Complaint($complaintData);
                        $entity->complaints()->save($complaintModel);
                    }
                } else {
                    $complaintModel->update($complaintData);
                }

                if (!empty($complaint['documents'])) {
                    foreach ($complaint['documents'] as $complaintDocumentData) {
                        $doc = $complaintModel->documents()->where('orig_id', $complaintDocumentData['id'])->first();
                        $params = [
                            'document_parent_id' => 0,
                            'format' => $complaintDocumentData['format'],
                            'orig_id' => $complaintDocumentData['id'],
                            'title' => $complaintDocumentData['title'],
                            'url' => $complaintDocumentData['url'],
                            'date_published' => Carbon::parse($complaintDocumentData['datePublished'])->format('Y-m-d H:i:s'),
                            'date_modified' => Carbon::parse($complaintDocumentData['dateModified'])->format('Y-m-d H:i:s'),
                        ];

                        if (!$doc) {
                            $complaintModel->documents()->save(new ComplaintDocument($params));
                        } else {
                            $doc->update($params);
                        }

                        $searchByLen = (strlen($complaintDocumentData['url']) > 255) ? true : false;
                        $complaintModel->documents()->where('orig_id', $complaintDocumentData['id'])
                            ->where(function ($query) use ($searchByLen) {
                                $query->where('url', 'LIKE', 'https://lb.api%');
                                if ($searchByLen)
                                    $query->orWhereRaw('LENGTH(url) = 255');
                            })
                            ->delete();
                    }
                }
            }
        }
    }

    protected function _importQuestions($tenderModel)
    {
        foreach ($this->_data['questions'] as $question) {
            $questionModel = Question::where('cbd_id', $question['id'])->where('tender_id', $tenderModel->id)->first();

            $questionData = [
                'cbd_id' => $question['id'],
                'title' => $question['title'],
                'created_at' => \Carbon\Carbon::parse($question['date'])->format('Y-m-d H:i:s'),
                'description' => isset($question['description']) ? $question['description'] : '',
                'answer' => isset($question['answer']) ? $question['answer'] : '',
                'date_answer' => isset($question['dateAnswered']) ? $question['dateAnswered'] : null,
                'tender_id' => $tenderModel->id,
                'organization_to_id' => $tenderModel->organization->id
            ];

            if (!$questionModel) {
                if (isset($question['author'])) {
                    $organization = Organization::getModel($question['author']);
                    $organizationId = $organization->id;
                } else {
                    $organizationId = 0;
                }
                $questionData['organization_id'] = $organizationId;

                $entityName = 'App\Model\\'.ucfirst($question['questionOf']);
                $relatedItemId = isset($question['relatedItem']) && $question['questionOf'] != 'tender' ? $question['relatedItem'] : $tenderModel->cbd_id;
                if ($question['questionOf'] == 'tender') {
                    $entity = $tenderModel;
                } else {
                    $entity = $entityName::where('cbd_id', $relatedItemId)->first();
                }
                if ($entity) {
                    $questionModel = new Question($questionData);
                    $entity->questions()->save($questionModel);
                }
            } else {
                $questionModel->update($questionData);
            }
        }
    }

    protected function _importComplaintsQ($tenderModel, $data)
    {
        if (isset($data['complaints'])) {


            foreach ($data['complaints'] as $complaint) {
                $complaintModel = Complaint::where('cbd_id', $complaint['id'])->where('tender_id', $tenderModel->id)->first();


                $complaintData = [
                    'cbd_id' => $complaint['id'],
                    'title' => $complaint['title'],
                    'created_at' => \Carbon\Carbon::parse($complaint['date'])->format('Y-m-d H:i:s'),
                    'description' => isset($complaint['description']) ? $complaint['description'] : '',
                    'tender_id' => $tenderModel->id,
                    'tender_organization_id' => $tenderModel->organization_id,
                    'type' => $complaint['type'],
                    'status' => $complaint['status'],
                    'resolution' => (isset($complaint['resolution'])) ? $complaint['resolution'] : '',
                    'resolution_type' => (isset($complaint['resolutionType'])) ? $complaint['resolutionType'] : ''
                ];

                if (!$complaintModel) {
                    if (isset($complaint['author'])) {
                        $organization = Organization::getModel($complaint['author']);
                        $organizationId = $organization->id;
                    } else {
                        $organizationId = 0;
                    }
                    $complaintData['organization_id'] = $organizationId;

                    $entity = Qualification::where('cbd_id', $data['id'])->where('tender_id', $tenderModel->id)->first();
                    if ($entity) {
                        $complaintModel = new Complaint($complaintData);
                        $entity->complaints()->save($complaintModel);
                    }
                } else {
                    $complaintModel->update($complaintData);
                }

                if (!empty($complaint['documents'])) {
                    foreach ($complaint['documents'] as $complaintDocumentData) {
                        $doc = $complaintModel->documents()->where('orig_id', $complaintDocumentData['id'])->first();
                        $params = [
                            'document_parent_id' => 0,
                            'format' => $complaintDocumentData['format'],
                            'orig_id' => $complaintDocumentData['id'],
                            'title' => $complaintDocumentData['title'],
                            'url' => $complaintDocumentData['url'],
                            'date_published' => Carbon::parse($complaintDocumentData['datePublished'])->format('Y-m-d H:i:s'),
                            'date_modified' => Carbon::parse($complaintDocumentData['dateModified'])->format('Y-m-d H:i:s'),
                        ];

                        if (!$doc) {
                            $complaintModel->documents()->save(new ComplaintDocument($params));
                        } else {
                            $doc->update($params);
                        }

                        $searchByLen = (strlen($complaintDocumentData['url']) > 255) ? true : false;
                        $complaintModel->documents()->where('orig_id', $complaintDocumentData['id'])
                            ->where(function ($query) use ($searchByLen) {
                                $query->where('url', 'LIKE', 'https://lb.api%');
                                if ($searchByLen)
                                    $query->orWhereRaw('LENGTH(url) = 255');
                            })
                            ->delete();
                    }
                }
            }
        }
    }

    public function _importCancellation($tenderModel, $cancellationJson) {
        $cancellationData = [
            'cbd_id' => $cancellationJson['id'],
            'reason' => $cancellationJson['reason'],
            'status' => $cancellationJson['status'],
            'date'   => $cancellationJson['date'],
        ];

       $entity = null;

        if($cancellationJson['cancellationOf'] == 'tender') {
            $entity = \App\Model\Tender::where('cbd_id', $tenderModel->cbd_id)->first();
        }

        if($cancellationJson['cancellationOf'] == 'lot') {
            $entity = Lot::where('cbd_id', $cancellationJson['relatedLot'])->first();
        }

        if($entity != null) {
            $cancel = $entity->cancel()->updateOrCreate(['cbd_id' => $cancellationJson['id']], $cancellationData);

            if (isset($cancellationJson['documents'])) {
                foreach($cancellationJson['documents'] as $document) {
                    $docData = [
                        'orig_id'        => $document['id'],
                        'format'         => $document['format'],
                        'url'            => $document['url'],
                        'title'          => $document['title'],
                        'date_published' => $document['datePublished'],
                        'date_modified'  => $document['dateModified'],

                    ];

                    $cancelDoc = $cancel->documents()->where('orig_id', $document['id'])->first();

                    if($cancelDoc == null) {
                        $cancelDoc = CancellationDocuments::create($docData);
                        $cancel->documents()->save($cancelDoc);
                    } else {
                        $cancelDoc->update($docData);
                    }
                }

                $searchByLen = (strlen($document['url']) > 255) ? true : false;
                $cancel->documents()->where('orig_id', $document['id'])
                    ->where(function ($query) use ($searchByLen) {
                        $query->where('url', 'LIKE', 'https://lb.api%');
                        if ($searchByLen)
                            $query->orWhereRaw('LENGTH(url) = 255');
                    })
                    ->delete();
            }
        }
    }

    protected function _importFeatures($tenderModel)
    {
        $featureIds = [];
        foreach ($this->_data['features'] as $item) {
            $feature = Feature::where('cbd_id', $item['code'])->where('tender_id', $tenderModel->id)->first();
            $featureData = [
                'cbd_id' => $item['code'],
                'title' => $item['title'],
                'description' => isset($item['description']) ? $item['description'] : '',
                'tender_id' => $tenderModel->id
            ];

            if (!$feature) {
                $featureOf = $item['featureOf'] == 'tenderer' ? 'tender' : $item['featureOf'];
                $entityName = 'App\Model\\'.ucfirst($featureOf);
                $relatedItemId = $item['featureOf'] != 'tenderer' ? $item['relatedItem'] : $tenderModel->cbd_id;
                if ($featureOf == 'tender') {
                    $entity = $tenderModel;
                } else {
                    $entity = $entityName::where('cbd_id', $relatedItemId)->where('tender_id', $tenderModel->id)->first();
                }
                if ($entity) {
                    $feature = new Feature($featureData);
                    $entity->features()->save($feature);
                }
            } else {
                $feature->update($featureData);
            }
            if ($feature) {
                $featureIds[] = $feature->id;
                $valueIds = [];
                foreach ($item['enum'] as $valueData) {
                    $value = $feature->values()->where('title', $valueData['title'])->where('value', round($valueData['value'] * 100))->first();
                    if ($value) {
                        $valueIds[] = $value->id;
                    } else {
                        $value = $feature->values()->save(new FeatureValue([
                            'value' => round($valueData['value'] * 100),
                            'title' => $valueData['title']
                        ]));
                        $valueIds[] = $value->id;
                    }
                }
                $feature->values()->whereNotIn('id', $valueIds)->delete();
            }
        }
        Feature::whereNotIn('id', $featureIds)->where('tender_id', $tenderModel->id)->delete();
    }


    /**
     * @param \App\Model\Tender $tender
     * @return \App\Model\Tender|null
     * @throws \Exception
     */
    public function process(\App\Model\Tender $tender = null)
    {
        $source = 0;
        try {
            DB::beginTransaction();
            $data = $this->_data;
            $notification_service = new NotificationService();
            $tags = new Tags();
            $tags->set_offers_link('<a href="'.URL::route('bid.list').'">Мої пропозиції</a>');

            if (array_key_exists('UTregionid', $data['procuringEntity']['address'])) {
                $source = 2;
            }

            $tenderData = $this->_getTenderData($data, $source);

            if (is_null($tender) && isset($data['id'])) {
                $tender = \App\Model\Tender::where('cbd_id', $data['id'])->first();
            }

            if (!$tender) {
                $isNew = true;
                $tenderData['source'] = $source;
                if (isset($data['lots'])) {
                    $tenderData['multilot'] = 1;
                }
                $tender = new \App\Model\Tender($tenderData);

                if ($data['procurementMethod'] == 'selective') {
                    $firstStage = TenderStages::where('second_stage', $data['id'])->first();
                    if (isset($firstStage->firstStage) && $firstStage->firstStage->organization)
                    {
                        $firstStage = $firstStage->firstStage;
                        $firstStage->organization->tenders()->save($tender);

                        if ($firstStage->tenderContacts->count() > 0) {
                            foreach ($firstStage->tenderContacts as $contact) {
                                $tenderContacts = new TenderContacts(['tender_id' => $tender->id, 'contact_id' => $contact->contact_id]);
                                $tenderContacts->save();
                            }
                        }
                    } else {
                        Organization::getModel($data['procuringEntity'], 0)->tenders()->save($tender);
                    }
                } else {
                    Organization::getModel($data['procuringEntity'], 1)->tenders()->save($tender);
                }
            } else {
                $isNew = false;
                if (isset($tenderData['auction_start_date'])) {
                    $auctionStartDate = Carbon::parse($tenderData['auction_start_date'])->format('Y-m-d H:i');
                    $tags->set_tender_date($auctionStartDate);
                    $tags->set_tender_link('<a href="'.URL::route('tender.show', [$tender->id]).'">'.$tender->tenderID.'</a>');

                    if (is_null($tender->auction_start_date) && $tenderData['auction_start_date'] != null) {
                        foreach ($tender->bids as $bid) {
                            if ($bid->organization->user && $bid->organization->type == 'supplier') {
                                $notification_service->create($tags, NotificationTemplate::TENDER_SET_DATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
                            }

                            if ($tender->procedureType->threshold_type != 'above') {
                                $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
                            }
                        }
                    } elseif (strtotime($tender->auction_start_date) != strtotime($auctionStartDate)) {
                        foreach ($tender->bids as $bid) {
                            if ($bid->organization->user && $bid->organization->type == 'supplier') {
                                $notification_service->create($tags, NotificationTemplate::TENDER_CHANGE_DATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
                            }

                            if ($tender->procedureType->threshold_type != 'above') {
                                $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
                            }
                        }
                    }
                }

                if($tenderData['status']=='cancelled' && $tender->status != 'cancelled') {
                    if(count($tender->bids) != 0) {
                        foreach ($tender->bids as $bid) {
                            if ($bid->organization['source'] == 0) {
                                $user = $bid->organization->user;
                                Mail::queue('emails.activate', ['user' => $user, 'tenderData' => $tenderData], function ($message) use ($user, $tenderData) {
                                    $message->to($user->email, $user->name)->subject(
                                        '<p>Шановний користувач, лот ' . $tenderData['title'] . ' закупівлі №' . $tenderData['cbd_id'] . ' було відмінено. </p>
                                         <p>Ознайомитись з причиною можна за посиланням < a href="' . $tenderData['auction_url'] . '">Посилання на закупівлю</a></p>'
                                    );
                                });
                            };
                        }
                    }
                }

                $tenderData['blocked'] = 0;
                if ($data['status'] == 'active.tendering' && !isset($data['next_check']) && time() > strtotime($data['tenderPeriod']['endDate'])) {
                    $tenderData['blocked'] = 1;
                }


                if ($data['status'] == 'active.pre-qualification.stand-still' && !isset($data['next_check'])) {
                    $tenderData['blocked'] = 1;
                }

                $complaints = Complaint::where('tender_id', $tender->id)->where('complaintable_type', 'App\Model\Award')->where('status', '!=', 'resolved')->count();
                if ($data['status'] == 'active.awarded' && $complaints > 0) {
                    $tenderData['blocked'] = 1;
                }

                $tenderData['send_to_import'] = null;
                $tender->update($tenderData);
            }

            if (isset($data['stage2TenderID'])) {
                $tenderStages = TenderStages::where('first_stage', $data['id'])->first();
                if (!$tenderStages) {
                    $tenderStages = new TenderStages(['first_stage' => $data['id'], 'second_stage' => $data['stage2TenderID']]);
                    $tenderStages->save();
                }
                $secondStageTender = \App\Model\Tender::where('cbd_id', $data['stage2TenderID'])->first();
                if (!$secondStageTender) {
                    $this->dispatch((new SyncTender($data['stage2TenderID']))->onQueue('tenders'));
                }
            }

            if (isset($data['dialogueID'])) {
                $tenderStages = TenderStages::where('second_stage', $data['id'])->first();

                if (!$tenderStages) {
                    $tenderStages = new TenderStages(['first_stage' => $data['dialogueID'], 'second_stage' => $data['id']]);
                    $tenderStages->save();
                }
                $this->dispatch((new SyncTender($data['dialogueID']))->onQueue('tenders'));
            }

            if ($tender->procedureType->threshold_type == 'above') {
                $bids = $tender->allBids()->onlyOur()->get();
                foreach ($bids as $bid) {
                    $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
                }
            }

            $lotModels = [];
            $lotIds = [];

            if (isset($data['lots'])) {
                foreach ($data['lots'] as $lot) {
                    $lotData = [
                        'cbd_id' => $lot['id'],
                        'title' => $lot['title'],
                        'description' => isset($lot['description']) ? $lot['description'] : '',
                        'amount' => $lot['value']['amount'],
                        'minimal_step' => (isset($lot['minimalStep'])) ? $lot['minimalStep']['amount'] : 0,
                        'status' => $lot['status'],
                        'auction_url' => empty($lot['auctionUrl']) ? '' : $lot['auctionUrl'],
                        'auction_start_date' => array_key_exists('auctionPeriod', $lot) &&
                                                array_key_exists('startDate', $lot['auctionPeriod']) &&
                                                !empty($lot['auctionPeriod']['startDate']) ? $lot['auctionPeriod']['startDate'] : null,
                        'auction_end_date' => array_key_exists('auctionPeriod', $lot) &&
                                                array_key_exists('endDate', $lot['auctionPeriod']) &&
                                                !empty($lot['auctionPeriod']['endDate']) ? $lot['auctionPeriod']['endDate'] : NULL,
                        'guarantee_amount'      => isset($lot['guarantee']['amount']) ? $lot['guarantee']['amount'] : null,
                        'guarantee_currency_id' => isset($lot['guarantee']['currency']) ? \App\Model\Currencies::where('currency_code', $lot['guarantee']['currency'])->first()->id : null,
                        'date' => isset($lot['date']) ? Carbon::parse($lot['date'])->format('Y-m-d H:i:s') : null,
                    ];
                    //TODO-parus мог сломать
                    $lotModel = $tender->lots()->where('cbd_id', $lot['id'])->first();

                    if ($lotModel) {
                        $oldAuctionStartDate = $lotModel->auction_start_date;
                        $lotModel->update($lotData);
                    } else {
                        $oldAuctionStartDate = null;
                        $lotModel = new Lot($lotData);
                        $tender->lots()->save($lotModel);
                    }
                    $lotModels[$lot['id']] = $lotModel;
                    $lotIds[] = $lotModel->id;

                    if (isset($lotData['auction_start_date'])) {
                        $auctionStartDate = Carbon::parse($lotData['auction_start_date'])->format('Y-m-d H:i');
                        $tags->set_tender_date($auctionStartDate);
                        $tags->set_tender_link('<a href="'.URL::route('tender.show', [$lotModel->tender->id]).'">'.$lotModel->tender->tenderID.'</a>');

                        if ( is_null($oldAuctionStartDate) && $lotData['auction_start_date'] != null) {
                            foreach ($lotModel->bids as $bid) {
                                if ($bid->organization->user && $bid->organization->type == 'supplier') {
                                    $notification_service->create($tags, NotificationTemplate::TENDER_SET_DATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
                                }

                                if ($tender->procedureType->threshold_type != 'above') {
                                    $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
                                }
                            }
                        } elseif (strtotime(substr($oldAuctionStartDate, 0, 16)) != strtotime($auctionStartDate)) {
                            foreach ($lotModel->bids as $bid) {
                                if ($bid->organization->user && $bid->organization->type == 'supplier') {
                                    $notification_service->create($tags, NotificationTemplate::TENDER_CHANGE_DATE, $bid->organization->user->id, self::DEFAULT_LANGUAGE);
                                }

                                if ($tender->procedureType->threshold_type != 'above') {
                                    $this->dispatch((new SyncBid($bid->id))->onQueue('bids'));
                                }
                            }
                        }
                    }
                }
            } else {
                $lotModel = $tender->lots()->first();
                $lotData = [
                    'guarantee_amount' => isset($data['guarantee']['amount']) ? $data['guarantee']['amount'] : null,
                    'guarantee_currency_id' => isset($data['guarantee']['currency']) ? \App\Model\Currencies::where('currency_code', $data['guarantee']['currency'])->first()->id : null,
                ];
                if (!$lotModel) {
                    $lotModel = new Lot($lotData);
                    $tender->lots()->save($lotModel);
                } else {
                    $lotModel->update($lotData);
                }
                $lotModels[] = $lotModel;
                $lotIds[]    = $lotModel->id;
            }
            Lot::whereNotIn('id', $lotIds)->where('tender_id', $tender->id)->delete();

            $itemIds = [];
            foreach ($data['items'] as $item) {
                //TODO 3b7a3662181a4825aecfbe55f1c6aad0 (sandbox) у этого тендера нет unit code и как оказалось он не обязателен
                $unitModel = \App\Model\Units::where('code', $item['unit']['code'])->first();

                if(!$unitModel) {
                    $unitModel              = new Units();
                    $unitModel->code        = $item['unit']['code'];
                    $unitModel->description = $item['unit']['name'];
                    $unitModel->symbol      = $item['unit']['name'];
                    $unitModel->save();
                }

                $itemData = [
                    "cbd_id"      => $item['id'],
                    "description" => $item['description'],
                    "quantity"    => $item['quantity'],
                    "unit_id"     => $unitModel->id,
                    "tender_id"   => $tender->id,
                ];

                if (isset($item['deliveryDate'])) {
                    $itemData['delivery_date_start'] = isset($item['deliveryDate']['startDate']) ? $item['deliveryDate']['startDate'] : null;
                    $itemData['delivery_date_end'] = isset($item['deliveryDate']['endDate']) ? $item['deliveryDate']['endDate'] : null;
                } else {
                    $itemData['delivery_date_start'] = NULL;
                    $itemData['delivery_date_end'] = NULL;
                }

                $regionId = 0;
                if (isset($item['deliveryAddress'])) {
                    if (isset($item['deliveryAddress']['region'])) {
                        $regionName = trim(str_replace('область', '', $item['deliveryAddress']['region']));
                        $region = \App\Model\TendersRegions::orWhere('region_ua', 'LIKE', '%' . $regionName . '%')->orWhere('region_search', 'LIKE', '%' . $regionName . '%')->first();
                        if ($region) {
                            $regionId = $region->id;
                        }
                    }
                    $itemData['country_id'] = 1;
                    $itemData['region_id'] = $regionId;
                    $itemData['region_name'] = isset($item['deliveryAddress']['region']) ? $item['deliveryAddress']['region'] : '';
                    $itemData['postal_code'] = isset($item['deliveryAddress']['postalCode']) ? $item['deliveryAddress']['postalCode'] : '';
                    $itemData['locality'] = isset($item['deliveryAddress']['locality']) ? $item['deliveryAddress']['locality'] : '';
                    $itemData['delivery_address'] = isset($item['deliveryAddress']['streetAddress']) ? $item['deliveryAddress']['streetAddress'] : '';
                }
                $codes = [];
                $codes[] = \App\Model\Codes::where('code', $item['classification']['id'])->first()->id;
                if (isset($item['additionalClassifications'])) {
                    foreach ($item['additionalClassifications'] as $additionalCode) {
                        //todo-parus возможно поломал парус
                        $_code = \App\Model\Codes::where('code', $additionalCode['id'])->first();
                        if ($_code) {
                            $codes[] = $_code->id;
                        }
                    }
                }
                $lotModelId = isset($item['relatedLot']) ? $item['relatedLot'] : 0;
                if (isset($lotModels[$lotModelId])) {
                    $itemModel = $lotModels[$lotModelId]->items()->where('cbd_id', $itemData['cbd_id'])->first();
                    if ($itemModel) {
                        $itemModel->update($itemData);
                    } else {
                        $itemModel = new Item($itemData);
                        $lotModels[$lotModelId]->items()->save($itemModel);
                    }
                    $itemModel->codes()->sync($codes);
                    $itemIds[] = $itemModel->id;
                }
            }
            $tender->allItems()->whereNotIn('items.id', $itemIds)->delete();
            if (isset($data['features'])) {
                $this->_importFeatures($tender);
            }
            if (isset($data['bids'])) {
                $this->_importBids($tender);
            }
            if (isset($data['qualifications'])) {
                $this->_importQualifications($tender);
            }

            if (isset($data['awards'])) {
                $this->_importAwards($tender);
            }

            if (isset($data['contracts'])) {
                $this->_importContracts($tender);
            }

            if (isset($data['documents'])) {
                $this->_importDocuments($tender);
            }

            if (isset($data['questions'])) {
                $this->_importQuestions($tender);
            }
            if (isset($data['complaints'])) {
                $this->_importComplaints($data['complaints'], $tender);
            }

            if (isset($data['qualifications'])) {
                foreach ($data['qualifications'] as $qualification) {
                    $this->_importComplaintsQ($tender, $qualification);
                }
            }

            if (isset($data['cancellations'])) {
                foreach($data['cancellations'] as $cancellation) {
                    $this->_importCancellation($tender, $cancellation);
                }
            }

            DB::commit();
            if ($isNew) {
                $this->sendToUatenders($tender);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $tender;
    }

    public function sendToUatenders($tender)
    {
//        $client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => false]);
//        $response = $client->get('http://www.ua-tenders.com/imp/import.php?action=add&feed=zakupki&type=tender&id=' . $tender->id);
//        $body = (string)$response->getBody();
//        if ($body == 1) {
//            $tender->send_to_import = date('Y-m-d H:i:s');
//            $tender->save();
//        }
    }

    /**
     * @param $data
     * @param $source
     * @return array
     */
    private function _getTenderData($data, $source)
    {
        $tenderData = [
            'type_id' => ProcedureTypes::where('procurement_method', $data['procurementMethod'])
                ->where('procurement_method_type', $data['procurementMethodType'])->first()->id,
            'title' => $data['title'],
            'description' => isset($data['description']) ? $data['description'] : '',
            'tenderID' => $data['tenderID'],
            'cbd_id' => $data['id'],
            'mode' => isset($data['mode']) && $data['mode'] == 'test' ? 0 : 1,
            'status' => $data['status'],
            'number_of_bids' => isset($data['numberOfBids']) ? $data['numberOfBids'] : 0,
            'auction_url' => isset($data['auctionUrl']) ? $data['auctionUrl'] : '',
            'amount' => $data['value']['amount'],
            'currency_id' => \App\Model\Currencies::where('currency_code', $data['value']['currency'])->first()->id,
            'tax_included' => $data['value']['valueAddedTaxIncluded'],
            'minimal_step' => isset($data['minimalStep']['amount']) ? $data['minimalStep']['amount'] : 0,
            'auction_start_date' => isset($data['auctionPeriod']['startDate']) ? $data['auctionPeriod']['startDate'] : null,
            //'auction_end_date' => isset($data['auctionPeriod']['endDate']) ? $data['auctionPeriod']['endDate'] : null,
            'award_start_date' => isset($data['awardPeriod']['startDate']) ? $data['awardPeriod']['startDate'] : null,
            'award_end_date' => isset($data['awardPeriod']['endDate']) ? $data['awardPeriod']['endDate'] : null,
            'enquiry_start_date' => isset($data['enquiryPeriod']['startDate']) ? $data['enquiryPeriod']['startDate'] : null,
            'enquiry_end_date' => isset($data['enquiryPeriod']['endDate']) ? $data['enquiryPeriod']['endDate'] : null,
            'tender_start_date' => isset($data['tenderPeriod']['startDate']) ? $data['tenderPeriod']['startDate'] : null,
            'tender_end_date' => isset($data['tenderPeriod']['endDate']) ? $data['tenderPeriod']['endDate'] : null,
            'complaint_date_start' => isset($data['complaintPeriod']['startDate']) ? $data['complaintPeriod']['startDate'] : null,
            'complaint_date_end' => isset($data['complaintPeriod']['endDate']) ? $data['complaintPeriod']['endDate'] : null,
            'date_modified' => $data['dateModified']        ];

        return $tenderData;
    }
}