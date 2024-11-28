<?php

namespace app\assets;

class ReactAsset extends \yii\web\AssetBundle
{
    public $js = [
        ['https://unpkg.com/react@18/umd/react.development.js'],
        ['https://unpkg.com/react-dom@18/umd/react-dom.development.js'],
        ['https://unpkg.com/@babel/standalone@7.19.3/babel.js'],
    ];

    public $jsOptions = [ 'position' => \yii\web\View::POS_HEAD ];
}
