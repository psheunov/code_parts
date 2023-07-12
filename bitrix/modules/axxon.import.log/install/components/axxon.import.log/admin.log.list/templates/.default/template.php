<?php
CJSCore::Init(["jquery"]);

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 */
?>
<script type="application/javascript">
    BX       = BX || {};
    BX.axxon = {
        params: <?php echo json_encode($this->getComponent()->getSignedParameters()); ?>,
    };
</script>

<table class="internal log-list" width="100%" cellspacing="0" cellpadding="0" border="0">
    <thead>
    <tr class="heading">
        <td>дата</td>
        <td>название поля</td>
        <td>старое значение</td>
        <td>новое значение</td>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($arResult['ROWS'] as $row): ?>
        <tr>
            <td><?php echo $row['timestamp']; ?></td>
            <td><?php echo $row['field']; ?></td>
            <td><?php echo $row['old_value']; ?></td>
            <td><?php echo $row['new_value']; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php if ($arResult['PAGES'] > 1): ?>
    <div class="adm-navigation log-navigation">
        <div class="adm-nav-pages-block">
            <span class="adm-nav-page adm-nav-page-prev js-subnav-page-prev"><span
                        class="adm-subnav-page-prev-before "></span></span>
            <?php for ($i = 1; $i <= $arResult['PAGES']; $i++): ?>
                <span class="adm-nav-page js-load_page <?php echo ($i == 1) ? 'adm-nav-page-active' : '' ?>"
                      data-page="<?php echo $i - 1; ?>"
                >
                    <?php echo $i; ?>
                </span>
            <?php endfor; ?>
            <span class="adm-nav-page adm-nav-page-next js-subnav-page-next"><span
                        class="adm-subnav-page-next-before"></span></span>
        </div>
    </div>
<?php endif; ?>
