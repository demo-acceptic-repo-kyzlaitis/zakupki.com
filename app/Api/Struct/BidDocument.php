<?php

namespace App\Api\Struct;

use Carbon\Carbon;
use Illuminate\Support\Facades\Config;

class BidDocument extends Structure
{
    protected $data = [];
    protected $_document;
    protected $bid;
    protected $tender;
    public $uploadUrl = '';

    public function __construct($document)
    {
        $this->_document = $document;
        $bid = $document->bid;
        $this->tender = $bid->bidable->tender;
        $convert = 'documents';
        if ($this->tender->type_id == 3 && ($document->type_id == 27 || $document->type_id == 28)) {
            $convert = 'financial_documents';
        }

        if ($this->tender->type_id == 3 && $document->type_id == 29 ) {
            $convert = 'eligibility_documents';
        }

        if (!empty($document->orig_id)) {
            $this->uri = '/tenders/' . $this->tender->cbd_id . '/bids/' . $bid->cbd_id . '/' . $convert . '/' . $document->orig_id . '?acc_token=' . $bid->access_token;
            $this->isNew = false;
        } else {
            $this->uri = '/tenders/' . $this->tender->cbd_id . '/bids/' . $bid->cbd_id . '/' . $convert . '?acc_token=' . $bid->access_token;
            $this->isNew = true;
        }

        $this->_document = $document;
        $this->uploadUrl = $document->upload_url;

        $this->access_token = $bid->access_token;
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

        if (in_array($this->tender->type_id, [3, 9, 10, 11, 12]) && $data['format'] != 'application/pkcs7-signature') {
            if ($this->_document->confidential) {
                $data['confidentiality'] = 'buyerOnly';
                $data['confidentialityRationale'] = $this->_document->confidential_cause;
            } else {
                $data['confidentiality'] = 'public';
            }
            if ($this->tender->type_id == 9 || $this->tender->type_id == 10) {
                $data['isDescriptionDecision'] = ($this->_document->description_decision) ? true : false;
            }
        }

        return $data;
    }

    public function getFileName() {
        return $this->_document->title;
    }

    public function getFullPath()
    {
        return $storagePath = Config::get('filesystems.disks.documents.root').$this->_document->path;
    }
}