<?php

namespace Axxon\Import\Log;

use Bitrix\Main\Config\Option;
use Exception;
use MongoDB\Client;

class ImportLogger
{
    const MODULE_ID     = 'axxon.import.log';
    const DEFAULT_LIMIT = 50;

    public  $pages;
    private $collection;
    private $productId;
    private $connection = true;
    private $limit;

    /**
     * @param int $productId
     */
    public function __construct(int $productId)
    {
        $this->productId = $productId;
        try {
            $client           = new Client(Option::get(self::MODULE_ID, 'uri'), []);
            $this->collection = $client
                ->selectCollection
                (
                    Option::get(self::MODULE_ID, 'database'),
                    Option::get(self::MODULE_ID, 'collection')
                );
            $this->limit      = ((Option::get(self::MODULE_ID, 'offset') != '') ? (int)Option::get(self::MODULE_ID, 'offset') : self::DEFAULT_LIMIT);
            $count            = $this->collection->countDocuments(['product_id' => $this->productId]);
            $this->pages      = ceil($count / $this->limit);

        } catch (Exception $e) {
            $this->connection = false;
        }

    }

    /**
     * @param array $data
     * @return void
     */
    public function log(array $data): void
    {
        if ($this->connection) {
            $this->collection->insertMany($data);
        }
    }

    /**
     * @param int $page
     * @return array
     */
    public function getProductLog(int $page = 0): array
    {
        $result = [];
        if ($this->connection) {
            $result = $this->collection->find(
                [
                    'product_id' => $this->productId
                ],
                [
                    'skip'  => $this->limit * $page,
                    'limit' => $this->limit,
                    'sort'  => ['timestamp' => -1]
                ]
            )->toArray();
        }
        return $result;
    }
}