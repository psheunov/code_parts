<?php

namespace Axxon\Scripts;

use Axxon\Site;
use Bitrix\Main\Config\Option;
use CLang;
use Composer\Script\Event;
use Exception;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

class PostAutoload
{
    const SETTINGS_FILE_PATH = 'axxon/bitrix/.settings.php';
    const DB_CONN_FILE_PATH  = 'axxon/bitrix/php_interface/dbconn.php';
    const LICENSE_FILE_PATH  = 'axxon/bitrix/license_key.php';

    /** @var string $homeDir */
    private static string $homeDir;

    /** @var string $srcDir */
    private static string $srcDir;

    /** @var string $vendorDir */
    private static string $vendorDir;

    /** @var string $publicPath */
    private static string $publicPath;

    /**
     * @param Event $event
     * @return int
     */
    public static function run(Event $event): int
    {
        self::$vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
        self::$srcDir    = dirname(self::$vendorDir);
        self::$homeDir   = dirname(self::$vendorDir, 2);

        $output = new ConsoleOutput();

        try {
            self::copyBin();
            self::loadClasses();
            self::loadEnv();

            self::$publicPath = sprintf("%s/%s", self::$srcDir, getenv('PUBLIC_PATH'));

            if (Site::getInstance()->isLocal()) {
                $output->writeln("Local environment, there is nothing we can do");
                return 0;
            }

            if (!self::mkLink($output)) {
                throw new Exception('Error while make links');
            }

            self::updateSettings($output);

            if (self::initBitrixCore()) {
                self::setBitrixOptions();
                Site::getInstance()->compileIBlockConstants();
            } else {
                $output->writeln('Error initializing bitrix core');
            }
        } catch (Exception $e) {
            $output->writeln(sprintf("Exception occured: <error>%s</error>", $e->getMessage()));
            return 500;
        }

        return 0;
    }

    /**
     * @return void
     * @throws Exception
     */
    private static function loadClasses()
    {
        $fileName = self::$vendorDir . '/autoload.php';

        if (!file_exists($fileName)) {
            throw new Exception(sprintf("Autoload not found, check path %s", self::$vendorDir));
        }

        require_once $fileName;
    }

    /**
     * @return void
     */
    private static function copyBin()
    {
        if (!file_exists('bin/exchange')) {
            copy(self::$vendorDir . '/axxon/sale-exchange/bin/exchange', 'bin/exchange');
        }

        if (!file_exists('bin/bic')) {
            copy(self::$vendorDir . '/axxon/local/bin/bic', 'bin/bic');
        }
    }

    /**
     * @return void
     */
    private static function loadEnv()
    {
        $fileName = self::$homeDir . '/.env';

        $dotenv = new Dotenv();
        $dotenv->usePutenv();

        if (file_exists($fileName)) {
            $dotenv->load($fileName);
        } else {
            putenv('APP_ENV=local');
        }
    }

    /**
     * @param ConsoleOutput $output
     * @return bool
     */
    private static function mkLink(ConsoleOutput $output): bool
    {
        $publicPath = getenv('PUBLIC_PATH');

        $commands = [
            ['ln', '-sf', self::$vendorDir, self::$publicPath],

            ['ln', '-sf', self::$vendorDir . '/axxon/bitrix', self::$srcDir . '/'],
            ['ln', '-sf', self::$vendorDir . '/axxon/bitrix', self::$publicPath],

            ['ln', '-sf', self::$vendorDir . '/axxon/local', self::$publicPath],
            ['ln', '-sf', self::$vendorDir . '/axxon/local', self::$srcDir . '/'],

            ['ln', '-sf', self::$homeDir . '/static/upload', self::$publicPath],
            ['ln', '-sf', self::$homeDir . '/static/upload', self::$srcDir . '/'],

            ['ln', '-snf', self::$publicPath, self::$homeDir . '/' . getenv('APP_URL')],

            [
                'cp',
                sprintf("%s/%s.orig", self::$vendorDir, self::DB_CONN_FILE_PATH),
                sprintf("%s/%s", self::$vendorDir, self::DB_CONN_FILE_PATH),
            ],
            [
                'cp',
                sprintf("%s/%s.orig", self::$vendorDir, self::SETTINGS_FILE_PATH),
                sprintf("%s/%s", self::$vendorDir, self::SETTINGS_FILE_PATH),
            ],
            [
                'cp',
                sprintf("%s/%s.orig", self::$vendorDir, self::LICENSE_FILE_PATH),
                sprintf("%s/%s", self::$vendorDir, self::LICENSE_FILE_PATH),
            ]
        ];

        try {
            foreach ($commands as $command) {
                $process = new Process($command, self::$homeDir);
                $process->run();

                if (!$process->isSuccessful()) {
                    $output->writeln($process->getCommandLine() . "\t<error>Error</error>");
                    $output->writeln("Error output: " . $process->getErrorOutput());
                }

                $output->writeln($process->getCommandLine() . "\t<info>Success</info>");
            }

        } catch (Exception $e) {
            $output->writeln(
                sprintf("<error>%s</error>", $e->getMessage())
            );
            return false;
        }

        return true;
    }

    /**
     * @param ConsoleOutput $output
     * @return void
     */
    private static function updateSettings(ConsoleOutput $output)
    {
        $settings = sprintf("%s/%s", self::$vendorDir, self::SETTINGS_FILE_PATH);

        if (file_exists($settings)) {
            $d7settings = include_once($settings);

            $d7settings['connections']['value']['default']['host']     = getenv('DB_HOST');
            $d7settings['connections']['value']['default']['database'] = getenv('DB_DATABASE');
            $d7settings['connections']['value']['default']['login']    = getenv('DB_USERNAME');
            $d7settings['connections']['value']['default']['password'] = getenv('DB_PASSWORD');

            $d7settings['exception_handling']['value']['debug'] = !Site::getInstance()->isProd();

            file_put_contents($settings, sprintf("<?php\nreturn %s;\n", var_export($d7settings, true)));
        } else {
            $output->writeln(
                sprintf("<error>Settings file %s not found</error>", $settings)
            );
        }

        $dbConn = sprintf("%s/%s", self::$vendorDir, self::DB_CONN_FILE_PATH);

        if (file_exists($dbConn)) {
            $oldSettings = file_get_contents($dbConn);

            $oldSettings = preg_replace('/(?<=\$DBHost\s=\s)""/m', sprintf("\"%s\"", getenv('DB_HOST')), $oldSettings);
            $oldSettings = preg_replace('/(?<=\$DBName\s=\s)""/m', sprintf("\"%s\"", getenv('DB_DATABASE')), $oldSettings);
            $oldSettings = preg_replace('/(?<=\$DBLogin\s=\s)""/m', sprintf("\"%s\"", getenv('DB_USERNAME')), $oldSettings);
            $oldSettings = preg_replace('/(?<=\$DBPassword\s=\s)""/m', sprintf("\"%s\"", getenv('DB_PASSWORD')), $oldSettings);

            file_put_contents($dbConn, $oldSettings);
        } else {
            $output->writeln(
                sprintf("<error>DB conn file %s not found</error>", $dbConn)
            );
        }

        $license = sprintf("%s/%s", self::$vendorDir, self::LICENSE_FILE_PATH);

        if (file_exists($license)) {
            file_put_contents($license, sprintf("<?php\n\$LICENSE_KEY = '%s';", getenv('LICENSE')));
        } else {
            $output->writeln(
                sprintf("<error>License file %s not found</error>", $license)
            );
        }
    }

    /**
     * @return bool
     */
    private static function initBitrixCore(): bool
    {
        if (file_exists(self::$publicPath . '/bitrix/modules/main/include/prolog_before.php')) {
            define('NOT_CHECK_PERMISSIONS', true);
            $DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = self::$publicPath;

            require_once(self::$publicPath . '/bitrix/modules/main/include/prolog_before.php');
        }

        return defined('B_PROLOG_INCLUDED') && B_PROLOG_INCLUDED === true;
    }

    /**
     * @return void
     */
    private static function setBitrixOptions()
    {
        $options = [
            'update_devsrv', // версия для разработки
            'site_stopped'   // закрыть публичную часть
        ];

        try {
            $state = Site::getInstance()->isProd() ? 'N' : 'Y';

            foreach ($options as $option) {
                if (Option::get('main', $option) !== $state) {
                    Option::set('main', $option, $state);
                }
            }

            if (!Site::getInstance()->isProd()) {
                (new CLang())->Update('s1', ['DOMAINS' => '']);
            }

        } catch (Exception $e) {
        }
    }
}