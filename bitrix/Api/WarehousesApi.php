<?php


namespace Axxon\Api;


use Axxon\ApiException;
use Axxon\BaseApi;
use Axxon\Response\WarehouseResponse;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class WarehousesApi extends BaseApi
{
    /**
     * @return WarehouseResponse
     */
    public function all(): WarehouseResponse
    {
        $path = '/warehouses';

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

        return new WarehouseResponse($response);
    }

    /**
     * @param int $warehouseId
     * @return array
     */
    public function allUnits(int $warehouseId): array
    {
        $path = '/warehouses/{warehouseId}/units';
        $path = str_replace('{warehouseId}', $warehouseId, $path);

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

        return $response;
    }
}