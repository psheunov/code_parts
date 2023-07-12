<?php

namespace Axxon\Recaptcha\Lib;

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Web\HttpClient;
use Exception;

/**
 * Class Captcha
 * @package Axxon\Recaptcha\Lib
 */
class Captcha
{
    /** @var string URL */
    const URL = 'https://www.google.com/recaptcha/api/siteverify';

    private static string $privateKey     = '';
    public static string  $publicKey      = '';
    public static string  $captchaVersion = '';

    /**
     * Верификация ключа
     * @param $token
     * @return bool
     */
    public static function verify($token): bool
    {
        $httpClient = new HttpClient();
        $response   = $httpClient->post(self::URL, [
            'secret'   => self::getPrivateKey(),
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_IP']
        ]);

        $result = json_decode($response, true);

        if (json_last_error() == JSON_ERROR_NONE) {
            return $result['success'];
        }
        return false;
    }

    /**
     * Метод возвращает приватный ключ для гугл капчи из настроек модуля для текущего сайта
     * @return string
     */
    public static function getPrivateKey(): string
    {
        if (!self::$privateKey) {
            try {
                $siteId           = self::getSiteId();
                self::$privateKey = Option::get('axxon.recaptcha', $siteId . '_private');

            } catch (Exception $e) {
            }
        }
        return self::$privateKey;
    }

    /**
     * Метод возвращает публичный ключ для гугл капчи из настроек модуля для текущего сайта
     * @return string
     */
    public static function getPublicKey(): string
    {
        if (!self::$publicKey) {
            try {
                $siteId          = self::getSiteId();
                self::$publicKey = Option::get('axxon.recaptcha', $siteId . '_public');
            } catch (Exception $e) {
            }
        }
        return self::$publicKey;
    }

    /**
     * Метод возвращает версию рекапчи установленого для сайта
     * @return string
     */
    public static function getCaptchaVersion(): string
    {
        if (!self::$captchaVersion) {
            try {
                $siteId               = self::getSiteId();
                self::$captchaVersion = Option::get('axxon.recaptcha', $siteId . '_type', 'v2');
            } catch (Exception $e) {
            }
        }

        return self::$captchaVersion;
    }

    /**
     * Функция хранит конфигурации разных версии гугл рекпчи, и возвращает нужный параметр если он есть
     * @param $name
     * @return null|mixed
     */
    private static function getOption($name)
    {
        $options = [
            'v2' => [
                'query'    => [
                    'onload' => 'onloadCallback',
                    'render' => 'explicit'
                ],
                'template' => '<div id="g-recaptcha-%s" data-captcha=true></div>'
            ],
            'v3' => [
                'query'    => [
                    'render' => self::getPublicKey()
                ],
                'template' => '<input type="hidden" id="g-recaptcha-%s" data-captcha=true name="g-recaptcha-response" class="g-recaptcha-response"/>'
            ]
        ];

        if (array_key_exists($name, $options[self::getCaptchaVersion()])) {
            return $options[self::getCaptchaVersion()][$name];
        }
        return null;
    }

    /**
     * метод возвращает список параметров для запроса капчи
     * @return string
     */
    public static function getQuery(): string
    {
        return http_build_query(self::getOption('query'));
    }

    /**
     * Функция возвращает тег обертку для гугл рекапчи нужной версии
     * @param $params
     * @return string
     */
    public static function getTemplate($params): string
    {
        if ($format = self::getOption('template')) {
            return vsprintf($format, $params);
        }
        return '';
    }

    /**
     * @return string
     */
    public static function getSiteId(): string
    {
        $result = '';

        try {
            $serverData = explode('.', Application::getInstance()->getContext()->getServer()->getServerName());

            if (count($serverData) < 2) {
                throw new Exception('Server name is not set');
            }

            $domain = array_reverse($serverData)[1];
            $site   = SiteTable::getList([
                'select' => ['LID'],
                'filter' => ['%SERVER_NAME' => $domain],
                'limit'  => 1
            ])->fetch();

            if ($site) {
                $result = $site['LID'];
            }
        } catch (Exception $e) {
        }

        return $result;
    }
}