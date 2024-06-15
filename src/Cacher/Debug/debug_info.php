<?php
use Uru\BitrixCacher\Debug\CacheDebugger;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    exit;
}

// @var $bShowCacheStat
if (!$bShowCacheStat) {
    return;
}

$totalQueryCount = CacheDebugger::getTracksCount();
$queryGroups = [
    'Hits' => CacheDebugger::getCacheTracksGrouped('hits'),
    'Misses' => CacheDebugger::getCacheTracksGrouped('misses'),
    'Запросы с нулевым TTL' => CacheDebugger::getCacheTracksGrouped('zero_ttl'),
];

echo '<div class="bx-component-debug bx-debug-summary" id="bx-component-debug-bitrix-cacher" style="bottom: 160px;">';
echo 'Статистика arrilot/bitrix-cacher<br>';
echo '<a title="Посмотреть подробную статистику по запросам" href="javascript:BX_DEBUG_INFO_BITRIX_CACHER.Show(); BX_DEBUG_INFO_BITRIX_CACHER.ShowDetails(\'BX_DEBUG_INFO_BITRIX_CACHER_1\');">Всего запросов в кэш: </a> '.$totalQueryCount.'<br>';
echo '</div><div class="empty"></div>';

// CJSPopup
require_once $_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/modules/main/interface/admin_lib.php';
?>
<script type="text/javascript">
    // Ставим суммарную инфу по высоте так, чтобы не накладывалось на останые
    document.addEventListener("DOMContentLoaded", function(){
        var maxTop = 0;
        var elements = document.getElementsByClassName('bx-component-debug');
        for (var i = 0; i < elements.length; i++)
        {
            if (elements[i].style.display!=="none" && elements[i].getAttribute('id') !== 'bx-component-debug-bitrix-cacher') {
                var top = Number(elements[i].style.bottom.replace("px", ""));
                if (maxTop < top) {
                    maxTop = top;
                }
            }
        }

        if (!maxTop) {
            maxTop = 60;
        } else {
            maxTop += 60;
        }
        document.getElementById('bx-component-debug-bitrix-cacher').style.bottom = maxTop + 'px';
    });
    BX_DEBUG_INFO_BITRIX_CACHER = new BX.CDebugDialog();
</script>
<?php
$obJSPopup = new CJSPopupOnPage('', []);
$obJSPopup->jsPopup = 'BX_DEBUG_INFO_BITRIX_CACHER';
$obJSPopup->StartDescription('bx-core-debug-info');
?>
<p>Статистика arrilot/bitrix-cacher</p>
<p>Всего запросов: <?php echo $totalQueryCount; ?></p>
<?php
$obJSPopup->StartContent(['buffer' => true]);
if ($totalQueryCount > 0) {
    ?>
    <div class="bx-debug-content bx-debug-content-details">
    <?php foreach ($queryGroups as $name => $queries) { ?>
        <?php
            if (!$queries) {
                continue;
            }
        ?>
        <b><?php echo $name; ?>:</b>
        <table cellpadding="3px" cellspacing="0px" border="1px" style="margin-top:5px">
            <tr>
                <th>key</th>
                <th>initDir</th>
                <th>baseDir</th>
                <th>количество вызовов</th>
                <th>примерный суммарный размер данных, KB</th>
            </tr>

            <?php foreach ($queries as $query) { ?>
                <tr>
                    <td><?php echo htmlspecialcharsbx($query['key']); ?></td>
                    <td><?php echo htmlspecialcharsbx($query['initDir']); ?></td>
                    <td><?php echo htmlspecialcharsbx($query['basedir']); ?></td>
                    <td <?php if ($query['count'] > 1) { ?>style="color:red"<?php } ?> ><?php echo htmlspecialcharsbx($query['count']); ?></td>
                    <td><?php echo htmlspecialcharsbx(round($query['size'] / 1024)); ?></td>
                </tr>
            <?php } ?>
        </table>
        <br>
    <?php } ?>
    </div>
    <?php
}
$obJSPopup->StartButtons();
$obJSPopup->ShowStandardButtons(['close']);
