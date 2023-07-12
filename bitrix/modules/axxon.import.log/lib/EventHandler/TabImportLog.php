<?php

namespace Axxon\Import\Log\EventHandler;

use Bitrix\Main\Config\Option;

class TabImportLog
{
    const MODULE_ID = 'axxon.import.log';

    /**
     * @return array
     */
    public static function eventHandler(): array
    {

        return [
            'TABSET'  => 'import_log',
            'Check'   => [__CLASS__, 'checkFields'],
            'Action'  => [__CLASS__, 'saveData'],
            'GetTabs' => [__CLASS__, 'getTabs'],
            'ShowTab' => [__CLASS__, 'showTab'],
        ];
    }


    /**
     * @param $element
     * @return array[]|null
     * @throws \Exception
     */
    public static function getTabs($element): ?array
    {
        $showTab = (int)$element['IBLOCK']['ID'] == (int)Option::get(self::MODULE_ID, 'block_id');
        return $showTab ? [
            [
                'DIV'   => 'axxon_import_log',
                'SORT'  => 6,
                'TAB'   => 'Логирование импорта',
                'TITLE' => 'Логирование импорта',
            ],
        ] : null;
    }

    /**
     * Показывает таблицу логов
     * @param $div
     * @param $element
     */
    public static function showTab($div, $element)
    {
        global $APPLICATION;
        echo '<tr><td>';
        $APPLICATION->IncludeComponent(sprintf('%s:admin.log.list', self::MODULE_ID), '',
            [
                'ELEMENT_ID' => $element['ID'],
            ]);
        echo '<tr><td>';
    }

    /**
     * @return bool
     */
    public static function checkFields(): bool
    {
        return true;
    }

    /**
     * @return bool
     */
    public static function saveData(): bool
    {
        return true;
    }
}