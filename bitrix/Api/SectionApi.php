<?php


namespace Axxon\Api;

use Axxon\ApiException;
use Axxon\BaseApi;
use Axxon\Response\SectionResponse;
use GuzzleHttp\Psr7\Request;

/**
 * Class CatalogApi
 * @package Axxon\Api
 */
class SectionApi extends BaseApi
{
    /**
     * @return SectionResponse
     */
    public function all(): SectionResponse
    {
        $path = '/sections';

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

        return new SectionResponse($response);
    }
}