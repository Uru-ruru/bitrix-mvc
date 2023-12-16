<?php

namespace Uru\BitrixModels\Debug;

use Illuminate\Database\Capsule\Manager;

class IlluminateQueryDebugger
{
    public static function onAfterEpilogHandler()
    {
        global $DB, $USER;

        $bExcel = isset($_REQUEST['mode']) && 'excel' === $_REQUEST['mode'];
        if (!defined('ADMIN_AJAX_MODE') && !defined('PUBLIC_AJAX_MODE') && !$bExcel) {
            $bShowStat = ($DB->ShowSqlStat && ($USER->CanDoOperation('edit_php') || 'Y' == $_SESSION['SHOW_SQL_STAT']));
            if ($bShowStat && class_exists(Manager::class) && Manager::logging()) {
                require_once __DIR__.'/debug_info.php';
            }
        }
    }

    public static function interpolateQuery($query, $params)
    {
        $keys = [];

        // build a regular expression for each parameter
        foreach ($params as $key => $value) {
            $keys[] = is_string($key) ? '/:'.$key.'/' : '/[?]/';
            $params[$key] = "'".$value."'";
        }

        return preg_replace($keys, $params, $query, 1, $count);
    }
}
