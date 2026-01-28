<?php

namespace app\assets;

use yii\web\AssetBundle;

class ScheduleAsset extends AssetBundle
{
    public $js = [
        '/components/ElementFactory.js',
        '/components/schedule/schedule.js',
        '/components/calendar/calendar.js',
        '/components/dateSelector/dateSelector.js',
        '/components/timeline/jquery.timeline.min.js',
    ];
    public $css = [
        '/components/schedule/schedule.css',
        '/components/calendar/calendar_v2.css',
        '/components/dateSelector/dateSelector.css',
        '/components/timeline/jquery.timeline.min.css',
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap5\BootstrapAsset',
        'yii\web\JqueryAsset',
    ];
}