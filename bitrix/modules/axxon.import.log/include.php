<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Axxon\Import\Log\EventHandler\Catalog;
use Axxon\Import\Log\EventHandler\TabImportLog;
use Axxon\Import\Log\EventHandler\Iblock;
use Axxon\Import\Log\ImportLogger;
use Bitrix\Main\Loader;

try {
    Loader::registerAutoLoadClasses('axxon.import.log', [
        TabImportLog::class => 'lib/EventHandler/TabImportLog.php',
        Iblock::class       => 'lib/EventHandler/Iblock.php',
        Catalog::class      => 'lib/EventHandler/Catalog.php',
        ImportLogger::class => 'lib/ImportLogger.php',
    ]);
} catch (Exception $e) {
}