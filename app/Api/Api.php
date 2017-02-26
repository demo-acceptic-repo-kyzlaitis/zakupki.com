<?php

namespace App\Api;

use App\Api\Struct\CancelDocument;
use App\Api\Struct\Structure;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Exception\ClientException;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Api
{
    public $responseCode;

    protected $_url;
    protected $_key;
    public $startListPage = '/tenders?feed=changes&descending=1&mode=_all_';
    public $namespace = 'tenders';
    public $updateMode = 'all';


    protected $_client;
    protected $_nextPage;
    protected $_currentOffset;
    protected $_logger;

    public function __construct($exception = true, $public = false)
    {
        $this->_url = $public ? env('PRZ_API_PUBLIC') : env('PRZ_API');
        $this->_key = env('PRZ_KEY');

        $cookieFile = storage_path('app').'/'.md5($this->_url).'_cookies.'.$this->updateMode.'.'.$this->namespace.'.txt';
        $this->_logger = new Logger('API');
        $this->_logger->pushHandler(new RotatingFileHandler(storage_path('logs/API.log')));

        $this->_client = new \GuzzleHttp\Client(['verify' => false, 'http_errors' => $exception, 'cookies' => new FileCookieJar($cookieFile, true)]);
    }

    public function getNextPage() {
        return (isset($this->_nextPage)) ? $this->_nextPage : '';
    }

    public function getNextPageUri()
    {
        $suffix = $this->updateMode.'.'.$this->namespace;
        if (file_exists(storage_path('app')."/".md5($this->_url).".api.nextpage.$suffix.txt")) {
            $this->_nextPage = file_get_contents(storage_path('app')."/".md5($this->_url).".api.nextpage.$suffix.txt");
            print "Get next page from file\n";

            return;
        }
        $url = $this->_url.$this->startListPage;
        print $url."\n";
        $response = $this->_client->get($url);
        $result = json_decode((string) $response->getBody(), true);
        if (isset($result['prev_page']['uri'])) {
            $response = $this->_client->get($result['prev_page']['uri']);
            $result = json_decode((string) $response->getBody(), true);
            $this->_nextPage = $result['next_page']['uri'];
            file_put_contents(storage_path('app')."/".md5($this->_url).".api.nextpage.$suffix.txt", $this->_nextPage);
            print "Get next page from JSON\n";

            return $result;
        }
    }

    public function getList($url = '')
    {
        $suffix = $this->updateMode.'.'.$this->namespace;
        if (empty($url)) {
            $url = $this->_url.$this->startListPage;
        }
        $response = $this->_client->get($url);
        $result = json_decode((string) $response->getBody(), true);
        $result['current_uri'] = $url;
        if ($response->getStatusCode() != 200) {
            unlink(storage_path('app')."/".md5($this->_url).".api.nextpage.$suffix.txt");

            return $this->getNextPageUri();
        }

        if (isset($result['next_page']['uri'])) {
            $this->_nextPage = $result['next_page']['uri'];
            file_put_contents(storage_path('app')."/".md5($this->_url).".api.nextpage.$suffix.txt", $this->_nextPage);
            if ($this->_currentOffset != $result['next_page']['offset']) {
                $this->_currentOffset = $result['next_page']['offset'];
            } else {

                return false;
            }

        }

        return $result;
    }

    public function getNext()
    {
        $uri = '';
        if (!empty($this->_nextPage)) {
            $uri = $this->_nextPage;
        }

        return $this->getList($uri);
    }

    public function get($id, $path = '', $token = '')
    {
        $url = $this->_url."/{$this->namespace}/".$id;
        if (!empty($path)) {
            $url .= '/'.$path;
        }
        if (!empty($token)) {
            $url .= "?acc_token=$token";
        }
        $reqId = $this->uuid4();
//        $this->logRequest('GET', $reqId, $url, []);
        try {
            $response = $this->_client->get($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'auth' =>  [$this->_key, $this->_key]
            ]);
//            $this->logResponse($response);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
//            $this->logResponse($e->getResponse(), 'error');
            return null;
        }
    }

    public function getRaw($url)
    {
        $reqId = $this->uuid4();
        try {
            $response = $this->_client->get($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'auth' =>  [$this->_key, $this->_key]
            ]);

            return $response->getBody();
        } catch (ClientException $e) {
            return null;
        }
    }



    public function post(Structure $structure)
    {
        $url = $this->_url.$structure->uri;
        $data['data'] = $structure->getData();
        $reqId = $this->uuid4();
        if ($structure->isNew) {
            $this->logRequest('POST', $reqId, $url, $data);
            $method = 'post';
        } else {
            $this->logRequest('PUT', $reqId, $url, $data);
            $method = 'put';
        }
        try {
            $response = $this->_client->$method($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'json' => $data,
                'auth' =>  [$this->_key, $this->_key]
            ]);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
                $response = $e->getResponse();
            }
        }

        return json_decode((string)$response->getBody(), true);
    }

    public function postRaw($url, $data)
    {
        $url = $this->_url.$url;
        $reqId = $this->uuid4();
        $this->logRequest('POST', $reqId, $url, $data);
        try {
            $response = $this->_client->post($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'json' => $data,
                'auth' =>  [$this->_key, $this->_key]
            ]);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException $e) {
            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
                $response = $e->getResponse();
            }
        }
        return json_decode((string)$response->getBody(), true);
    }

    public function patch(Structure $structure)
    {
        $url = $this->_url.$structure->uri;
        $data['data'] = $structure->getData();
        $reqId = $this->uuid4();
        $this->logRequest('PATCH', $reqId, $url, $data);
        try {
            $response = $this->_client->patch($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'json' => $data,
                'auth' =>  [$this->_key, $this->_key]
            ]);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException  $e) {

            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
                $response = $e->getResponse();
            }
        }

        return json_decode((string)$response->getBody(), true);
    }

    public function patchRaw($url, $data)
    {
        $url = $this->_url.$url;
        try {
            $reqId = $this->uuid4();
            $this->logRequest('PATCH', $reqId, $url, $data);
            $response = $this->_client->patch($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'json' => $data,
                'auth' =>  [$this->_key, $this->_key]
            ]);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException  $e) {
            $response = $e->getResponse();
            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
            }
        }

        return json_decode((string)$response->getBody(), true);
    }

    public function delete(Structure $structure)
    {
        $url = $this->_url.$structure->uri;
        $data['data'] = $structure->getData();
        $reqId = $this->uuid4();
        $this->logRequest('DELETE', $reqId, $url, $data);
        try {
            $response = $this->_client->delete($url, [
                'headers' => ['X-client-request-id' => $reqId],
                'json' => $data,
                'auth' =>  [$this->_key, $this->_key]
            ]);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException  $e) {

            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
            }
        }

        return json_decode((string)$response->getBody(), true);
    }

    public function upload($document)
    {
        $url = $document->uploadUrl;
        $reqId = $this->uuid4();
        try {
            $postData =  [
                'headers' => [
                    'X-client-request-id' => $reqId,
                    'X-Access-Token' => $document->access_token
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($document->getFullPath(), 'r')
                    ]
                ],
                'auth' =>  ['zakupki.com.ua', env('DS_KEY')]
            ];
            $this->logRequest('UPLOAD', $reqId, $url, $postData);
            $response = $this->_client->request('POST', $url, $postData);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException  $e) {
            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
            }
        }

        return json_decode((string)$response->getBody(), true);

    }

    public function registerDoc($hash)
    {
        $url = env('DS_URL');
        $reqId = $this->uuid4();
        try {
            $postData =  [
                'headers' => ['X-client-request-id' => $reqId],
                'multipart' => [
                    [
                        'name'     => 'hash',
                        'contents' => 'md5:'.$hash
                    ]
                ],
                'auth' =>  ['zakupki.com.ua', env('DS_KEY')]
            ];
            $this->logRequest('REGISTER', $reqId, $url, $postData);
            $response = $this->_client->request('POST', $url, $postData);
            $this->responseCode = $response->getStatusCode();
            $this->logResponse($response);
        } catch (ClientException  $e) {
            if ($e->hasResponse()) {
                $this->logResponse($e->getResponse(), 'error');
            }
        }

        return json_decode((string)$response->getBody(), true);

    }

    public function uploadCancel(CancelDocument $document)
    {
        $url = $this->_url.$document->uri;
        try {
            $response = $this->_client->request($document->isNew ? 'POST' : 'PUT', $url, [
                'headers' => [
                    'X-Access-Token' => $document->access_token
                ],
                'multipart' => [
                    [
                        'name'     => 'file',
                        'filename' => $document->title,
                        'contents' => fopen($document->getFullPath(), 'r')
                    ],
                ],
                'auth' =>  [$this->_key, $this->_key]
            ]);
        } catch (ClientException  $e) {

            if ($e->hasResponse()) {
                $e->getResponse();
            }
        }

        return json_decode((string)$response->getBody(), true);

    }

    public function logRequest($requestType, $reqId, $url, $data)
    {
        $this->_logger->info($reqId."\t[$requestType]\t[$url]\t".json_encode($data));

    }

    public function logResponse($response, $type = 'info')
    {
        $reqId = $response->getHeaderLine('X-Request-Id');
        $this->_logger->{$type}($reqId."\t[".$response->getStatusCode()."]\t".(string)$response->getBody());
    }

    public function uuid4()
    {
        return 'req-zak-'.str_replace('.', '', uniqid('', true));
    }
}
