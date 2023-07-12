<?php

namespace Axxon\Recaptcha\Lib\EventHandler;

use Axxon\Recaptcha\Lib\Captcha;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\Page\AssetLocation;
use Bitrix\Main\Request;
use Bitrix\Main\SystemException;

class Main
{
    /** @var string[] EXCLUDE_IP Список исключаемых ip адресов */
    const EXCLUDE_IP = [
        '194.28.73.98'
    ];

    /** @var string CLI_MODE */
    const CLI_MODE = 'cli';

    /**
     * @return bool
     */
    private static function isDevEnv(): bool
    {
        return (Option::get('main', 'update_devsrv') === 'Y')
            && in_array(Context::getCurrent()->getRequest()->getRemoteAddress(), self::EXCLUDE_IP);
    }

    /**
     * Событие работает до подключения prolog
     * Здесь будем проверять запрос, и если запрос POST и содержит в себе sid  капчи,
     * то будем проверять капчу
     */
    public static function OnPageStart(): void
    {
        try {
            $request = Application::getInstance()->getContext()->getRequest();
            $conn    = Application::getConnection();

            if (self::verificationRequired($request)) {
                $captchaCode = 'DEFAULT';

                if (Captcha::verify($request->getPost('g-recaptcha-response')) || self::isDevEnv()) {
                    $query = sprintf(
                        "select code from b_captcha where id='%s'",
                        $conn->getSqlHelper()->forSql($request->getPost('captcha_sid'))
                    );

                    $rows = $conn->query($query)->fetch();

                    if ($rows && $rows['code'] && strlen($rows['code'])) {
                        $captchaCode = $rows['code'];
                    }
                }

                $_REQUEST['captcha_word'] = $captchaCode;
                $_POST['captcha_word']    = $captchaCode;
            }
        } catch (SystemException $e) {
        }
    }

    /**
     * Подключаем скрипты капчи
     */
    public static function OnBeforeProlog(): void
    {
        if (php_sapi_name() !== self::CLI_MODE && !Context::getCurrent()->getRequest()->isAdminSection()) {
            Asset::getInstance()->addString(
                sprintf("<script>window['reCaptchaKey'] = '%s';</script>", Captcha::getPublicKey()),
                true,
                AssetLocation::AFTER_CSS
            );
            Asset::getInstance()->addString(
                "<script>var recaptcha = {isChecked: function () {return " . (self::isDevEnv() ? 'true' : 'grecaptcha.getResponse().length') . ";}};</script>",
                true,
                AssetLocation::AFTER_CSS
            );
            Asset::getInstance()->addJs(
                sprintf('https://www.google.com/recaptcha/api.js?%s', Captcha::getQuery()),
                Captcha::getCaptchaVersion() == 'v2'
            );
            Asset::getInstance()->addJs(
                sprintf('/local/modules/axxon.recaptcha/assets/recaptcha_%s.js', Captcha::getCaptchaVersion()),
                Captcha::getCaptchaVersion() != 'v2'
            );
        }
    }

    /**
     * Выполняется перед выводом конетента. Меняем стандартную капчу на капчу от гугл
     *
     * @param $content
     */
    public static function OnEndBufferContent(&$content): void
    {
        if (self::isCaptchaRenderable()) {
            Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . '/local/modules/axxon.recaptcha/options.php');

            // стираем картинку
            $buffer = preg_replace(
                '/<img[^>]+src\s*=\s*[\'"]\/bitrix\/tools\/captcha\.php\?captcha_sid=[0-9a-z]+[\'"][^>]+>/m',
                '',
                $content
            );

            $buffer = preg_replace_callback('/<input[^>]+name\s*=\s*[\'"]captcha_word[\'"][^>]+>/m', function () {
                $id = uniqid();
                return Captcha::getTemplate([$id]);
            }, $buffer);

            // стираем лишний текст
            $buffer = preg_replace([
                '/' . GetMessage('REGISTER_CAPTCHA_PROMT') . '\s*<span[^>]+>\s*\*\s*<\/span>/m',
                '/' . GetMessage('REGISTER_CAPTCHA_TITLE') . '\s*<span[^>]+>\s*\*\s*<\/span>/m',
                '/' . GetMessage('system_auth_captcha') . '\s*<span[^>]+>\s*\*\s*<\/span>/m',
                '/<span[^>]+>\s*\*\s*<\/span>\s*' . GetMessage('system_auth_captcha') . '/m',
                '/<span[^>]+>\s*\*\s*<\/span>\s*' . GetMessage('REGISTER_CAPTCHA_PROMT') . '/m',
                '/<span[^>]+>\s*\*\s*<\/span>\s*' . GetMessage('REGISTER_CAPTCHA_TITLE') . '/m'
            ], '', $buffer);

            $buffer = str_replace(
                [
                    GetMessage('REGISTER_CAPTCHA_PROMT'),
                    GetMessage('REGISTER_CAPTCHA_TITLE'),
                    GetMessage('system_auth_captcha'),
                    GetMessage('AUTH_CAPTCHA_PROMT'),
                    GetMessage('AUTH_OTP_CAPTCHA_PROMT'),
                    GetMessage('CAPTCHA_REGF_PROMT'),
                    GetMessage('CPST_ENTER_WORD_PICTURE'),
                    GetMessage('CPST_SUBSCRIBE_CAPTCHA_TITLE'),
                    GetMessage('MAIN_AUTH_CHD_FIELD_CAPTCHA'),
                    GetMessage('MAIN_AUTH_FORM_FIELD_CAPTCHA'),
                    GetMessage('MAIN_AUTH_OTP_FIELD_CAPTCHA'),
                    GetMessage('MAIN_AUTH_PWD_FIELD_CAPTCHA'),
                    GetMessage('MFT_CAPTCHA_CODE'),
                    GetMessage('NOTIFY_POPUP_CAPTHA'),
                    GetMessage('REGISTER_CAPTCHA_PROMT'),
                    GetMessage('SPCR1_CAPTCHA_WRD'),
                    GetMessage('subscr_CAPTCHA_REGF_PROMT'),
                ],
                '',
                $buffer
            );

            $buffer = preg_replace(
                '/' . GetMessage('MAIN_FUNCTION_REGISTER_CAPTCHA') . '/m',
                GetMessage('AXXON_NOT_ROBOT'),
                $buffer
            );

            $content = $buffer;
        }
    }

    /**
     * @param Request $request
     * @return bool
     */
    private static function verificationRequired(Request $request): bool
    {
        return $request->isPost()
            && $request->getPost('captcha_sid')
            && ($request->getPost('g-recaptcha-response') || self::isDevEnv());
    }

    /**
     * @return bool
     */
    public static function isCaptchaRenderable(): bool
    {
        return php_sapi_name() !== self::CLI_MODE
            && !Context::getCurrent()->getRequest()->isAdminSection()
            && Captcha::getPrivateKey()
            && Captcha::getPublicKey();
    }
}