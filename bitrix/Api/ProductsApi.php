<?php


namespace Axxon\Api;

use Axxon\ApiException;
use Axxon\BaseApi;
use Axxon\Response\ProductsCountResponse;
use Axxon\Response\ProductsResponse;
use GuzzleHttp\Psr7\Request;

/**
 * Class ProductsApi
 * @package Axxon\Api
 */
class ProductsApi extends BaseApi
{
    /** @var int LIMIT */
    const LIMIT = 100;
    /** @var int START */
    const START = 1;
    /** @var int ACTIVE */
    const ACTIVE = 1;
    /** @var int INACTIVE */
    const INACTIVE = 2;
    /** @var bool FULL */
    const FULL = true;
    /** @var bool BRIEF */
    const BRIEF = false;

    /**
     * @param int $start
     * @param int $limit
     * @return ProductsResponse
     */
    public function all(int $start, int $limit): ProductsResponse
    {
        $path = '/products';

        $request = new Request(
            'GET',
            $this->buildUri($path),
            $this->getHeaders()
        );

        try {
            $response = $this->send($request, [
                'query' => [
                    'start' => $start,
                    'limit' => $limit
                ]
            ]);
        } catch (ApiException $e) {
            $response = [
                'error_code' => $e->getCode(),
                'message'    => $e->getMessage(),
                'success'    => false
            ];
        }

        return new ProductsResponse($response);
    }

    /**
     * @return ProductsCountResponse
     */
    public function updatedCount(): ProductsCountResponse
    {
        $path = '/products/count';

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

        return new ProductsCountResponse($response);
    }

    /**
     * @param int $timestamp
     * @return ProductsCountResponse
     */
    public function updatedSinceCount(int $timestamp): ProductsCountResponse
    {
        $path = '/products/updated-since/{timestamp}/count';
        $path = str_replace('{timestamp}', $timestamp, $path);

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

        return new ProductsCountResponse($response);
    }

    /**
     * @param int $timestamp
     * @param int $start
     * @param int $limit
     * @return ProductsResponse
     */
    public function updatedSince(int $timestamp, int $start, int $limit): ProductsResponse
    {
        $path = '/products/updated-since/{timestamp}';
        $path = str_replace('{timestamp}', $timestamp, $path);

        $request = new Request(
            'GET',
            $this->buildUri($path),
            $this->getHeaders()
        );

        try {
            $response = $this->send($request, [
                'query' => [
                    'start' => $start,
                    'limit' => $limit
                ]
            ]);
        } catch (ApiException $e) {
            $response = [
                'error_code' => $e->getCode(),
                'message'    => $e->getMessage(),
                'success'    => false
            ];
        }

        return new ProductsResponse($response);
    }

    /**
     * @param int $timestamp
     * @param int $start
     * @param int $limit
     * @return ProductsResponse
     */
    public function unavailableSince(int $timestamp, int $start, int $limit): ProductsResponse
    {
        $path = '/products/unavailable-since/{timestamp}';
        $path = str_replace('{timestamp}', $timestamp, $path);

        $request = new Request(
            'GET',
            $this->buildUri($path),
            $this->getHeaders()
        );

        try {
            $response = $this->send($request, [
                'query' => [
                    'start' => $start,
                    'limit' => $limit
                ]
            ]);
        } catch (ApiException $e) {
            $response = [
                'error_code' => $e->getCode(),
                'message'    => $e->getMessage(),
                'success'    => false
            ];
        }

        return new ProductsResponse($response);
    }

    /**
     * @param int $id
     * @return ProductsResponse
     */
    public function single(int $id): ProductsResponse
    {
        $path = '/products/{id}';
        $path = str_replace('{id}', $id, $path);

        $request = new Request(
            'GET',
            $this->buildUri($path),
            $this->getHeaders()
        );

        try {
            $response = $this->send($request, []);
        } catch (ApiException $e) {
            $response = [
                'error_code' => $e->getCode(),
                'message'    => $e->getMessage(),
                'success'    => false
            ];
        }

        return new ProductsResponse($response);
    }
}