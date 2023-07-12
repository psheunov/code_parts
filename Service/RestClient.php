<?php


namespace App\Service;


use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Http;

class RestClient
{
    const DATA_FORMAT = 'json';

    /**
     * @var array $settings
     */
    private $settings = [];
    private $webHookUrl;

    /**
     * RestClient constructor.
     *
     * @param string $webHookUrl
     * @param array $settings
     * @throws Exception
     */
    public function __construct(string $webHookUrl, array $settings)
    {
        if (!(
            array_key_exists('userId', $settings)
            && array_key_exists('endpoint', $settings)
            && $settings['userId']
            && $settings['endpoint']
        )) {
            throw new Exception('Please set UserId and Endpoint');
        }
        $this->settings   = $settings;
        $this->webHookUrl = $webHookUrl;
    }

    /**
     * @param $method
     * @param $params
     * @return array
     */
    public function tap($method, $params): array
    {
        $jsonResponse = [];
        try {
            $url      = $this->getUrl($method);
            $client   = new Client();
            $response = $client->post($url, [
                'form_params'     => $params,
                'timeout'         => 10,
                'connect_timeout' => 10
            ]);

            if ($response->getStatusCode() >= 200 && $response->getStatusCode() < 300) {
                $jsonResponse = $this->decodeResponse($response->getBody());
            }

            if (array_key_exists('error', $jsonResponse)) {
                throw new Exception($this->getError($jsonResponse['error']));
            }
        } catch (Exception|GuzzleException $e) {
            dump($e->getMessage());
        }

        return $jsonResponse;
    }

    /**
     * Вызов api метода
     *
     * @param $method
     * @param array $postParams
     * @param int $timeOut
     * @return array
     */
    public function call($method, array $postParams = [], int $timeOut = 0): array
    {
        try {
            $url        = $this->getUrl($method);
            $postFields = http_build_query($postParams);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

            if ($timeOut) {
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut);
                curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut);
            }

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception(curl_error($ch));
            }

            if (is_bool($response)) {
                throw new Exception('Error while get response');
            }

            $result = $this->decodeResponse($response);
            curl_close($ch);

            if (array_key_exists('error', $result)) {
                $this->log([$response]);
                throw new Exception($this->getError($result['error']));
            }
        } catch (Exception $e) {
            return [
                'error' => $e->getMessage()
            ];
        }

        return $result;
    }

    /**
     * Получение uri запорса по названию метода
     *
     * @param $method
     * @return string
     */
    private function getUrl($method): string
    {
        return sprintf("%s/%s/%s/%s.%s",
            $this->webHookUrl,
            $this->settings['userId'],
            $this->settings['endpoint'],
            $method,
            self::DATA_FORMAT
        );
    }

    /**
     * Получение ответа в виде массива
     *
     * @param string $data
     * @return array
     * @throws Exception
     */
    private function decodeResponse(string $data): array
    {
        $result = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(json_last_error_msg(), 500);
        }

        return $result;
    }

    /**
     * Перевод ошибок в человекочитаемые строки
     *
     * @param $errorCode
     * @return string
     */
    private function getError($errorCode): string
    {
        $errorInfo = [
            'EXPIRED_TOKEN'          => 'Expired token, cant get new auth? Check access oauth server.',
            'INVALID_TOKEN'          => 'Invalid token, need reinstall application',
            'INVALID_GRANT'          => 'Invalid grant, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
            'INVALID_CLIENT'         => 'Invalid client, check out define C_REST_CLIENT_SECRET or C_REST_CLIENT_ID',
            'QUERY_LIMIT_EXCEEDED'   => 'Too many requests, maximum 2 query by second',
            'ERROR_METHOD_NOT_FOUND' => 'Method not found! You can see the permissions of the application: CRest::call(\'scope\')',
            'NO_AUTH_FOUND'          => 'Some setup error b24, check in table "b_module_to_module" event "OnRestCheckAuth"',
            'INTERNAL_SERVER_ERROR'  => 'Server down, try later'
        ];

        if (array_key_exists(strtoupper($errorCode), $errorInfo)) {
            return $errorInfo[$errorCode];
        }

        return $errorCode;
    }

    /**
     * @param array $context
     * @return void
     */
    private function log(array $context)
    {
        file_put_contents('/home/laravel/homedir/rest_client.log', sprintf(
            "%s\n",
            json_encode($context)
        ), FILE_APPEND);
    }
}