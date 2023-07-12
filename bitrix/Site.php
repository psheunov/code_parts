<?php

namespace Axxon;

use Axxon\EventHandler\Main;
use Axxon\EventHandler\Sale;
use Axxon\Properties\IntegerField\IntegerField;
use Axxon\Middleware\SkipFrameCheck;
use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use CLang;
use Exception;
use Symfony\Component\Dotenv\Dotenv;

/**
 * Class Site
 *
 * @package Axxon
 */
class Site
{
    private static ?Site $instance = null;

    private bool $initialized = false;

    private array $middleware =
        [
            SkipFrameCheck::class
        ];

    /**
     * Создает экземпляр класса при первом обращении
     *
     * @return Site
     */
    public static function getInstance(): Site
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }


    /**
     * @return mixed
     * @throws Exception
     */
    public function __clone()
    {
        throw new Exception('This class connot cloned');
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function __wakeup()
    {
        throw new Exception('This class connot be serialized');
    }

    /**
     * Site constructor.
     */
    private function __construct()
    {
    }

    /**
     * @return void
     */
    private function bindEventHandlers(): void
    {
        $eventManager = EventManager::getInstance();

        $eventManager->addEventHandlerCompatible('main', 'OnProlog', [Main::class, 'onProlog']);
        $eventManager->addEventHandlerCompatible('sale', 'onSalePaySystemRestrictionsClassNamesBuildList', [Sale::class, 'onSalePaySystemRestrictionsClassNamesBuildList']);
        $eventManager->addEventHandlerCompatible('iblock', 'OnIBlockPropertyBuildList', [IntegerField::class, 'getUserTypeDescription']);
    }

    /**
     * @return void
     */
    public function compileIBlockConstants(): void
    {
        try {
            Loader::includeModule('iblock');

            $rows = IblockTable::getList([
                'select' => [
                    'ID',
                    'CODE',
                    'NAME',
                    'IBLOCK_TYPE_ID'
                ],
                'filter' => [
                    '!CODE' => false
                ],
                'order'  => [
                    'IBLOCK_TYPE_ID' => 'ASC',
                    'CODE'           => 'ASC'
                ]
            ]);

            $handle = fopen(Application::getDocumentRoot() . '/constants/iblock.php', 'w');

            fprintf($handle, "<?php\n\n");

            while ($row = $rows->fetch()) {
                $row['CODE'] = preg_replace('/[^a-z_-]/iu', '', $row['CODE']);
                $code = 'IB_'
                    . strtoupper($row['IBLOCK_TYPE_ID']) . '_'
                    . strtoupper(preg_replace('/-/iu', '_', $row['CODE']));

                fprintf($handle, "define('%s', %d); // %s\n", $code, (int)$row['ID'], $row['NAME']);
            }

            fclose($handle);
        } catch (Exception $e) {
        }
    }

    /**
     * @return bool
     */
    public function isDev(): bool
    {
        return getenv('APP_ENV') === 'dev';
    }

    /**
     * @return bool
     */
    public function isTest(): bool
    {
        return getenv('APP_ENV') === 'test';
    }


    /**
     * @return bool
     */
    public function isProd(): bool
    {
        return getenv('APP_ENV') === 'prod';
    }

    /**
     * @return bool
     */
    public function isLocal(): bool
    {
        return !$this->isDev() && !$this->isProd() && !$this->isTest();
    }

    /**
     * @return bool
     */
    public function isCliMode(): bool
    {
        return defined('STDIN') || php_sapi_name() === 'cli';
    }

    /**
     * Устанавливает настроки приложения
     *
     * @return void
     */
    private function updateSettings(): void
    {
        try {
            if (
                !Site::getInstance()->isProd()
                && Option::get('main', 'update_devsrv') === 'N'
            ) {
                Option::set('main', 'update_devsrv', 'Y');
            }

            if (!Site::getInstance()->isProd()) {
                (new CLang())->Update('s1', ['DOMAINS' => '']);
            }

        } catch (Exception $e) {
            return;
        }

    }

    /**
     * Подключаем env файл
     * @return void
     */
    private function loadEnv(): void
    {
        $envFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/.env';

        if (!file_exists($envFile)) {
            $envFile = dirname($_SERVER['DOCUMENT_ROOT']) . '/homedir/.env';
        }

        if (file_exists($envFile)) {
            $dotenv = new Dotenv();
            $dotenv->usePutenv();
            $dotenv->load($envFile);
        }
    }

    /**
     * run Middleware
     * @return void
     */
    private function runMiddleware(): void
    {
        foreach ($this->middleware as $middleware) {
            $middleware::handle();
        }
    }

    /**
     * Тут можно вызвать методы, которые проинициализируют наше приложение
     * @return void
     */
    public function init(): void
    {
        if (!$this->initialized) {
            $this->loadEnv();
            $this->updateSettings();
            $this->runMiddleware();
            $this->bindEventHandlers();
            $this->initialized = true;
        }
    }
}
