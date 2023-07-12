<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\SiteTable;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/options.php');
Loc::loadMessages(__FILE__);


$module_id = 'axxon.recaptcha';;
Loader::includeModule($module_id);
$request = HttpApplication::getInstance()->getContext()->getRequest();

$sites = SiteTable::getList();
$options = [];
while ($site = $sites->Fetch()) {
    $options[] = [$site['LID'], '<h2>' . $site['NAME'].'</h2>'];
    $options[] = [$site['LID'] . '_public',Loc::getMessage('AXXON_RECAPTCHA_PUBLIC_KEY') . $site['NAME'], '', ['text', 50]];
    $options[] = [$site['LID'] . '_private',Loc::getMessage('AXXON_RECAPTCHA_PRIVATE_KEY') . $site['NAME'], '', ['text', 50]];
    $options[] = [
        $site['LID'].'_type',Loc::getMessage('AXXON_RECAPTCHA_TYPE').' '.$site['NAME'],
        '',
        ['selectbox', ['v2' => 'version 2', 'v3' => 'version 3' ]]
    ];

}
$tabs = [
    [
        'DIV'       => 'edit',
        'TAB'       => Loc::getMessage('AXXON_RECAPTCHA_SETTINGS'),
        'TITLE'     => Loc::getMessage('AXXON_RECAPTCHA_KEY_FOR_ACCESS'),
        'OPTIONS'   => $options
    ],
    [
        'DIV'       => 'access',
        'TAB'       => Loc::getMessage('AXXON_RECAPTCHA_ACCESS'),
        'TITLE'     => Loc::getMessage('AXXON_RECAPTCHA_ACCESS'),
    ]
];

if ($request->isPost() && $request->getPost('update') && check_bitrix_sessid()) {
    foreach ($tabs as $tab) {
        foreach ($tab['OPTIONS'] as $option) {
            if (!is_array($option)) {
                continue;
            }
            $optionName = $option[0];
            $optionValue = $request->getPost($optionName);
            Option::set($module_id, $optionName, $optionValue);
        }
    }
}

$tabControl = new CAdminTabControl('tabControl', $tabs);
$tabControl->Begin(); ?>
    <form method='post'  action="<?php echo $APPLICATION->GetCurPage() ?>?mid=<?=htmlspecialcharsbx($request['mid']) ?>&lang=<? echo $request['lang']; ?>" name='axxon_recaptcha_settings'>
        <?php
        foreach($tabs as $tab) {
            if($tab[ 'OPTIONS']) {
                $tabControl->BeginNextTab();
                __AdmSettingsDrawList($module_id, $tab['OPTIONS']);
            }
        }
        $tabControl->BeginNextTab();
        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
        $tabControl->Buttons();
        ?>
        <input type="submit" name="update" value=<?php echo  Loc::getMessage('AXXON_RECAPTCHA_SAVE')?>>
        <input type="reset" name="reset" value=<?php echo Loc::getMessage('AXXON_RECAPTCHA_RESET')?>>
        <?php echo bitrix_sessid_post();?>
    </form>
<?php $tabControl->End();