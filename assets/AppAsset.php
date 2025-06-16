<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\bootstrap4\BootstrapAsset;
use yii\web\AssetBundle;
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
        '/react/css/main.2088561c.css',
    ];
    public $js = [
        '/js/jquery.maskedinput.js',
        '/react/js/453.0799a3d3.chunk.js',
        '/react/js/main.bf149a15.js',
    ];
//    public $jsOptions = [
//        'position' => \yii\web\View::POS_LOAD,
//    ];
    public $depends = [
        YiiAsset::class,
        BootstrapAsset::class,
    ];
}
