<?php

namespace App\Api\Struct;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class QualificationDocument extends Structure
{
    protected $data = [];
    protected $_document;
    protected $bid;
    public $uploadUrl = '';


    public function __construct($document)
    {
        $this->_document = $document;
        $qualification = $document->qualification;
        $tender = $qualification->bid->tender;

        if (!empty($document->orig_id)) {
            $this->uri = '/tenders/' . $tender->cbd_id . '/qualifications/' . $qualification->cbd_id . '/documents/' . $document->orig_id . '?acc_token=' . $tender->access_token;
            $this->isNew = false;
        } else {
            $this->uri = '/tenders/' . $tender->cbd_id . '/qualifications/' . $qualification->cbd_id . '/documents?acc_token=' . $tender->access_token;
            $this->isNew = true;
        }

        $this->_document = $document;
        $this->uploadUrl = $document->upload_url;

        $this->access_token = $tender->access_token;
    }

    public function getData()
    {
        $data['format'] = mime_content_type($this->getFullPath());
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
        return $storagePath = Config::get('filesystems.disks.documents.root') . $this->_document->path;
    }
}