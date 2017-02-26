<?php

namespace App\Api\Struct;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class ComplaintDocument extends Structure
{
    protected $data = [];
    protected $_document;
    public $uploadUrl = '';

    public function __construct($document)
    {
        $this->_document = $document;

        if ($document->complaint->complaintable->type == 'qualification') {
            $tender = $document->complaint->complaintable->bid->tender;
        } else {
            $tender = $document->complaint->complaintable->tender;
        }
        $cbdId = $tender->cbd_id;

        if ($document->author == 'complaint_owner') {
            $accessToken = $document->complaint->access_token;
        } else {
            $accessToken = $tender->access_token;
        }
        $uri = [
            'tenders',
            $cbdId
        ];
        if ($document->complaint->complaintable->type == 'award') {
            $uri[] = 'awards';
            $uri[] = $document->complaint->complaintable->cbd_id;
        }
        if ($document->complaint->complaintable->type == 'qualification') {
            $uri[] = 'qualifications';
            $uri[] = $document->complaint->complaintable->cbd_id;
        }
        $uri[] = 'complaints';
        $uri[] = $document->complaint->cbd_id;
        $uri[] = 'documents';


        if ($document->orig_id == '') {
            $this->isNew = true;
        } else {
            $uri[] = $document->orig_id;
            $this->isNew = false;
        }

        $this->uri = '/'.implode('/', $uri)."?acc_token=$accessToken";
        $this->uploadUrl = $document->upload_url;


        $this->access_token = $accessToken;
    }


    public function getData()
    {
        //$data['documentOf'] = $this->_document->documentable->type;
//        if ($this->_document->documentable->type != 'tender') {
//            $data['relatedItem'] = $this->_document->documentable->cbd_id;
//        }

        $data['format'] = mime_content_type($this->getFullPath());
        if (!empty($this->_document->hash)) {
            $data['hash'] = 'md5:'.$this->_document->hash;
        }
        if (!empty($this->_document->upload_url)) {
            $data['url'] = $this->_document->url;
        }
        $temp = explode(DIRECTORY_SEPARATOR, $this->getFullPath());
        $data['title'] =  array_pop($temp);

        return $data;
    }

    public function getFullPath()
    {
        return $storagePath = Config::get('filesystems.disks.documents.root').$this->_document->path;
    }
}