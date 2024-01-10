<?php
namespace RussianProtein\iikoTransport;

use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use App\Models\User;

class iikoTransport
{

    public function __construct($iikoLogin = null)
    {
        $this->iikoLogin = $iikoLogin;

        if(config('iikoTransport.debug'))
            $this->guzzleClient = app('GuzzleClient')(['base_uri' => 'https://api-ru.iiko.services/']);
        else
            $this->guzzleClient = new Client();
    }

    private function TelegramHandler($method, $error)
    {
        if(!config('iikoTransport.debug'))
            return;

        $chat_ids = [
            config('iikoTransport.channels.telegram.chat_id')
        ];

        //typing errors
        if(stristr($error, "Error converting value")){
            $enum = "<b>Был передан неверный ID'шник организации.</b>";
        }elseif(stristr($error, "Timeout exceeded")){
            $enum = "<b>Данный метод не ответил во время. Что-то с транспортом или терминалом.</b>";
        }else{
            $enum = "<b>Неизвестно</b>";
        }

        //Check if user auth
        if(isset(auth('api')->user()->id)){
            $userdata = User::find(auth('api')->user()->id);
            //info auth user
            $user = "Телефон пользователя: ".$userdata->phone."\nПлатформа: ".$userdata->os."\n\n";
        }

        $data = @$user."Предполагаемая ошибка: \n".$enum."\n\n<b>Запрос:</b>\n\n".$method."\n\n<b>Ответ:</b>\n\n".$error;


        foreach ($chat_ids as $chat_id) {
            $client = new Client();

            $client->request('POST', 'https://api.telegram.org/bot'.config('iikoTransport.channels.telegram.bot_key').'/sendMessage', [
                'form_params' => [
                    'chat_id' => $chat_id,
                    'text' => urldecode($data),
                    'parse_mode' => 'HTML'
                ],
                'http_error' => false
            ]);
        }
    }

    public function getToken()
    {
        try{
            $url = "";

            $client = $this->guzzleClient;

            $body = ['apiLogin' => $this->iikoLogin ? $this->iikoLogin : config('iikoTransport.apiLogin')];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/access_token', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data->token;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

            $error = json_decode($response->getBody(), true);

            $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return response()->json(['statusCode' => $statusCode, 'response' => $error]);
        }
    }


    public function getOrganizations($organizationIds = null, $returnAdditionalInfo = false, $includeDisabled = false)
    {

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds, 'returnAdditionalInfo' => $returnAdditionalInfo, 'includeDisabled' => $includeDisabled];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/organizations', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();


              $error = json_decode($response->getBody(), true);

              $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getNomenclature($organizationId, $startRevision = 0)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'startRevision' => $startRevision];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/nomenclature', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));


            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

    public function getTerminal($organizationIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds, 'includeDisabled' => false];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/terminal_groups', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getTerminalIsAlive($organizationIds, $terminalGroupIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds, 'terminalGroupIds' => $terminalGroupIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/terminal_groups/is_alive', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDiscounts($organizationIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];
            //dd(json_encode($body));
            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/discounts', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            // dd($res);

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            // dd($e);
            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }



    public function createOrder($organizationId, $terminal, $orderIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId,
            'terminalGroupId' => $terminal,
            'createOrderSettings' => ['transportToFrontTimeout' => 300, "mode" => "Async", "transportToFrontTimeout" => 120], 'order' => $orderIds];
            //dd(json_encode($body));
            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/create', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            // dd($res);

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            // dd($e);
            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getOrderById($organizationId, $orderIds){
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'orderIds' => $orderIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/by_id', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function updateStatus($organizationId, $orderId, $status)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'orderId' => $orderId, 'deliveryStatus' => $status];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/update_order_delivery_status', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function checkOrder($organizationId, $orderIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId,  'createOrderSettings' => ['transportToFrontTimeout' => 300], 'terminalGroupId' => 'fb2a900f-9011-4e37-8059-2a5152b5b64d', 'order' => $orderIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/check_create', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function saveDraft($organizationId, $orderId)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'order' => $orderId];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/drafts/save', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\BadResponseException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function sendFront($organizationId, $orderId)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'terminalGroupId' => 'c880392b-0fe7-4006-a328-3b4a8843fbab', 'createOrderSettings' => ['transportToFrontTimeout' => 120], 'order' => $orderId];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/drafts/commit', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getPaymentType($organizationIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/payment_types', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getOrderId($organizationId, $orderIds)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'orderIds' => $orderIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/by_id', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getRegions($organizationIds){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/regions', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getCities($organizationIds){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/cities', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getAddress($organizationId, $cityId){
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'cityId' => $cityId];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/streets/by_city', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getAllowedDelivery($organizationIds, $lat, $long){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds, 'orderLocation' => ['latitude' => $lat, 'longitude' => $long], 'isCourierDelivery' => true];
            // dump(json_encode($body));
            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/delivery_restrictions/allowed', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            //dump($data);

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getZoneDelivery($organizationIds){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/delivery_restrictions', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

            $error = json_decode($response->getBody(), true);

            $this->TelegramHandler(substr(Psr7\Message::toString($e->getRequest()), 0, 500), substr(Psr7\Message::toString($e->getResponse()), 0, 500));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getStopList($organizationIds){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/stop_lists', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getDeliveryByPhone($organizationIds, $phone){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds, 'phone' => $phone];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/by_delivery_date_and_phone', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDeliveryByPhoneAndDate($organizationIds, $deliveryDateFrom, $deliveryDateTo, $phone)
    {
        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds, 'deliveryDateFrom' => $deliveryDateFrom, 'deliveryDateTo' => $deliveryDateTo, 'phone' => $phone];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/by_delivery_date_and_phone', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDeliveryById($organizationId, $orderIds){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'orderIds' => [$orderIds]];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/deliveries/by_id', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDeliveryZoneList($organizationIds){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationIds' => $organizationIds];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/delivery_restrictions', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

    public function setWebhookTransport($organizationId, $url){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId, 'webHooksUri' => $url, 'authToken' => $this->iikoLogin ? $this->iikoLogin : config('iikoTransport.apiLogin')];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/webhooks/update_settings', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

    public function getWebhookTransport($organizationId, $url){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = ['organizationId' => $organizationId];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/webhooks/settings', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

    public function getCombosInfo($organizationId){

        $token = $this->getToken();

        try{

            $client = $this->guzzleClient;

            $body = [
                'extraData' => true,
                'organizationId' => $organizationId
            ];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/combo', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer '.$token
                ],
                'http_error' => false
            ]);

            $data = json_decode($res->getBody());

            return $data;

        } catch (\GuzzleHttp\Exception\RequestException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

             $this->TelegramHandler(Psr7\Message::toString($e->getRequest()), Psr7\Message::toString($e->getResponse()));

            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

}
