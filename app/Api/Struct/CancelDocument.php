<?php

namespace App\Api\Struct;

use Carbon\Carbon,
    Illuminate\Support\Facades\Config;

class CancelDocument extends Structure
{
    protected $data = [];
    protected $_document;
    public $title;
    public $uploadUrl = '';

    public function __construct($document)
    {
        $this->_document = $document;
        $cancel = $document->cancel;
        $this->title = isset($document->title) ? $document->title  : '';
        $tender = $cancel->cancelable->type == 'tender' ? $cancel->cancelable : $cancel->cancelable->tender;
        if($this->_document->orig_id) {
            $this->uri = '/tenders/'.$tender->cbd_id.'/cancellations/'.$cancel->cbd_id.'/documents/'. $document->orig_id .'?acc_token=' . $tender->access_token;
            $this->isNew = false;
        } else {
            $this->uri = '/tenders/'.$tender->cbd_id.'/cancellations/'.$cancel->cbd_id.'/documents?acc_token=' . $tender->access_token;
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
        return $storagePath = Config::get('filesystems.disks.documents.root').$this->_document->path;
    }


}