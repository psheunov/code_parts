<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\EventManager;

/** @noinspection ClassOverridesFieldOfSuperClassInspection */
class axxon_recaptcha extends CModule
{
    public $MODULE_ID = 'axxon.recaptcha';

    public $MODULE_VERSION = '0.0.1';
    public $MODULE_VERSION_DATE = '2020-01-28 00:00:00';

    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    public $PARTNER_NAME;
    public $PARTNER_URI;

    protected $moduleRoot;

    /** @var CMain $application */
    protected $application;

    public function __construct()
    {
        Loc::loadMessages(__FILE__);

        $this->moduleRoot = dirname(__DIR__);

        global $APPLICATION;
        $this->application = $APPLICATION;

        $this->MODULE_NAME        = Loc::getMessage('AXXON_RECAPTCHA_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('AXXON_RECAPTCHA_MODULE_DESC');
        $this->PARTNER_NAME       = Loc::getMessage('AXXON_RECAPTCHA_PARTNER_NAME');
        $this->PARTNER_URI        = Loc::getMessage('AXXON_RECAPTCHA_PARTNER_URI');
    }

    /**
     * @return bool
     */
    public function doInstall()
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
     */
    public function InstallFiles(array $params = [])
    {
    }

    public function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler('main', 'OnPageStart',        $this->MODULE_ID, 'Axxon\Recaptcha\Lib\EventHandler\Main', 'OnPageStart');
        EventManager::getInstance()->registerEventHandler('main', 'onBeforeProlog',     $this->MODULE_ID, 'Axxon\Recaptcha\Lib\EventHandler\Main', 'onBeforeProlog');
        EventManager::getInstance()->registerEventHandler('main', 'OnEndBufferContent', $this->MODULE_ID, 'Axxon\Recaptcha\Lib\EventHandler\Main', 'OnEndBufferContent');
    }

    /**
     * @return bool
     */
    public function doUninstall()
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
     */
    public function UnInstallFiles(array $params = [])
    {
    }

    public function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnPageStart',        $this->MODULE_ID, 'Axxon\Recaptcha\Lib\EventHandler\Main', 'OnPageStart');
        EventManager::getInstance()->unRegisterEventHandler('main', 'onBeforeProlog',     $this->MODULE_ID, 'Axxon\Recaptcha\Lib\EventHandler\Main', 'onBeforeProlog');
        EventManager::getInstance()->unRegisterEventHandler('main', 'OnEndBufferContent', $this->MODULE_ID, 'Axxon\Recaptcha\Lib\EventHandler\Main', 'OnEndBufferContent');
    }
}
