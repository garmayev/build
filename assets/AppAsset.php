<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\bootstrap4\BootstrapAsset;
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
        '/react/css/main.d37e276b.css',
    ];
    public $js = [
        '/js/jquery.maskedinput.js',
        '/react/js/453.abd1f321.chunk.js',
        '/react/js/main.ca855874.js',
    ];
    public $depends = [
        YiiAsset::class,
        JQueryAsset::class,
        BootstrapAsset::class,
    ];
}
