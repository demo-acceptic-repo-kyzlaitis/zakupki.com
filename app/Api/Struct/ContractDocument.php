<?php

namespace App\Api\Struct;

use App\Model\ContractChange;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Log;

class ContractDocument extends Structure
{
    protected $data = [];
    protected $_document;
    public $uploadUrl = '';


    public function __construct($document)
    {
        $this->_document = $document;
        $contract = $document->contract;
        $tender = $contract->tender;


        if ($contract->access_token != '') {
            if (!empty($document->orig_id)) {
                $this->uri = '/contracts/'.$contract->cbd_id.'/documents/'.$document->orig_id.'?acc_token=' . $contract->access_token;
                $this->isNew = false;
            } else {
                $this->uri = '/contracts/'.$contract->cbd_id.'/documents?acc_token=' . $contract->access_token;
            }
        } else {
            if (!empty($document->orig_id)) {
                $this->uri = '/tenders/'.$tender->cbd_id.'/contracts/'.$contract->cbd_id.'/documents/'.$document->orig_id.'?acc_token=' . $tender->access_token;
                $this->isNew = false;
            } else {
                $this->uri = '/tenders/'.$tender->cbd_id.'/contracts/'.$contract->cbd_id.'/documents?acc_token=' . $tender->access_token;
            }
        }



        $this->_document = $document;
        $this->uploadUrl = $document->upload_url;

        $this->access_token = $tender->access_token;
    }

    public function getData()
    {
        $data = [];

        if ($this->_document->title == 'sign.p7s') {
            $data['format'] = 'application/pkcs7-signature';
        } else {
            $data['format'] = mime_content_type($this->getFullPath());
        }
        if (!empty($this->_document->hash)) {
            $data['hash'] = 'md5:'.$this->_document->hash;
        }
        if (!empty($this->_document->upload_url)) {
            $data['url'] = $this->_document->url;
        }

        if ($this->_document->change_id > 0) {
            $contractChange = ContractChange::findOrFail($this->_document->change_id);
            $data['documentOf'] = 'change';
            $data['relatedItem'] = $contractChange->cbd_id;
        } else {
            $data['documentOf'] = 'tender';
        }
        $data['title'] = basename($this->getFullPath());

        return $data;
    }
    public function getFileName() {
        return  $this->_document->title;
    }

    public function getFullPath()
    {
        return $storagePath = Config::get('filesystems.disks.documents.root').$this->_document->path;
    }
}