<?php

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\Application;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use MongoDB\Client;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . '/modules/main/options.php');
Loc::loadMessages(__FILE__);

$moduleId = 'axxon.import.log';
global $APPLICATION;

Loader::includeModule($moduleId);
Loader::includeModule('iblock');
$request = HttpApplication::getInstance()->getContext()->getRequest();

$blocks = [];
$rows   = IblockTable::getList(['select' => ['NAME', 'ID']]);

while ($row = $rows->fetch()) {
    $blocks[$row['ID']] = $row['NAME'];
}

$checkConnection = '<input id="checkButton" type="button" name="" value="'
    . Loc::getMessage('AXXON_IMPORT_CHECK_CONNECTION')
    . '" onclick="checkConnection(\''
    . sprintf("%s?lang=%s&mid=%s&%s;", $APPLICATION->GetCurPage(), LANGUAGE_ID, urlencode($_REQUEST["mid"]), bitrix_sessid_get())
    . '\')">';

$tabs = [
    [
        'DIV'     => 'edit',
        'TAB'     => Loc::getMessage('AXXON_IMPORT_SETTINGS'),
        'TITLE'   => Loc::getMessage('AXXON_IMPORT_SETTINGS_TITLE'),
        'OPTIONS' => [
            ['uri', Loc::getMessage('AXXON_IMPORT_OPTION_URI'), '', ['text', 50]],
            ['database', Loc::getMessage('AXXON_IMPORT_OPTION_DATABASE'), '', ['text', 50]],
            ['collection', Loc::getMessage('AXXON_IMPORT_OPTION_TABLE'), '', ['text', 50]],
            ['offset', Loc::getMessage('AXXON_IMPORT_OPTION_OFFSET'), '', ['text', 10]],
            ['', '', $checkConnection, ['statichtml']]
        ]

    ],
    [
        'DIV'     => 'edit_iblock',
        'TAB'     => Loc::getMessage('AXXON_IMPORT_OPTION_IBLOCK_TAB'),
        'TITLE'   => Loc::getMessage('AXXON_IMPORT_OPTION_IBLOCK_TAB'),
        'OPTIONS' => [
            [
                'block_id',
                Loc::getMessage('AXXON_IMPORT_OPTION_IBLOCK_SELECT'),
                '',
                [
                    'selectbox',
                    $blocks
                ]
            ]
        ]

    ]
];

if ($request->isPost() && $request->isAjaxRequest()) {

    try {
        $uri        = $request->getPost('uri');
        $database   = $request->getPost('database');
        $collection = $request->getPost('collection');

        $response = null;
        $client   = new Client($uri, []);
        foreach ($client->listDatabaseNames() as $name) {
            if ($name == $database) {
                $response = new Json([
                    'ok'      => true,
                    'message' => 'Success'
                ]);
                break;
            }
        }

        if (!$response) {
            throw new Exception('Database not found');
        }
    } catch (Exception $e) {
        $response = new Json([
            'ok'      => false,
            'message' => $e->getMessage()
        ]);
    }

    Application::getInstance()->end(0, $response);
}

if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {
    foreach ($tabs as $tab) {
        foreach ($tab['OPTIONS'] as $option) {
            if (!is_array($option) || $option['note'] || !isset($option[0]) || !$option[0]) {
                continue;
            }

            $optionName  = $option[0];
            $optionValue = $request->getPost($optionName);

            if ($optionValue !== null) {
                try {
                    Option::set($moduleId, $optionName, is_array($optionValue) ? implode(',', $optionValue) : $optionValue);
                } catch (ArgumentOutOfRangeException $e) {
                }
            }
        }
    }
}

$tabControl = new CAdminTabControl('tabControl', $tabs);
?>
<?php $tabControl->Begin(); ?>
    <script>
        function checkConnection(url) {
            var data = {};

            $('#mongoOptions')
                .find('input')
                .each(function (index, item) {
                    var $item = $(item);
                    if (['uri', 'database', 'collection'].includes($item.attr('name'))) {
                        data[$item.attr('name')] = $item.val();
                    }
                });

            BX.ajax({
                    url            : url,
                    method         : 'POST',
                    data           : data,
                    dataType       : 'json',
                    timeout        : 30,
                    async          : true,
                    processData    : true,
                    scriptsRunFirst: true,
                    emulateOnload  : true,
                    start          : true,
                    cache          : false,
                    onsuccess      : function (data) {
                        if (data.ok) {
                            $('#checkButton')
                                .css('color', 'green')
                                .val('<?php echo Loc::getMessage('AXXON_IMPORT_ACTIVE'); ?>');
                        } else {
                            $('#checkButton')
                                .css('color', 'red')
                                .val('<?php echo Loc::getMessage('AXXON_IMPORT_ERROR'); ?>');
                        }

                        setTimeout(function () {
                            $('#checkButton')
                                .css('color', '#333')
                                .val('<?php echo Loc::getMessage('AXXON_IMPORT_CHECK_CONNECTION'); ?>')
                        }, 3000);
                    },
                    onfailure      : function (data) {
                        console.log('!!! Error !!! call to developers');
                    }
                }
            );
        }
    </script>
    <form method='post'
          id='mongoOptions'
          action="<?php echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($request['mid']) ?>&lang=<? echo $request['lang']; ?>"
          name='axxon_import_log_settings'>
        <?php
        foreach ($tabs as $tab) {
            if ($tab['OPTIONS']) {
                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($moduleId, $tab['OPTIONS']);
            }
        }

        $tabControl->BeginNextTab();
        $tabControl->Buttons();
        ?>
        <input type="submit" class="adm-btn-save" name="Update" value="Сохранить">
        <input type="reset" name="reset" value="Сбросить">
        <?php echo bitrix_sessid_post(); ?>
    </form>
<?php $tabControl->End(); ?>