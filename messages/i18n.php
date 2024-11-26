<?php

return [
    'sourcePath' => __DIR__ . DIRECTORY_SEPARATOR,
    'languages' => ['ru'],
    'translator' => ['\Yii::t', 'Yii::t'],
    'sort' => true,
    'removeUnused' => false,
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
