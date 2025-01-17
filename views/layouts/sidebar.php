<?php
/**
 * @var string $assetDir
 */
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
        <img src="<?= $assetDir ?>/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
             style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= $assetDir ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">
                    <?php
                    if (\Yii::$app->user->identity->profile->name) {
                        echo \Yii::$app->user->identity->profile->name;
                    } else {
                        echo \Yii::$app->user->identity->username;
                    }
                    ?>
                </a>
            </div>
        </div>

        <nav class="mt-2">
            <?php
//            var_dump( \Yii::$app->controller->id );
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    [
                        'label' => \Yii::t('app', 'Dashboard'),
                        'url' => ['/site/index'],
                        'icon' => 'tachometer-alt',
                    ], [
                        'label' => \Yii::t('app', 'Building'),
                        'icon' => 'building',
                        'url' => ['/building/index'],
                        'active' => \Yii::$app->controller->id === 'building' || \Yii::$app->controller->id === 'location',
                    ], [
                        'label' => \Yii::t('app', 'Control Panel'),
                        'icon' => 'th',
                        'items' => [
                            [
                                'label' => \Yii::t('app', 'Categories'),
                                'icon' => 'layer-group',
                                'url' => ['/category/index'],
                                'active' => \Yii::$app->controller->id === 'category',
                            ], [
                                'label' => \Yii::t('app', 'Materials'),
                                'icon' => 'crop',
                                'url' => ['/material/index'],
                                'active' => \Yii::$app->controller->id === 'material',
                            ], [
                                'label' => \Yii::t('app', 'Coworkers'),
                                'icon' => 'splotch',
                                'url' => ['/coworker/index'],
                                'active' => \Yii::$app->controller->id === 'coworker',
                            ], [
                                'label' => \Yii::t('app', 'Technique'),
                                'icon' => 'cube',
                                'url' => ['/technique/index'],
                                'active' => \Yii::$app->controller->id === 'technique',
                            ], [
                                'label' => \Yii::t('app', 'Equipment'),
                                'icon' => 'cube',
                                'url' => ['/equipment/index'],
                                'active' => \Yii::$app->controller->id === 'equipment',
                            ]
                        ]
                    ], [
                        'label' => \Yii::t('app', 'Orders'),
                        'icon' => 'swatchbook',
                        'url' => ['/order/index'],
                        'active' => \Yii::$app->controller->id === 'order',
                    ], [
                        'label' => \Yii::t('app', 'Configuration'),
                        'icon' => 'cog',
                        'items' => [
                            [
                                'label' => \Yii::t('app', 'Properties'),
                                'icon' => '',
                                'url' => ['/property/index'],
                                'active' => \Yii::$app->controller->id === 'property',
                            ], [
                                'label' => \Yii::t('app', 'Dimensions'),
                                'icon' => '',
                                'url' => ['/dimension/index'],
                                'active' => \Yii::$app->controller->id === 'dimension',
                            ]
                        ],
                    ]
                ]
            ])
            ?>
        </nav>
    </div>
</aside>
