<?php


namespace Axxon\Api;


use Axxon\ApiException;
use Axxon\BaseApi;
use Axxon\Response\PropertyResponse;
use Axxon\Response\PropertiesResponse;
use CEventLog;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class PropertiesApi extends BaseApi
{
    /**
     * @return PropertiesResponse
     */
    public function all() :PropertiesResponse
    {
        $path = '/properties';

        $request = new Request(
            'GET',
            $this->buildUri($path),
            $this->getHeaders()
        );

        try {
            $response = $this->send($request);
        } catch (ApiException $e) {
            $response = [
                'error_code' => $e->getCode(),
                'message'    => $e->getMessage(),
                'success'    => false
            ];
        }

        return new PropertiesResponse($response);
    }

    /**
     * @param int $attributeId
     * @return PropertyResponse
     */
    public function getAttributeById(int $attributeId): PropertyResponse
    {
        $path = '/goods-attributes/{attributeId}';
        $path = str_replace('{attributeId}', $attributeId, $path);

        $request = new Request(
            'GET',
            $this->buildUri($path),
            $this->getHeaders()
        );

        try {
            $response = $this->send($request);
        } catch (ApiException $e) {
            $response = [
                'error_code' => $e->getCode(),
                'message'    => $e->getMessage(),
                'success'    => false
            ];
        }

        return new PropertyResponse($response);
    }
}