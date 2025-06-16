<?php
/**
 * @var string $assetDir
 */
?>
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="/" class="brand-link">
<!--        <img src="/images/logo-white.svg" alt="AdminLTE Logo" class="brand-image"-->
<!--             style="opacity: .8; min-width: 70px;">-->
        <span class="brand-text font-weight-light px-3" style="letter-spacing: 10px;">&nbsp;</span>
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
                    echo \Yii::$app->user->identity->username;
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
                                'badge' => '<span class="right badge badge-danger">DEV</span>',
                                'url' => ['#'],
                                'active' => \Yii::$app->controller->id === 'material',
                            ], [
                                'label' => \Yii::t('app', 'Coworkers'),
                                'icon' => 'splotch',
                                'url' => ['/coworker/index'],
                                'active' => \Yii::$app->controller->id === 'coworker',
                            ], [
                                'label' => \Yii::t('app', 'Technique'),
                                'icon' => 'cube',
                                'badge' => '<span class="right badge badge-danger">DEV</span>',
                                'url' => ['#'],
                                'active' => \Yii::$app->controller->id === 'technique',
                            ], [
                                'label' => \Yii::t('app', 'Equipment'),
                                'icon' => 'cube',
                                'badge' => '<span class="right badge badge-danger">DEV</span>',
                                'url' => ['#'],
                                'active' => \Yii::$app->controller->id === 'equipment',
                            ]
                        ]
                    ], [
                        'label' => \Yii::t('app', 'Orders'),
                        'icon' => 'swatchbook',
                        'url' => ['/order/index'],
                        'active' => \Yii::$app->controller->id === 'order',
                    ], [
                        'label' => \Yii::t('app', 'Calendar'),
                        'icon' => 'calendar',
                        'url' => ['/site/calendar'],
                        'active' => \Yii::$app->controller->id === 'site' && \Yii::$app->controller->action->id === 'calendar',
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
                            ], [
                                'label' => \Yii::t('user', 'Users'),
                                'icon' => 'person',
                                'url' => ['/user/admin/index'],
                                'active' => \Yii::$app->controller->id === 'admin',
                            ]
                        ],
                    ]
                ]
            ])
            ?>
        </nav>
    </div>
</aside>
