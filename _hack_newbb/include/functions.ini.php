<?php

declare(strict_types=1);

// $Id$
//  ------------------------------------------------------------------------ //
//                XOOPS - PHP Content Management System                      //
//                    Copyright (c) 2000 XOOPS.org                           //
//                       <https://www.xoops.org>                             //
//  ------------------------------------------------------------------------ //
//  This program is free software; you can redistribute it and/or modify     //
//  it under the terms of the GNU General Public License as published by     //
//  the Free Software Foundation; either version 2 of the License, or        //
//  (at your option) any later version.                                      //
//                                                                           //
//  You may not change or alter any portion of this comment or credits       //
//  of supporting developers from this source code or any supporting         //
//  source code which is considered copyrighted (c) material of the          //
//  original comment or credit authors.                                      //
//                                                                           //
//  This program is distributed in the hope that it will be useful,          //
//  but WITHOUT ANY WARRANTY; without even the implied warranty of           //
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            //
//  GNU General Public License for more details.                             //
//                                                                           //
//  You should have received a copy of the GNU General Public License        //
//  along with this program; if not, write to the Free Software              //
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307 USA //
//  ------------------------------------------------------------------------ //
//  Author: phppp (D.J., infomax@gmail.com)                                  //
//  URL: http://xoopsforge.com, http://xoops.org.cn                          //
//  Project: Article Project                                                 //
//  ------------------------------------------------------------------------ //
if (!defined('XOOPS_ROOT_PATH')) {
    exit();
}

if (defined('NEWBB_FUNCTIONS_INI')) {
    return;
}
define('NEWBB_FUNCTIONS_INI', 1);

require_once XOOPS_ROOT_PATH . '/Frameworks/art/functions.php';

/**
 * @return bool
 */
function newbb_load_object()
{
    return load_object();
}

/**
 * @param $message
 */
function newbb_message($message)
{
    global $xoopsModuleConfig;

    if (!empty($xoopsModuleConfig['do_debug'])) {
        if (is_array($message) || is_object($message)) {
            echo '<div><pre>';

            print_r($message);

            echo '</pre></div>';
        } else {
            echo "<div>$message</div>";
        }
    }
}

/**
 * @return array|mixed|null
 */
function newbb_load_config()
{
    static $moduleConfig;

    if (isset($moduleConfig)) {
        return $moduleConfig;
    }

    if (isset($GLOBALS['xoopsModule']) && is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname', 'n') == 'newbb') {
        if (!empty($GLOBALS['xoopsModuleConfig'])) {
            $moduleConfig = &$GLOBALS['xoopsModuleConfig'];
        } else {
            return null;
        }
    } else {
        $moduleHandler = xoops_getHandler('module');

        $module = $moduleHandler->getByDirname('newbb');

        $configHandler = xoops_getHandler('config');

        $criteria = new CriteriaCompo(new Criteria('conf_modid', $module->getVar('mid')));

        $configs = &$configHandler->getConfigs($criteria);

        foreach (array_keys($configs) as $i) {
            $moduleConfig[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
        }

        unset($configs);
    }

    if ($customConfig = @include(XOOPS_ROOT_PATH . '/modules/newbb/include/plugin.php')) {
        $moduleConfig = array_merge($moduleConfig, $customConfig);
    }

    return $moduleConfig;
}

/**
 * @return array|mixed|null
 */
function getConfigForBlock()
{
    return newbb_load_config();
    static $newbbConfig;

    if (isset($newbbConfig)) {
        return $newbbConfig;
    }

    if (is_object($GLOBALS['xoopsModule']) && $GLOBALS['xoopsModule']->getVar('dirname') == 'newbb') {
        $newbbConfig = &$GLOBALS['xoopsModuleConfig'];
    } else {
        $moduleHandler = xoops_getHandler('module');

        $newbb = $moduleHandler->getByDirname('newbb');

        $configHandler = xoops_getHandler('config');

        $criteria = new CriteriaCompo(new Criteria('conf_modid', $newbb->getVar('mid')));

        $criteria->add(new Criteria('conf_name', "('show_realname', 'subject_prefix', 'allow_require_reply')", 'IN'));

        $configs = &$configHandler->getConfigs($criteria);

        foreach (array_keys($configs) as $i) {
            $newbbConfig[$configs[$i]->getVar('conf_name')] = $configs[$i]->getConfValueForOutput();
        }

        unset($newbb, $configs);
    }

    return $newbbConfig;
}

// Backword compatible
/**
 * @param        $filename
 * @param string $module
 * @param string $default
 * @return bool|mixed
 */
function newbb_load_lang_file($filename, $module = '', $default = 'english')
{
    if (function_exists('xoops_load_lang_file')) {
        return xoops_load_lang_file($filename, $module, $default);
    }

    $lang = $GLOBALS['xoopsConfig']['language'];

    $path = XOOPS_ROOT_PATH . (empty($module) ? '/' : "/modules/$module/") . 'language';

    if (!($ret = @include_once("$path/$lang/$filename.php"))) {
        $ret = @require_once "$path/$default/$filename.php";
    }

    return $ret;
}

// Adapted from PMA_getIp() [phpmyadmin project]
/**
 * @param false $asString
 * @return false|int|mixed
 */
function newbb_getIP($asString = false)
{
    return mod_getIP($asString);
}

/**
 * @param        $time
 * @param string $format
 * @param string $timeoffset
 * @return false|string
 */
function newbb_formatTimestamp($time, $format = 'c', $timeoffset = '')
{
    /*
    if(strtolower($format) == "reg" || strtolower($format) == "") {
        $format = "c";
    }
    if( (strtolower($format) == "custom" || strtolower($format) == "c") && !empty($GLOBALS["xoopsModuleConfig"]["formatTimestamp_custom"]) ) {
        $format = $GLOBALS["xoopsModuleConfig"]["formatTimestamp_custom"];
    }

    load_functions("locale");
    return XoopsLocal::formatTimestamp($time, $format, $timeoffset);

    if(class_exists("XoopsLocal") && is_callable(array("XoopsLocal", "formatTimestamp")) && defined("_TODAY")){
        return XoopsLocal::formatTimestamp($time, $format, $timeoffset);
    }

    */

    global $xoopsConfig, $xoopsUser;

    if (mb_strtolower($format) == 'rss' || mb_strtolower($format) == 'r') {
        $TIME_ZONE = '';

        if (!empty($GLOBALS['xoopsConfig']['server_TZ'])) {
            $server_TZ = abs((int)($GLOBALS['xoopsConfig']['server_TZ'] * 3600.0));

            $prefix = $GLOBALS['xoopsConfig']['server_TZ'] < 0 ? ' -' : ' +';

            $TIME_ZONE = $prefix . date('Hi', $server_TZ);
        }

        return gmdate('D, d M Y H:i:s', (int)$time) . $TIME_ZONE;
    }

    $usertimestamp = xoops_getUserTimestamp($time, $timeoffset);

    switch (mb_strtolower($format)) {
        case 's':
            $datestring = _SHORTDATESTRING;

            break;
        case 'm':
            $datestring = _MEDIUMDATESTRING;

            break;
        case 'mysql':
            $datestring = 'Y-m-d H:i:s';

            break;
        case 'rss':
            $datestring = 'r';

            break;
        case 'l':
            $datestring = _DATESTRING;

            break;
        case 'c':
        case 'custom':
        default:
            newbb_load_lang_file('main', 'newbb');
            $current_timestamp = xoops_getUserTimestamp(time(), $timeoffset);
            if (date('Ymd', $usertimestamp) == date('Ymd', $current_timestamp)) {
                $datestring = _MD_TODAY;
            } elseif (date('Ymd', $usertimestamp + 24 * 60 * 60) == date('Ymd', $current_timestamp)) {
                $datestring = _MD_YESTERDAY;
            } elseif (date('Y', $usertimestamp) == date('Y', $current_timestamp)) {
                $datestring = _MD_MONTHDAY;
            } else {
                $datestring = _MD_YEARMONTHDAY;
            }

            break;
    }

    return date($datestring, $usertimestamp);
}
