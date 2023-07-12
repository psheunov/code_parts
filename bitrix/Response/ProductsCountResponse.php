<?php


namespace Axxon\Response;


class ProductsCountResponse extends ApiResponse
{
    /** @var array $count */
    protected $count;

    /**
     * CatalogResponse constructor.
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->count = $data['count'] ?? 0;
    }

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}