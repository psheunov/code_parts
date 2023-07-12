<?php


namespace Axxon\Response;


class ProductsResponse extends ApiResponse
{
    /** @var array $products */
    protected $products;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->products = $data['products'] ?? null;
    }

    /**
     * @return array
     */
    public function getProducts(): ?array
    {
        return $this->products;
    }
}