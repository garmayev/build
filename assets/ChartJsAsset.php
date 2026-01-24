<?php

namespace app\assets;

use yii\web\AssetBundle;

class ChartJsAsset extends AssetBundle
{
    public $sourcePath = '@npm/chart.js/dist';

    public $js = [
        'chart.min.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];

}