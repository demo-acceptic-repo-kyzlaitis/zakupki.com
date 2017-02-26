<?php

namespace App\Http\Controllers\Admin;

use App\Events\TenderAnswerEvent;
use App\Events\TenderPublishEvent;
use App\Model\Replication;
use App\Model\Status;
use Validator;

use App\Events\DocumentUploadEvent;
use App\Events\TenderSaveEvent;
use App\Http\Requests\CreateTenderRequest;
use App\Model\Codes;
use App\Model\Document;
use App\Model\DocumentType;
use App\Model\Item;
use App\Model\Organization;
use App\Model\Tender;
use App\Model\Bid;
use App\Model\Currencies;
use App\Model\Units;


use Illuminate\Http\Request;

use Event;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class ReplicationController extends Controller
{
    /**
     *
     */
    public function index(){
            dd(Replication::all());
        }
    public function truncate(){
        Replication::truncate();
        return 'Ok';
    }
}
