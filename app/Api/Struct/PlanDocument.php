<?php

namespace App\Api\Struct;

use Carbon\Carbon,
    Illuminate\Support\Facades\Config;

class PlanDocument extends Structure
{
    protected $data = [];
    protected $_document;
    public $uploadUrl = '';


    public function __construct($document)
    {
        $this->_document = $document;
        $plan = $document->plan;

        if (!empty($document->orig_id)) {
            $this->uri = '/plans/'.$plan->cbd_id.'/documents/'.$document->orig_id.'?acc_token=' . $plan->access_token;
            $this->isNew = false;
        } else {
            $this->uri = '/plans/'.$plan->cbd_id.'/documents?acc_token=' . $plan->access_token;
            $this->isNew = true;
        }


        $this->_document = $document;
        $this->uploadUrl = $document->upload_url;

        $this->access_token = $plan->access_token;
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
        $data['title'] = basename($this->getFullPath());

        return $data;
    }

    public function getFullPath()
    {
        return $storagePath = Config::get('filesystems.disks.documents.root').$this->_document->path;
    }

    public function getFileName() {
        return $this->_document->title;
    }
}