<?php

namespace App\Api\Struct;

use Illuminate\Support\Facades\Config;

class Document extends Structure
{
    protected $data = [];
    protected $_document;
    public $uploadUrl = '';

    public function __construct($document)
    {
        $this->_document = $document;

        $cbdId = $document->documentable->tender->cbd_id;

        if (isset($document->documentable->access_token)) {
            $accessToken = $document->documentable->access_token;
        } else {
            $accessToken = $document->documentable->tender->access_token;
        }
        $uri = [
            'tenders',
            $cbdId
        ];
        if (!empty($document->documentable->documentContainerName)) {
            $uri[] = $document->documentable->documentContainerName;
            $uri[] = $document->documentable->cbd_id;
        }
        $uri[] = 'documents';


        if ($document->orig_id != '') {
            $uri[] = $document->orig_id;
            $this->isNew = false;
        }
        
        $this->uri = '/'.implode('/', $uri)."?acc_token=$accessToken";
        $this->uploadUrl = $document->upload_url;

        $this->access_token = $accessToken;
    }


    public function getData()
    {
        $data['documentOf'] = $this->_document->documentable->type;
        if ($this->_document->title == 'sign.p7s') {
            $data['format'] = 'application/pkcs7-signature';
        } else {
            $data['format'] = mime_content_type($this->getFullPath());
        }
        
        if ($this->_document->documentable->type != 'tender') {
            $data['relatedItem'] = $this->_document->documentable->cbd_id;
        }
        $data['title'] = basename($this->getFullPath());

        if (!empty($this->_document->hash)) {
            $data['hash'] = 'md5:'.$this->_document->hash;
        }
        if (!empty($this->_document->upload_url)) {
            $data['url'] = $this->_document->url;
        }

        return $data;
    }

    public function getFileName() {
        return $this->_document->title;
    }

    public function getFullPath()
    {
        $storagePath = Config::get('filesystems.disks.documents.root').$this->_document->path;
        return $storagePath;
    }
}