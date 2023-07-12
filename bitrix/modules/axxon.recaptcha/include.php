<?php

namespace Axxon\Recaptcha;

use Axxon\Recaptcha\Lib\Captcha;
use Axxon\Recaptcha\Lib\EventHandler\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use CJSCore;

CJSCore::Init([
    'jquery'
]);

try {
    Loader::registerAutoLoadClasses('axxon.recaptcha', [
        Captcha::class => 'lib/captcha.php',
        Main::class    => 'lib/eventhandler/main.php',
    ]);
} catch (LoaderException $e) {
}