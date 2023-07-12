<?php


namespace Axxon\Response;


class WarehouseResponse extends ApiResponse
{
    /** @var array $warehouses */
    protected $warehouses;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->warehouses = $data['warehouses'] ?? null;
    }

    /**
     * @return array
     */
    public function getWarehouses(): ?array
    {
        return $this->warehouses;
    }
}