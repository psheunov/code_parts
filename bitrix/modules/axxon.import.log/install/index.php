<?php

use Axxon\Import\Log\EventHandler\TabImportLog;
use Axxon\Import\Log\EventHandler\Iblock;
use Axxon\Import\Log\EventHandler\Catalog;

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

/** @noinspection ClassOverridesFieldOfSuperClassInspection */

class axxon_import_log extends CModule
{
    public $MODULE_ID = 'axxon.import.log';

    public $MODULE_VERSION      = '0.0.1';
    public $MODULE_VERSION_DATE = '2021-12-01 00:00:00';

    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public $PARTNER_NAME;
    public $PARTNER_URI;

    protected string $moduleRoot;

    /** @var CMain $application */
    protected CMain $application;

    public function __construct()
    {
        Loc::loadMessages(__FILE__);

        $this->moduleRoot = dirname(__DIR__);

        global $APPLICATION;
        $this->application = $APPLICATION;

        $this->MODULE_NAME        = Loc::getMessage('AXXON_IMPORT_LOG_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('AXXON_IMPORT_LOG_DESC');
        $this->PARTNER_NAME       = Loc::getMessage('AXXON_LOG_PARTNER_NAME');
        $this->PARTNER_URI        = Loc::getMessage('AXXON_LOG_PARTNER_URI');
    }

    /**
     * @return bool
     */
    public function doInstall(): bool
    {
        $this->InstallFiles();
        $this->InstallEvents();
        $this->InstallTasks();

        if (!$this->application->GetException()) {
            ModuleManager::registerModule($this->MODULE_ID);
            return true;
        }

        return false;
    }

    /**
     * @param array $params
     * @return void
     */
    public function InstallFiles(array $params = []): void
    {
        CopyDirFiles(__DIR__ . '/components', $_SERVER['DOCUMENT_ROOT'] . '/local/components/', true, true);
    }

    /**
     * @return void
     */
    public function InstallEvents(): void
    {
        EventManager::getInstance()->registerEventHandler('main', 'OnAdminIBlockElementEdit', $this->MODULE_ID, TabImportLog::class, 'eventHandler');

        EventManager::getInstance()->registerEventHandler('iblock', 'OnIBlockElementUpdate', $this->MODULE_ID, Iblock::class, 'onElementUpdate');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, Iblock::class, 'onAfterElementAdd');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnIBlockElementSetPropertyValuesEx', $this->MODULE_ID, Iblock::class, 'onElementSetPropertyEx');
        EventManager::getInstance()->registerEventHandler('iblock', 'OnIBlockElementSetPropertyValues', $this->MODULE_ID, Iblock::class, 'onElementSetProperty');

        EventManager::getInstance()->registerEventHandler('catalog', 'Bitrix\Catalog\Model\Product::OnBeforeUpdate', $this->MODULE_ID, Catalog::class, 'onBeforeProductUpdate');
        EventManager::getInstance()->registerEventHandler('catalog', 'Bitrix\Catalog\Model\Price::OnBeforeUpdate', $this->MODULE_ID, Catalog::class, 'onBeforePriceUpdate');
    }

    /**
     * @return bool
     */
    public function doUninstall(): bool
    {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallTasks();

        if (!$this->application->GetException()) {
            ModuleManager::unRegisterModule($this->MODULE_ID);
            return true;
        }

        return false;
    }

    /**
     * @param array $params
     * @return void
     */
    public function UnInstallFiles(array $params = []): void
    {
        DeleteDirFilesEx('local/components/axxon.import.log');
    }

    /**
     * @return void
     */
    public function UnInstallEvents(): void
    {
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnAdminIBlockElementEdit', $this->MODULE_ID, TabImportLog::class, 'eventHandler');

        EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnIBlockElementUpdate', $this->MODULE_ID, Iblock::class, 'onElementUpdate');
        EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnAfterIBlockElementAdd', $this->MODULE_ID, Iblock::class, 'onAfterElementAdd');
        EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnIBlockElementSetPropertyValuesEx', $this->MODULE_ID, Iblock::class, 'onElementSetPropertyEx');
        EventManager::getInstance()->unRegisterEventHandler('iblock', 'OnIBlockElementSetPropertyValues', $this->MODULE_ID, Iblock::class, 'onElementSetProperty');

        EventManager::getInstance()->unRegisterEventHandler('catalog', 'Bitrix\Catalog\Model\Product::OnBeforeUpdate', $this->MODULE_ID, Catalog::class, 'onBeforeProductUpdate');
        EventManager::getInstance()->unRegisterEventHandler('catalog', 'Bitrix\Catalog\Model\Price::OnBeforeUpdate', $this->MODULE_ID, Catalog::class, 'onBeforePriceUpdate');
    }
}
