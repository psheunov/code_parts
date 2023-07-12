<?php
/**
 * Created by PhpStorm.
 * User: ATB | Artur Tetuev <atetuev@atbdev.ru>
 * Date: 08/10/2020
 * Time: 17:23
 */

namespace Axxon\Import\Log\EventHandler;


use Axxon\Import\Log\ImportLogger;
use Bitrix\Main\Config\Option;
use Exception;

/**
 * Class Iblock
 *
 * @package Axxon\Import\Log\EventHandler
 */
class Iblock
{
    const MODULE_ID = 'axxon.import.log';

    /**
     * @param array $freshData
     * @param array $originalData
     * @return void
     */
    public static function onElementUpdate($freshData, $originalData): void
    {
        try {
            if ((int)$freshData['IBLOCK_ID'] != (int)Option::get(self::MODULE_ID, 'block_id')) {
                return;
            }

            $timestamp = date('Y-m-d H:i:s');

            $exclude = [
                'IPROPERTY_TEMPLATES',
                'SEARCHABLE_CONTENT',
                'WF',
                'PROPERTY_VALUES',
                'IBLOCK_SECTION',
            ];

            $excludeValueLogging = [
                'DETAIL_TEXT',
                'PREVIEW_TEXT',
            ];

            $diffFields = [];
            foreach ($freshData as $key => $value) {
                if ($value != $originalData[$key] && !in_array($key, $exclude)) {
                    if (in_array($key, $excludeValueLogging)) {
                        $old = strlen((string)$originalData[$key]);
                        $new = strlen((string)$value);
                    } else {
                        $old = $originalData[$key];
                        $new = $value;
                    }

                    $diffFields[] = [
                        'timestamp'  => $timestamp,
                        'product_id' => (int)$freshData['ID'],
                        'field'      => $key,
                        'old_value'  => $old,
                        'new_value'  => $new
                    ];
                }
            }

            if (!empty($diffFields)) {
                (new ImportLogger((int)$freshData['ID']))->log($diffFields);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param array $id
     * @param array $blockId
     * @param array $freshData
     * @param array $properties
     * @param array $originalData
     */
    public static function onElementSetPropertyEx($id, $blockId, $freshData, $properties, $originalData): void
    {
        try {
            if ((int)$blockId != (int)Option::get(self::MODULE_ID, 'block_id')) {
                return;
            }

            $timestamp = date('Y-m-d H:i:s');

            $oldValues = [];
            foreach ($originalData as $key => $valueInfo) {
                $values   = array_values($valueInfo);
                $property = $properties[$key];

                if ($property['MULTIPLE'] != 'Y') {
                    $oldValues[$property['CODE']] = $values[0]['VALUE'];
                } else {
                    foreach ($values as $item) {
                        $oldValues[$property['CODE']][] = $item['VALUE'];
                    }
                }
            }

            $diffProps = [];
            foreach ($freshData as $key => $value) {
                if (is_array($value)) {
                    $changed         = (!empty(array_diff($value, $oldValues[$key])) || !empty(array_diff($oldValues[$key], $value)));
                    $value           = json_encode($value);
                    $oldValues[$key] = json_encode($oldValues[$key]);
                } else {
                    $changed = $value != $oldValues[$key];
                }

                if ($changed) {
                    $diffProps[] = [
                        'timestamp'  => $timestamp,
                        'product_id' => (int)$id,
                        'field'      => $key,
                        'old_value'  => $oldValues[$key],
                        'new_value'  => $value
                    ];
                }
            }

            if (!empty($diffProps)) {
                (new ImportLogger((int)$id))->log($diffProps);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param int $id
     * @param int $blockId
     * @param array $freshData
     * @param string $propertyCode
     * @param array $properties
     * @param array $originalData
     * @return void
     */
    public static function onElementSetProperty($id, $blockId, $freshData, $propertyCode, $properties, $originalData): void
    {
        try {
            if ((int)$blockId != (int)Option::get(self::MODULE_ID, 'block_id')) {
                return;
            }

            $timestamp = date('Y-m-d H:i:s');
            $diffProps = [];

            foreach ($freshData as $key => $fresh) {
                $property = $properties[$key];

                if ($property['MULTIPLE'] != 'Y') {
                    $old = current($originalData[$key] ?? [['VALUE' => null]]);
                    $new = current($fresh);


                    if ($old['VALUE'] != $new['VALUE']) {
                        $diffProps[] = [
                            'timestamp'  => $timestamp,
                            'product_id' => (int)$id,
                            'field'      => $property['NAME'],
                            'old_value'  => $old['VALUE'],
                            'new_value'  => $new['VALUE']
                        ];
                    }
                } else {
                    $oldValues = [];
                    $newValues = [];

                    foreach ($originalData[$key] as $item) {
                        if ($item['VALUE']) {
                            $oldValues[$property['ID']][] = $item['VALUE'];
                        }
                    }

                    foreach ($fresh as $item) {
                        if ($item['VALUE']) {
                            $newValues[$property['ID']][] = $item['VALUE'];
                        }
                    }

                    sort($oldValues);
                    sort($newValues);

                    if (md5(json_encode($oldValues)) != md5(json_encode($newValues))) {
                        $diffProps[] = [
                            'timestamp'  => $timestamp,
                            'product_id' => (int)$id,
                            'field'      => $property['NAME'],
                            'old_value'  => json_encode($oldValues),
                            'new_value'  => json_encode($newValues)
                        ];
                    }
                }
            }

            if (!empty($diffProps)) {
                (new ImportLogger((int)$id))->log($diffProps);
            }
        } catch (Exception $e) {
        }
    }

    /**
     * @param $fields
     * @return void
     */
    public static function onAfterElementAdd(&$fields): void
    {
        try {
            (new ImportLogger((int)$fields['ID']))->log([
                [
                    'timestamp'  => date('Y-m-d H:i:s'),
                    'product_id' => (int)$fields['ID'],
                    'field'      => 'add',
                    'old_value'  => '',
                    'new_value'  => 'add new element'
                ]
            ]);
        } catch (Exception $e) {
        }
    }
}