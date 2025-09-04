<?php

namespace app\assets;

use yii\web\AssetBundle;

class GalleryAsset extends AssetBundle
{
    public $js = [
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/lightgallery.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/zoom/lg-zoom.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/thumbnail/lg-thumbnail.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/plugins/fullscreen/lg-fullscreen.min.js'
    ];
    public $css = [
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lightgallery-bundle.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-autoplay.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-fullscreen.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/lightgallery/2.8.3/css/lg-thumbnail.min.css',
    ];
}