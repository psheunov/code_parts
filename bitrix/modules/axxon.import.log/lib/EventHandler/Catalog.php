<?php

namespace Axxon\Import\Log\EventHandler;

use Axxon\Import\Log\ImportLogger;
use Bitrix\Catalog\Model\Event;
use Bitrix\Catalog\Model\Price;
use Bitrix\Catalog\ProductTable;
use Exception;

/**
 * Class Catalog
 *
 * @package Axxon\Import\Log\EventHandler
 */
class Catalog
{
    /**
     * @param Event $event
     * @return void
     */
    public static function onBeforeProductUpdate(Event $event): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $fields = [
            'QUANTITY',
            'WEIGHT',
            'WIDTH',
            'LENGTH',
            'HEIGHT',
            'QUANTITY_TRACE',
            'CAN_BUY_ZERO',
        ];

        try {
            $oldFields  = ProductTable::getList([
                'select' => $fields,
                'filter' => [
                    'ID' => $event->getParameter('id')
                ]
            ])->fetch();

            $newFields  = $event->getParameter('fields');
            $productId  = (int)$event->getParameter('id');
            $diffFields = [];

            foreach ($fields as $code) {
                if ($newFields[$code] != $oldFields[$code]) {
                    $diffFields[] = [
                        'timestamp'  => $timestamp,
                        'product_id' => $productId,
                        'field'      => $code,
                        'old_value'  => $oldFields[$code],
                        'new_value'  => $newFields[$code]
                    ];
                }
            }

            if (!empty($diffFields)) {
                (new ImportLogger($productId))->log($diffFields);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param Event $event
     * @return void
     */
    public static function onBeforePriceUpdate(Event $event): void
    {
        $timestamp = date('Y-m-d H:i:s');

        try {
            $currentData = Price::getList([
                'select' => ['PRODUCT_ID', 'PRICE'],
                'filter' => ['ID' => $event->getParameter('id')]
            ])->fetch();

            $newData = $event->getParameter('fields');

            if ((double)$currentData['PRICE'] != (double)$newData['PRICE']) {
                (new ImportLogger((int)$currentData['PRODUCT_ID']))->log([
                    [
                        'timestamp'  => $timestamp,
                        'product_id' => (int)$currentData['PRODUCT_ID'],
                        'field'      => 'PRICE',
                        'old_value'  => (double)$currentData['PRICE'],
                        'new_value'  => (double)$newData['PRICE']
                    ]
                ]);
            }
        } catch (Exception $e) {
        }
    }
}