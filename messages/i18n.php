<?php

return [
    'sourcePath' => dirname(__DIR__, 1),
    'languages' => ['ru'],
    'interactive' => true,
    'translator' => ['\Yii::t', 'Yii::t'],
    'sort' => true,
    'removeUnused' => true,
    'markUnused' => true,
    'only' => ['*.php'],
    'except' => [
        '.*',
        '/.*',
        '/messages',
        '/tests',
        '/runtime',
        '/vendor',
        '/BaseYii.php',
    ],
    'format' => 'php',
    'messagePath' => __DIR__,
    'overwrite' => true,
];
