<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\bootstrap5\BootstrapAsset;
use yii\web\AssetBundle;
use yii\web\JqueryAsset;
use yii\web\YiiAsset;

/**
 * Main application asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '/css/site.css',
        '/components/dateSelector/dateSelector.css',
        '/components/calendar/calendar_v2.css',
        '/components/dhtmlx.gantt/dhtmlxgantt.css',
//        '/react/css/main.3113935e.css',
    ];
    public $js = [
        '/components/DateUtils.js',
        '/components/dateSelector/dateSelector.js',
        '/components/calendar/calendar.js',
        '/components/calendar/calendar_v2.js',
        '/components/dhtmlx.gantt/dhtmlxgantt.js',
//        '/react/js/453.abd1f321.chunk.js',
//        '/react/js/main.ff9963a6.js',
    ];
    public $depends = [
        YiiAsset::class,
        JQueryAsset::class,
        BootstrapAsset::class,
        FontAwesomeAsset::class,
    ];
}
