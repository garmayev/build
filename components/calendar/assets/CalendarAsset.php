<?php

namespace app\components\calendar\assets;

use yii\web\AssetBundle;
use yii\web\JqueryAsset;

class CalendarAsset extends AssetBundle
{
    public $sourcePath = '@app/components/calendar/assets';
    public $css = ['calendar_v2.css'];
    public $js = ['calendar.js'];
    public $depends = [
        JQueryAsset::class,
    ];
}