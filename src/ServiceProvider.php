<?php
/**
 * @author Sergey Borguronov <s@htmlup.ru>
 * 10.08.21
 */

namespace RussianProtein\iikoTransport;

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Class ServiceProvider
 * @package RussianProtein\iikoTransport
 */
class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config.php' => config_path('iikoTransport.php')
        ], 'config');
    }

    public function register()
    {
                //Логирование всех запросов
                $this->app->bind('GuzzleClient', function () {
                    /**
                     * Если необходимо увидеть полный ответ от запроса, то достаточно заменить:
                     * 'RESPONSE: {code}',
                     *  на
                     * 'RESPONSE: {code} - {res_body}',
                     *
                     *  If you need to see the full response from the request, then it is enough to replace:
                     *  'RESPONSE: {code}',
                     *  on
                     *  'RESPONSE: {code} - {res_body}',
                     */

                    $messageFormats = [
                        'REQUEST: {method} - {uri} - HTTP/{version} - Headers: {req_headers} - Body: {req_body}',
                        'RESPONSE: {code}',
                    ];

                    $stack = HandlerStack::create();


                    collect($messageFormats)->each(function ($messageFormat) use ($stack) {
                        $stack->unshift(
                            Middleware::log(
                                with(new Logger('guzzle-log'))->pushHandler(
                                    new RotatingFileHandler(storage_path('logs/iiko-log.log'))
                                ),
                                new MessageFormatter($messageFormat)
                            )
                        );
                    });

                    return function ($config) use ($stack){
                        return new Client(array_merge($config, ['handler' => $stack]));
                    };
                });
    }
}
