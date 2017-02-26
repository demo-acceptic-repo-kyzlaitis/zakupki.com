<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::group(['domain' => '{server}.zakupki.com.ua'], function () {
    Route::get('robots.txt', function ($server) {

        return response()->view("robots.$server")->header('Content-Type', 'text/plain; charset=utf-8');
    });
});


//Route::group(['middleware'=> ['auth.basic']],    function() {

Route::get('testdocs/{id}', function ($id) {
    return view('pages.test.docs', ['id' => $id]);
});

Route::get('stat', 'TenderController@stat');
Route::get('stat/{date}', 'TenderController@stat');
Route::post('/paysystems/platon', function() {
	$logger = new Monolog\Logger('API');
	$logger->pushHandler(new Monolog\Handler\RotatingFileHandler(storage_path('logs/platon.log')));
	if (count($_POST)>0) {
		$logger->info(json_encode(array_keys($_POST)));
		foreach ($_POST as $key) {
			if ( base64_encode(base64_decode($key)) === $key){
    			$logTxt = "From base64: ".base64_decode($key);
			} else {
				$logTxt = $key;
			}
    		$logger->info(json_encode($logTxt));
		}
	} else {
		$logTxt = 'Empty $_POST';
		$logger->warning(json_encode($logTxt));
	}
});

Route::get('health', function () {
    $result = [];
    exec("pgrep supervisor", $pids);
    if(empty($pids)) {
        $result['supervisor']['status'] = 'SUPERVISOR_PROBLEM';
    } else {
        $result['supervisor']['status'] = 'SUPERVISOR_OK';
    }
    $load = sys_getloadavg();
    $result['proc']['last_minute'] = $load[0];
    $result['proc']['last_5_minutes'] = $load[1];
    $result['proc']['last_15_minutes'] = $load[2];
    $result['proc']['status'] = ($load[0] > 80) ? 'PROC_PROBLEM' : 'PROC_OK';

    exec('free -m', $result['memory']);

    $df = disk_free_space('/');
    $dt = disk_total_space('/');
    $result['disk']['space']['free'] = $df;
    $result['disk']['space']['total'] = $dt;
    if ($df / $dt > 0.1) {
        $result['disk']['space']['status'] = 'DISK_OK';
    }
    if(\Illuminate\Support\Facades\DB::connection()->getDatabaseName())
    {
        $result['mysql']['status'] = 'DB_OK';
    }
    $redis = \Illuminate\Support\Facades\Redis::connection();
    $countTenders = $redis->llen('queues:tenders');
    $countTendersHigh = $redis->llen('queues:tenders_high');
    $result['queue']['tenders_high']['count'] = $countTendersHigh;
    $result['queue']['tenders_high']['status'] = $countTendersHigh > 500 ? 'QUEUE_TENDERS_HIGH_PROBLEM' : 'QUEUE_TENDERS_HIGH_OK';
    $result['queue']['tenders']['count'] = $countTenders;
    $result['queue']['tenders']['status'] = $countTenders > 500 ? 'QUEUE_TENDERS_PROBLEM' : 'QUEUE_TENDERS_OK';

    return $result;
});

Route::get('/login', function () {
    return view('pages.user.login');
});

Route::post('/login', ['uses' => 'UserController@login', 'as' => 'user.login'])->middleware('log');
Route::get('/register', ['uses' => 'UserController@register', 'as' => 'user.register']);
Route::post('/register', ['uses' => 'UserController@create', 'as' => 'user.create']);
Route::post('/user/resend', ['uses' => 'UserController@resend', 'as' => 'user.resend']);
Route::get('/user/activate/{code}', ['uses' => 'UserController@activate', 'as' => 'user.activate']);
Route::post('/paysystems/callback', ['uses' => 'PaymentController@callback', 'as' => 'pay.callback']);

//Route::get('/',                     ['uses' => 'TenderController@listTenders',     'as' => 'home']);

Route::get('/v/', ['uses' => 'TenderController@showByTenderID', 'as' => 'tender.showByID']);

// Password reset link request routes...
Route::get('password/email', ['uses' => 'Auth\PasswordController@getEmail', 'as' => 'password.email']);
Route::post('password/email', ['uses' => 'Auth\PasswordController@postEmail', 'as' => 'password.email']);

// Password reset routes...
Route::get('password/reset/{token}', ['uses' => 'Auth\PasswordController@getReset', 'as' => 'password.reset']);
Route::post('password/reset', ['uses' => 'Auth\PasswordController@postReset', 'as' => 'password.reset']);

//Route::resource('feed', 'FeedController');
Route::get('feed/{id}', 'FeedController@show');
Route::get('feed/protocol/{id}', 'FeedController@protocol');
Route::get('feed/result/{id}', 'FeedController@result');

Route::get('/template/lot/{index}', ['uses' => 'TemplateController@lot']);
Route::get('/template/item/{lot_index}/{index}', ['uses' => 'TemplateController@item']);
Route::get('/template/planitem/{index}/{plan?}', ['uses' => 'TemplateController@planItem']);
Route::get('/template/feature/{namespace}/{index}', ['uses' => 'TemplateController@feature']);
Route::get('/template/feature-value/{namespace}/{feature_index}/{index}', ['uses' => 'TemplateController@featureValue']);

    /*
         * Api
     */
Route::post('/api/users', ['uses' => 'ApiController@createUserWithOrg']);



Route::group(['middleware' => ['basicAuth']], function () {
    Route::get('/api/users/{organization_id}', ['uses' => 'ApiController@getUserById']);
    Route::put('/api/organizations/{organization_id}', ['uses' => 'ApiController@updateOrganization']);
    Route::post('/api/organizations/{organization_id}', ['uses' => 'ApiController@changeOrganizationMode']);


    //Api тендер и организация
    Route::post('/api/tenders', ['uses' => 'ApiController@tenders', 'as' => 'api.tender.create']); //создание органииции и публикация  тендера
    Route::get('/api/tenders/{tender}', ['uses' => 'ApiController@getTender', 'as' => 'api.tender.get']); // получение тендера
//    Route::get('/api/tenders/{tender}/', ['uses' => 'ApiController@getSignTenderData', 'as' => 'api.tender.get']); // получение тендера
    Route::patch('/api/tenders/{tender_id}', ['uses' => 'ApiController@updateTender']);
    //Api добавление документа к лоту тендера

    Route::get('/api/tenders/{id}/lots/{lotId}/documents', ['uses' => 'ApiController@getLotDocuments', 'as' => 'api.tender.lot.doc.get']); // получение всех документов по лоту
    Route::post('/api/tenders/{id}/lots/{lotId}/documents', ['uses' => 'ApiController@uploadLotDocument', 'as' => 'api.tender.lot.doc.create']);
    Route::put('/api/tenders/{id}/lots/{lotId}/documents/{document_id}', ['uses' => 'ApiController@updateLotDocument', 'as' => 'api.tender.lot.doc.update']); // обновление документа

    //Api добавление документов к айтему
    Route::get('/api/tenders/{id}/lots/{lotId}/items/{itemsId}/documents', ['uses' => 'ApiController@getItemDocument', 'as' => 'api.tender.lot.doc.get']); // получение всех документов по лоту
    Route::post('/api/tenders/{id}/lots/{lotId}/items/{itemId}/documents', ['uses' => 'ApiController@uploadItemDocument', 'as' => 'api.tender.lot.doc.create']);
    Route::put('/api/tenders/{id}/lots/{lotId}/items/{itemId}/documents/{document_id}', ['uses' => 'ApiController@updateItemDocument', 'as' => 'api.tender.lot.doc.update']); // обновление документа


    //Api Документы тендера
    Route::get('/api/tenders/{id}/documents', ['uses' => 'ApiController@getDocument', 'as' => 'api.tender.doc.get']); //список всех доков тендера
    Route::post('/api/tenders/{id}/documents', ['uses' => 'ApiController@uploadDocument', 'as' => 'api.tender.doc.create']); //добавление документов к тендеру
    Route::put('/api/tenders/{id}/documents/{document_id}', ['uses' => 'ApiController@updateDocument', 'as' => 'api.tender.doc.update']); // обновление документа

    //Api вопросы/ответы
    Route::patch('/api/tenders/{tid}/questions/{qid}', ['uses' => 'ApiController@putAnswer', 'as' => 'api.tender.question.answer']);
    Route::get('/api/tenders/{id}/questions', ['uses' => 'ApiController@getQuestions', 'as' => 'api.tender.question.list']);
    Route::get('/api/tenders/{id}/questions/{qid}', ['uses' => 'ApiController@getQuestion', 'as' => 'api.tender.question.show']);
    Route::post('/api/tenders/{id}/questions', ['uses' => 'ApiController@askQuestion']);

    // Api отмена тендера
    Route::get('/api/tenders/{id}/cancellations/{cancellation_id}/documents', ['uses' => 'ApiController@getCancelDocuments']); //список документов отмены
    Route::post('/api/tenders/{id}/cancellations', ['uses' => 'ApiController@cancelTender']); //отмена тендера статус pending
    Route::post('/api/tenders/{id}/cancellations/{cancellation_id}/documents', ['uses' => 'ApiController@addCancelDoc']); //добавление документа
    Route::put('/api/tenders/{id}/cancellations/{cancellation_id}/documents/{document_id}', ['uses' => 'ApiController@updateCancelDoc']); // обновление документа
    Route::patch('/api/tenders/{id}/cancellations/{cancellation_id}', ['uses' => 'ApiController@activateTenderCancellation']); //активация отмены тендера статус active

    //Api отмена лота
    Route::get('/api/tenders/{id}/lots/{lot_id}/cancellations/{cancellation_id}/documents', ['uses' => 'ApiController@getCancellationDocuments']);
    Route::post('/api/tenders/{id}/lots/{lot_id}/cancellations', ['uses' => 'ApiController@cancelLot']);
    Route::post('/api/tenders/{id}/lots/{lot_id}/cancellations/{cancellation_id}/documents', ['uses' => 'ApiController@addCancelLotDoc']);
    Route::put('/api/tenders/{id}/lots/{lot_id}/cancellations/{cancellation_id}/documents/{document_id}', ['uses' => 'ApiController@updateCancelLotDoc']);
    Route::patch('/api/tenders/{id}/lots/{lot_id}/cancellations/{cancellation_id}', ['uses' => 'ApiController@activateLotCancellation']);

    //Api вопросы по лоту
    Route::patch('/api/tenders/{tid}/lots/{lot_id}/questions/{qid}', ['uses' => 'ApiController@putLotAnswer']);
    Route::get('/api/tenders/{id}/lots/{lot_id}/questions', ['uses' => 'ApiController@getLotQuestions']);
    Route::get('/api/tenders/{id}/lots/{lot_id}/questions/{qid}', ['uses' => 'ApiController@getLotQuestion']);
    Route::post('/api/tenders/{id}/lots/{lot_id}/questions', ['uses' => 'ApiController@askLotQuestion']);

    // Api awards
    Route::get('/api/tenders/{id}/awards', ['uses' => 'ApiController@getAwards']);
    Route::get('/api/tenders/{id}/awards/{aid}', ['uses' => 'ApiController@getAward']);
    Route::patch('/api/tenders/{id}/awards/{award_id}', ['uses' => 'ApiController@acceptAward']);
    Route::delete('/api/tenders/{id}/awards/{award_id}', ['uses' => 'ApiController@reject']);
    Route::put('/api/tenders/{id}/awards/{award_id}', ['uses' => 'ApiController@cancel']);
    Route::post('/api/tenders/{id}/awards/{award_id}/documents', ['uses' => 'ApiController@addAwardProtocol']);
    Route::put('/api/tenders/{id}/awards/{award_id}/documents/{document_id}', ['uses' => 'ApiController@updateAwardProtocol']);

    // Api bid
    Route::get('/api/tenders/{id}/bids/{bid_id}', ['uses' => 'ApiController@getBid', 'as' => 'tender.award.bid']);

    // Api contracting
    Route::get('/api/tenders/{id}/contracts', ['uses' => 'ApiController@getContracts']);
    Route::get('/api/tenders/{id}/contracts/{contract_id}', ['uses' => 'ApiController@getContract']);
    Route::patch('/api/tenders/{id}/contracts/{contract_id}', ['uses' => 'ApiController@addDetailsToContract']);
    Route::post('/api/tenders/{id}/contracts/{contract_id}', ['uses' => 'ApiController@activateContract']);
    Route::post('/api/tenders/{id}/contracts/{contract_id}/documents', ['uses' => 'ApiController@addDocumentToContract']);


    // Api Changes
    Route::get('/api/contracts/{contract_id}', ['uses' => 'ApiController@getCbdContract']);
    Route::patch('/api/contracts/{contract_id}', ['uses' => 'ApiController@changeContract']);
    // добавление и просмотр изменения
    Route::post('/api/contracts/{contract_id}/changes', ['uses' => 'ApiController@createChange']);
    Route::get('/api/contracts/{contract_id}/changes/{change_id}', ['uses' => 'ApiController@getChange']);
    Route::patch('/api/contracts/{contract_id}/changes/{change_id}', ['uses' => 'ApiController@activateChange']);
    // документы изменения
    Route::post('/api/contracts/{contract_id}/documents', ['uses' => 'ApiController@addDocumentToChange']);
    Route::patch('/api/contracts/{contract_id}/documents/{doc_id}', ['uses' => 'ApiController@updateChangeDocument']);

    Route::get('/api/run', ['uses' => 'ApiController@runQueue']);

//    Route::('/', ['uses' => 'ApiController@activateChange']);
//    Route::('', ['uses' => 'ApiController@downloadDocToChange']);
//    Route::('', ['uses' => 'ApiController@updateChangeDoc']);

    // Api sign tender
    Route::get('/api/tenders/{id}/signs', ['uses' => 'TenderController@getSign']);
    Route::post('/api/tenders/{id}/signs', ['uses' => 'TenderController@postSign']);

    // Api sign plans
    Route::get('/api/plans/{id}/signs', ['uses' => 'PlanningController@getSign']);
    Route::post('/api/plans/{id}/signs', ['uses' => 'PlanningController@postSign']);

    // Api plans
    Route::post('/api/plans', ['uses' => 'ApiController@createPlan']);
    Route::get('/api/plans/{plan_id}', ['uses' => 'ApiController@getPlanById']);
    Route::patch('/api/plans/{plan_id}', ['uses' => 'ApiController@updatePlan']);

    Route::get('/api/tenders/{tender_id}/signdata', ['uses' => 'ApiController@getTenderByCbdId']);
    // User api reg


}); // END OF API


Route::controller('api', 'ApiController');

Route::group(['middleware' => ['auth', 'admin']], function () {

    Route::group(['as' => 'admin::', 'namespace' => 'Admin', 'prefix' => 'admin'], function () {

        Route::get('/', function () {
            return redirect()->route('admin::organization.index');
        });

        Route::group(['prefix' => 'complaint'], function () {
            Route::get('/index', ['uses' => 'ComplaintController@index', 'as' => 'complaint.index']);
            Route::get('/excel', ['uses' => 'ComplaintController@excel', 'as' => 'complaint.excel']);
        });

        Route::group(['prefix' => 'tender'], function () {
            Route::get('/', ['uses' => 'TenderController@index', 'as' => 'tender.index']);
            Route::get('/edit/{tender}', ['uses' => 'TenderController@edit', 'as' => 'tender.edit']);
            Route::put('/update/{tender}', ['uses' => 'TenderController@update', 'as' => 'tender.update']);
            Route::get('/publish/{tender}', ['uses' => 'TenderController@publish', 'as' => 'tender.publish']);
        });
        Route::group(['prefix' => 'user'], function () {
            Route::get('/', ['uses' => 'UserController@index', 'as' => 'user.index']);
            Route::get('/edit/{user}', ['uses' => 'UserController@edit', 'as' => 'user.edit']);
            Route::put('/update/{user}', ['uses' => 'UserController@update', 'as' => 'user.update']);
        });
        Route::group(['prefix' => 'organization'], function () {
            Route::get('/', ['uses' => 'OrganizationController@index', 'as' => 'organization.index']);
            Route::get('/edit/{organization}', ['uses' => 'OrganizationController@edit', 'as' => 'organization.edit']);
            Route::put('/update/{organization}', ['uses' => 'OrganizationController@update', 'as' => 'organization.update']);
            Route::get('/confirm/{organization}', ['uses' => 'OrganizationController@confirm', 'as' => 'organization.confirm']);
            Route::get('/checksign/{organization}', ['uses' => 'OrganizationController@checkSign', 'as' => 'organization.checksign']);
            Route::get('/search', ['uses' => 'OrganizationController@searchOrganization', 'as' => 'organization.search']);
            Route::get('/checksign/{organization}', ['uses' => 'OrganizationController@checkSign', 'as' => 'organization.check.sign']);
        });
        Route::group(['prefix' => 'payments'], function () {
            Route::get('/', ['uses' => 'PaymentsController@index', 'as' => 'payments.index']);
            Route::get('/pay', ['uses' => 'PaymentsController@pay', 'as' => 'payments.pay']);
            Route::get('/transactions', ['uses' => 'PaymentsController@transactions', 'as' => 'payments.transactions']);
            Route::get('/orders', ['uses' => 'PaymentsController@orders', 'as' => 'payments.orders']);
            Route::post('/add', ['uses' => 'PaymentsController@add', 'as' => 'payments.add']);
            Route::post('/removeCash', ['uses' => 'PaymentsController@removeCash', 'as' => 'payments.removeCash']);
            Route::get('/transaction/edit/{id}', ['uses' => 'PaymentsController@edit', 'as' => 'payments.edit']);
            Route::put('/transaction/update/{id}', ['uses' => 'PaymentsController@update', 'as' => 'payments.update']);
            Route::get('/transaction/delete/{id}', ['uses' => 'PaymentsController@delete', 'as' => 'payments.delete']);
//            Route::post('/comment', ['uses' => 'PaymentsController@addComment', 'as' => 'payments.comment']);
            Route::get('/clientbank', ['uses' => 'PaymentsController@clientbank', 'as' => 'payments.clientbank']);
            Route::post('/upload', ['uses' => 'PaymentsController@upload', 'as' => 'payments.upload']);
            Route::post('/commit', ['uses' => 'PaymentsController@commit', 'as' => 'payments.commit']);


        });
        Route::group(['prefix' => 'paysystem'], function () {
            Route::get('/', ['uses' => 'PaysystemController@index', 'as' => 'paysystem.index']);
            Route::get('/cashless', ['uses' => 'PaysystemController@cashless', 'as' => 'paysystem.cashless']);
            Route::get('/cashlessHistory', ['uses' => 'PaysystemController@cashlessHistory', 'as' => 'paysystem.cashlessHistory']);
            Route::get('/payHistory/{id}', ['uses' => 'PaysystemController@payHistory', 'as' => 'paysystem.payHistory']);
            Route::get('/manualPay/{id}', ['uses' => 'PaysystemController@manualPay', 'as' => 'paysystem.manualPay']);
            Route::get('/cashlessLiqPay', ['uses' => 'PaysystemController@LiqPayHistory', 'as' => 'paysystem.LiqPayHistory']);
            Route::post('/manualPay/refill', ['uses' => 'PaysystemController@manualRefill', 'as' => 'paysystem.manualRefill']);
            Route::post('/cashlessHistory/search', ['uses' => 'PaysystemController@cashlessHistorySearch', 'as' => 'paysystem.cashlessHistorySearch']);
            Route::get('/repay', ['uses' => 'PaysystemController@repay', 'as' => 'paysystem.repay']);
            Route::get('/repay/moneyback/{id}', ['uses' => 'PaysystemController@moneyback', 'as' => 'paysystem.moneyback']);
            Route::post('/repay/search', ['uses' => 'PaysystemController@repaySearch', 'as' => 'paysystem.repaySearch']);
            Route::get('/balance', ['uses' => 'PaysystemController@balance', 'as' => 'paysystem.balance']);
            Route::get('/balance/search', ['uses' => 'PaysystemController@balanceSearch', 'as' => 'paysystem.balanceSearch']);
            Route::post('/cashless/search', ['uses' => 'PaysystemController@search', 'as' => 'paysystem.search']);
            Route::get('/cashless/confirm/{id}', ['uses' => 'PaysystemController@confirm', 'as' => 'paysystem.confirm']);
            Route::post('/cashless/uploadcsv', ['uses' => 'PaysystemController@uploadcsv', 'as' => 'paysystem.uploadcsv']);
        });
        Route::group(['prefix' => 'email'], function () {
            Route::get('/', ['uses' => 'EmailController@index', 'as' => 'email.index']);
            Route::post('/updateTemplate', ['uses' => 'EmailController@updateTemplate', 'as' => 'email.updateTemplate']);
            Route::post('/restoreCopy', ['uses' => 'EmailController@restoreCopy', 'as' => 'email.restoreCopy']);
        });
        Route::group(['prefix' => 'notification'], function () {
            Route::get('/', ['uses' => 'NotificationController@index', 'as' => 'notification.index']);
            Route::post('/store', ['uses' => 'NotificationController@store', 'as' => 'notification.store']);
        });
        Route::group(['prefix' => 'bids'], function () {
            Route::get('/', ['uses' => 'BidsController@index', 'as' => 'bids.index']);
            Route::get('/bids/{id}/edit', ['uses' => 'BidsController@edit', 'as' => 'bids.edit']);

        });
        Route::group(['prefix' => 'replication'], function () {
            Route::get('/', ['uses' => 'ReplicationController@index', 'as' => 'replication.index']);
        //    Route::get('/truncate ', ['uses' => 'ReplicationController@truncate', 'as' => 'replication.truncate']);

        });
        Route::group(['prefix' => 'relogin'], function () {
           Route::get('/', ['uses' => 'ReloginHistoryController@index', 'as' => 'relogin.index']);
        });
        Route::group(['prefix' => 'git'], function() {
            Route::get('/list',          ['uses' => 'GitController@listsAllRemoteBranches', 'as' => 'git.list']);
            Route::post('/switchBranch', ['uses' => 'GitController@switchBranch',           'as' => 'git.switchBranch']);
            Route::post('/make',         ['uses' => 'GitController@make',                   'as' => 'git.make']);
        });

        Route::group(['prefix' => 'agent'], function() {
            Route::get('/agent', ['uses' => 'AgentController@index', 'as' => 'agent.index']);
            Route::get('/active', ['uses' => 'AgentController@activeIndex', 'as' => 'agent.activeList']);
            Route::get('/agent/{id}', ['uses' => 'AgentController@edit', 'as' => 'agent.edit']);
            Route::put('/agent/{id}', ['uses' => 'AgentController@update', 'as' => 'agent.update']);
            Route::get('/search', ['uses' => 'AgentController@search', 'as' => 'agent.search']);

        });

        Route::group(['prefix' => 'db'], function () {
            Route::get('/queue', ['uses' => 'DBController@showQueue', 'as' => 'db.showQueue']);
        });

        Route::group(['prefix' => 'stats'], function () {
            Route::get('/organizations', ['uses' => 'StatsController@organizations', 'as' => 'stats.organizations']);
        });
    }); // END OF ADMIN ROUTE GROUP
}); //END OF ADMIN MIDDLEWARE

Route::group(['middleware' => ['auth', 'log']], function () {

    Route::get('/platon', function () {
        return view('test.platon');
    });
    Route::get('/platon/success', function () {
        print "Дякуємо, Ваш рахунок буде поповнено.";
    });

    Route::get('/notification/unsubscribe', ['uses' => 'NotificationController@unsubscribe', 'as' => 'notification.unsubscribe']);
    Route::get('/notification/subscribe', ['uses' => 'NotificationController@subscribe', 'as' => 'notification.subscribe']);

    /*
     * User
     */

    Route::get('/logout', ['uses' => 'UserController@logout', 'as' => 'user.logout']);
    Route::get('/relogin/{id}', ['uses' => 'UserController@relogin', 'as' => 'user.relogin']);
    Route::get('/offer', ['uses' => 'UserController@showOffer', 'as' => 'user.offer']);
    Route::post('/keep-alive', ['uses' => 'UserController@keepAlive', 'as' => 'user.keep-alive']);

    Route::group(['middleware' => ['active']], function () {

        /*
         * General
         */

        Route::get('/home', ['uses' => 'TenderController@listTenders', 'as' => 'user.home.page']);
        Route::get('/', ['uses' => 'TenderController@listTenders', 'as' => 'home']);
        Route::get('/getTenders', ['uses' => 'TenderController@getTenders', 'as' => 'getTenders']);
        Route::get('/getBids', ['uses' => 'BidController@getBids', 'as' => 'getBids']);


        /*
         * User
         */
        Route::get('/user/edit', ['uses' => 'UserController@edit', 'as' => 'user.edit']);
        Route::put('/user/update', ['uses' => 'UserController@update', 'as' => 'user.update']);

        /*
         * Organization
         */

        Route::post('/organization/contacts', ['uses' => 'OrganizationController@contacts', 'as' => 'organization.contacts']);

        Route::get('/organization/edit', ['uses' => 'OrganizationController@edit', 'as' => 'organization.edit']);
        Route::get('/organization/home', ['uses' => 'OrganizationController@home', 'as' => 'organization.home']);
        Route::get('/organization/mode', ['uses' => 'OrganizationController@mode', 'as' => 'organization.mode']);
        Route::get('/organization/switchType', ['uses' => 'OrganizationController@switchType', 'as' => 'organization.switch']);
        Route::put('/organization', ['uses' => 'OrganizationController@update', 'as' => 'organization']);
        Route::resource('organization', 'OrganizationController', ['except' => ['edit']]);


        /**
         * Мидлвер organization заставляет регать организацию если ее нет
         * Мидлвер role показывает 403 для тех кого нет в списке
         */
        Route::group(['middleware' => ['organization', 'role:supplier']], function () {
            Route::resource('agent', 'AgentController');
            Route::get('/agent/archive/{id}', ['uses' => 'AgentController@archive', 'as' => 'agent.archive']);
        });

        Route::group(['middleware' => ['organization', 'role:supplier|customer']], function () {


            Route::get('/tender/create/{procedureType}', ['uses' => 'TenderController@create', 'as' => 'tender.create']);
            Route::get('/tender/{tender}/publish', ['uses' => 'TenderController@publish', 'as' => 'tender.publish']);
            Route::get('/tender/classifier/{type}', ['uses' => 'TenderController@classifier', 'as' => 'tender.classifier']);
            Route::get('/tender/{tender}/bids', ['uses' => 'BidController@index', 'as' => 'tender.bids']);
            Route::get('/tender/{tender}/participants', ['uses' => 'TenderController@participants', 'as' => 'tender.participants']);
            Route::get('/tender/{id}/qualifications', ['uses' => 'BidController@qualifications', 'as' => 'bid.qualifications']);
            Route::post('/draftTender/store',  ['uses' => 'TenderController@storeDraft', 'as' => 'tender.storeDraft']);
            Route::match(['put', 'post'], '/draftTender/update',  ['uses' => 'TenderController@updateDraft', 'as' => 'tender.updateDraft']);


            Route::delete('/tender/delete/{id}', ['uses' => 'TenderController@destroy', 'as' => 'tender.delete']);
            Route::get('/tender/test', ['uses' => 'TenderController@test', 'as' => 'tender.test']);


            Route::post('/tender/{id}/sign',  ['uses' => 'TenderController@postSign', 'as' => 'tender.postSign']);

            Route::post('/tender/{id}/qualify',  ['uses' => 'TenderController@qualify', 'as' => 'tender.qualify']);
            Route::get('/tender/{id}/completeFirstStage',  ['uses' => 'TenderController@completeFirstStage', 'as' => 'tender.completeFirstStage']);
            Route::get('/tender/{id}/publishSecondStage',  ['uses' => 'TenderController@publishSecondStage', 'as' => 'tender.publishSecondStage']);

            Route::get('/tender/{id}/sync',  ['uses' => 'TenderController@sync', 'as' => 'tender.sync']);


            Route::resource('tender', 'TenderController', ['except' => ['show']]);


            /*
             * Questions
             */
            Route::get('/questions/{entity}/{id}', ['uses' => 'QuestionController@create', 'as' => 'questions.create']);
            Route::get('/questions/list/{entity}/{id}', ['uses' => 'QuestionController@index', 'as' => 'questions.index']);
            Route::post('/questions/answer/{id}', ['uses' => 'QuestionController@answer', 'as' => 'questions.answer']);
            Route::get('/questions/list', ['uses' => 'QuestionController@showUsersQuestions', 'as' => 'questions.userlist']);
            Route::get('/questions/customer', ['uses' => 'QuestionController@showCustomersQuestions', 'as' => 'questions.Customerslist']);
            Route::resource('question', 'QuestionController');

            /*
             * Cancellation
             */

            Route::get('/cancel/deletedoc/{id}', ['uses' => 'CancellationController@destroy', 'as' => 'cancel.docs.destroy']);
            Route::get('/cancel/create/{entity}/{id}', ['uses' => 'CancellationController@create', 'as' => 'cancel.create']);
            Route::get('/cancel/activate/{cancel}', ['uses' => 'CancellationController@activate', 'as' => 'cancel.activate']);
            Route::resource('cancellation', 'CancellationController');

            /*
             * Complaint
             */

            Route::get('/complaint/create/{entity}/{id}', ['uses' => 'ComplaintController@create', 'as' => 'claim.create']);
            Route::get('/complaint/activate/{complaint}', ['uses' => 'ComplaintController@activate', 'as' => 'claim.activate']);
            Route::get('/complaint/listing', ['uses' => 'ComplaintController@listing', 'as' => 'claim.list']);
            Route::get('/complaint/list/{entity}/{id}', ['uses' => 'ComplaintController@index', 'as' => 'claim.index']);
            Route::get('/complaint/claim/{id}', ['uses' => 'ComplaintController@claim', 'as' => 'claim.claim']);
            Route::get('/complaint/complaint/{id}', ['uses' => 'ComplaintController@complaint', 'as' => 'claim.complaint']);
            Route::post('/complaint/cancel', ['uses' => 'ComplaintController@cancelComplaint', 'as' => 'cancel.complaint']);
            Route::resource('complaint', 'ComplaintController');

            /*
             * Contract
            */

            Route::get('/contract/deletedoc/{id}', ['uses' => 'ContractController@deleteDoc', 'as' => 'contract.docs.delete']);
            Route::get('/contract/create/{tender}', ['uses' => 'ContractController@create', 'as' => 'tender.contract.create']);
            Route::get('/contract/activate/{tender}', ['uses' => 'ContractController@activate', 'as' => 'contract.activate']);
            Route::get('/contract/change/activate/{id}', ['uses' => 'ContractController@activateChange', 'as' => 'contract.change.activate']);
            Route::get('/contract/list/{tender}', ['uses' => 'ContractController@index', 'as' => 'tender.contracts']);
            Route::get('/contract/{id}/change', ['uses' => 'ContractController@change', 'as' => 'contract.change']);
            Route::get('/contract/{id}/terminate', ['uses' => 'ContractController@terminate', 'as' => 'contract.terminate']);
            Route::get('/contract/{id}/terminated', ['uses' => 'ContractController@terminated', 'as' => 'contract.terminated']);
            Route::get('/contract/{id}/term', ['uses' => 'ContractController@term', 'as' => 'contract.term']);
            Route::post('/contract/{id}/change', ['uses' => 'ContractController@changeStore', 'as' => 'contract.change.store']);
            Route::get('/contract/{id}/sign',  ['uses' => 'ContractController@getSign', 'as' => 'contract.getSign']);
            Route::post('/contract/{id}/sign',  ['uses' => 'ContractController@postSign', 'as' => 'contract.postSign']);

            Route::resource('contract', 'ContractController');

            /*
             * Bids and Awards
             */

            Route::get('/bid/deletedoc/{id}', ['uses' => 'BidController@destroy', 'as' => 'bid.docs.destroy']);
            Route::get('/bid/list', ['uses' => 'BidController@listing', 'as' => 'bid.list']);
            Route::post('/bid/upload/{bid}', ['uses' => 'BidController@upload', 'as' => 'bid.upload']);
            Route::post('/bid/uploaddoc/{bid}', ['uses' => 'BidController@uploadDoc', 'as' => 'bid.uploadDoc']);
            Route::post('/bid/qualify/{id}', ['uses' => 'BidController@qualify', 'as' => 'bid.qualify']);
            Route::get('/bid/qualification/{id}', ['uses' => 'BidController@qualification', 'as' => 'bid.qualification']);
            Route::match(['get', 'put'], '/bid/reject/{bid}', ['uses' => 'BidController@reject', 'as' => 'bid.reject']);
            Route::match(['get', 'put'], '/bid/confirm/{bid}', ['uses' => 'BidController@confirm', 'as' => 'bid.confirm']);
            Route::get('/bid/reject/{bid}/form', ['uses' => 'BidController@rejectForm', 'as' => 'bid.reject.form']);
            Route::get('/bid/confirm/{bid}/form', ['uses' => 'BidController@confirmForm', 'as' => 'bid.confirm.form']);
            Route::delete('/bid/delete/{bid}', ['uses' => 'BidController@delete', 'as' => 'bid.delete']);
            Route::get('/bid/new/{entity}/{id}', ['uses' => 'BidController@create', 'as' => 'bid.new']);
            Route::get('/bid/adminupdate/{id}', ['uses' => 'BidController@updateBid', 'as' => 'bid.adminupdate']);
            Route::get('/bid/download/{id}', ['uses' => 'BidController@download', 'as' => 'bid.download']);
            Route::get('/bid/{id}/sign', ['uses' => 'BidController@getSign', 'as' => 'bid.getSign']);
            Route::post('/bid/{id}/sign', ['uses' => 'BidController@postSign', 'as' => 'bid.postSign']);
            Route::get('/bid/{id}/downloadall', ['uses' => 'BidController@downloadAll', 'as' => 'bid.docs.download.all']);

            Route::post('/bid/filter', ['uses' => 'BidController@filter', 'as' => 'bid.filter']);

            Route::get('/getBids', ['uses' => 'BidController@getBids', 'as' => 'getBids']);

            Route::resource('bid', 'BidController');


            /*
             * Awards
             */
            Route::get('/award/tender/{tender}', ['uses' => 'AwardController@tender', 'as' => 'award.tender']);
            Route::get('/award/create/{tender}', ['uses' => 'AwardController@create', 'as' => 'award.define']);
            Route::get('/award/lists/{tender}', ['uses' => 'AwardController@lists', 'as' => 'award.list']);
            Route::get('/award/admin/{id}', ['uses' => 'BidController@admin', 'as' => 'award.admin']);
            Route::get('/award/{id}/sign', ['uses' => 'AwardController@getSign', 'as' => 'award.getSign']);
            Route::post('/award/{id}/sign', ['uses' => 'AwardController@postSign', 'as' => 'award.postSign']);
            Route::resource('award', 'AwardController');


            /*
             * Planning
             */
            Route::get('/plan/list', ['uses' => 'PlanningController@lists', 'as' => 'plan.list']);
            Route::post('/plan/{id}/sign', ['uses' => 'PlanningController@postSign', 'as' => 'plan.postSign']);
            Route::get('/plan/import', ['uses' => 'PlanningController@createImport', 'as' => 'plan.createImport']);
            Route::post('/plan/import', ['uses' => 'PlanningController@importPlan', 'as' => 'plan.import']);
            Route::post('/plan/filter', ['uses' => 'PlanningController@filter', 'as' => 'plan.filter']);

            Route::resource('plan', 'PlanningController');


            /*
             * Documents
             */

            Route::get('/document/download/{id}/{entity?}', ['uses' => 'DocumentController@download', 'as' => 'document.download']);
            Route::resource('documents', 'DocumentController');

            /*
             * Notifications
             */
            Route::resource('notification', 'NotificationController');
            Route::post('/notification/readble', ['uses' => 'NotificationController@readble', 'as' => 'notification.readble']);
            Route::post('/notification/bidConfirm', ['uses' => 'NotificationController@bidConfirm', 'as' => 'notification.bidConfirm']);
            /*
             * PaySystems
             */
            Route::post('/paysystems/index', ['uses' => 'PaymentController@index', 'as' => 'Payment.index']);
            Route::get('/paysystems/payment', ['uses' => 'PaymentController@pay', 'as' => 'Payment.pay']);
            Route::get('/paysystems/print', ['uses' => 'PaymentController@printOrder', 'as' => 'Payment.print']);
            Route::get('/paysystems/saveaspdf', ['uses' => 'PaymentController@saveAsPdf', 'as' => 'Payment.saveAsPdf']);
            Route::get('/paysystems/loadview', ['uses' => 'PaymentController@loadview', 'as' => 'Pay.loadview']);

            /*
            * Test
            */
            Route::get('/test/index', ['uses' => 'TestController@index', 'as' => 'TestController.index']);

        });

        /*
         * Tender
         */
        Route::group(['middleware' => ['organization', 'role:supplier|customer|guest']], function () {

            Route::get('/tender/list', ['uses' => 'TenderController@listTenders', 'as' => 'tender.list']);
            Route::get('/tender/{id}', ['uses' => 'TenderController@show', 'as' => 'tender.show']);
            Route::get('/plan/{id}/sign', ['uses' => 'PlanningController@getSign', 'as' => 'plan.getSign']);
            Route::get('/tender/{id}/sign',  ['uses' => 'TenderController@getSign', 'as' => 'tender.getSign']);
            Route::post('/tender/filter', ['uses' => 'TenderController@filter', 'as' => 'tender.filter']);

        });


    });
});


//});
