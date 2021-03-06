<?php
namespace RussianProtein\iikoTransport;

use GuzzleHttp\Client;
use Log;

class iikoTransport
{
    private function TelegramHandler($error)
    {
        $chat_ids = [
            env('TELEGRAM_ID')
        ];

        foreach ($chat_ids as $chat_id) {
            $url = sprintf(
                'https://api.telegram.org/bot'.env('TELEGRAM_KEY').'/sendMessage?chat_id=%s&text=%s',
                $chat_id,
                $error
            );

            file_get_contents($url);
        }
    }

    public function getToken()
    {
        try{

            $client = new Client();

            $body = ['apiLogin' => env('IIKO_API_LOGIN', '75782002')];

            $res = $client->request('POST', 'https://api-ru.iiko.services/api/1/access_token', [
                'body' => json_encode($body),
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'http_error' => false
            ]);

            $statusCode = $res->getStatusCode();

            $data = json_decode($res->getBody());

            return $data->token;

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);


            $this->TelegramHandler($response->getBody());

            return response()->json(['statusCode' => $statusCode, 'response' => $error]);
        }
    }


    public function getOrganizations($organizationIds = null, $returnAdditionalInfo = false, $includeDisabled = false)
    {

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();


              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getNomenclature($organizationId, $startRevision = 0)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());


            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

    public function getTerminal($organizationIds)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDiscounts($organizationIds)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            // dd($e);
            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }



    public function createOrder($organizationId, $terminal, $orderIds)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

            $body = ['organizationId' => $organizationId,
            'terminalGroupId' => $terminal,
            'createOrderSettings' => ['transportToFrontTimeout' => 300, "mode" => "Async", "transportToFrontTimeout" => 60], 'order' => $orderIds];
            Log::info('createOrder');
            Log::info(json_encode($body));
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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            // dd($e);
            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getOrderById($organizationId, $orderIds){
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function updateStatus($organizationId, $orderId, $status)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function checkOrder($organizationId, $orderIds)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function saveDraft($organizationId, $orderId)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function sendFront($organizationId, $orderId)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

            $body = ['organizationId' => $organizationId, 'terminalGroupId' => 'c880392b-0fe7-4006-a328-3b4a8843fbab', 'createOrderSettings' => ['transportToFrontTimeout' => 100], 'order' => $orderId];

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getPaymentType($organizationIds)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getOrderId($organizationId, $orderIds)
    {
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getRegions($organizationIds){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getCities($organizationIds){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getAddress($organizationId, $cityId){
        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getAllowedDelivery($organizationIds, $lat, $long){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getZoneDelivery($organizationIds){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getStopList($organizationIds){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }


    public function getDeliveryByPhone($organizationIds, $phone){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDeliveryById($organizationId, $orderIds){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }
    }

    public function getDeliveryZoneList($organizationIds){

        $token = $this->getToken();

        try{

            $client = new Client();

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

    public function setWebhookTransport($organizationId, $url){

        $token = $this->getToken();

        try{

            $client = new Client();

            $body = ['organizationId' => $organizationId, 'webHooksUri' => $url, 'authToken' => env('IIKO_API_LOGIN', '75782002')];

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

        } catch (\GuzzleHttp\Exception\ClientException $e) {

            $response = $e->getResponse();

            $statusCode = $response->getStatusCode();

              $error = json_decode($response->getBody(), true);

            $this->TelegramHandler($response->getBody());

            return ['statusCode' => $statusCode, 'response' => $error];
        }

    }

}
