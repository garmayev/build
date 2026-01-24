<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\DetailView;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Coworker */
/* @var $statistics array */
/* @var $ordersDataProvider yii\data\ActiveDataProvider */
/* @var $year int */
/* @var $month int */
/* @var $monthName string */
/* @var $dailyData array */

$this->title = Yii::t('app', 'Statistics for {name}', ['name' => $model->name]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Statistics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Месяцы для навигации
$months = [
    1 => Yii::t('app', 'January'),
    2 => Yii::t('app', 'February'),
    3 => Yii::t('app', 'March'),
    4 => Yii::t('app', 'April'),
    5 => Yii::t('app', 'May'),
    6 => Yii::t('app', 'June'),
    7 => Yii::t('app', 'July'),
    8 => Yii::t('app', 'August'),
    9 => Yii::t('app', 'September'),
    10 => Yii::t('app', 'October'),
    11 => Yii::t('app', 'November'),
    12 => Yii::t('app', 'December'),
];
?>
    <div class="statistics-view">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
                            <?= $monthName . ' ' . $year ?> <span class="caret"></span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <h6 class="dropdown-header"><?= Yii::t('app', 'Select Period') ?></h6>
                            <?php for ($y = date('Y'); $y >= 2020; $y--): ?>
                                <?php for ($m = 12; $m >= 1; $m--): ?>
                                    <?php if ($y == $year && $m == $month) continue; ?>
                                    <a class="dropdown-item" href="<?= Url::to(['view', 'id' => $model->id, 'year' => $y, 'month' => $m]) ?>">
                                        <?= $months[$m] . ' ' . $y ?>
                                    </a>
                                <?php endfor; ?>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="btn-group ml-2">
                        <?= Html::a('<i class="fas fa-arrow-left"></i> ' . Yii::t('app', 'Back'),
                            ['index', 'year' => $year, 'month' => $month],
                            ['class' => 'btn btn-default btn-sm']
                        ) ?>
                        <?= Html::a('<i class="fas fa-calendar-alt"></i> ' . Yii::t('app', 'Year View'),
                            ['yearly', 'id' => $model->id, 'year' => $year],
                            ['class' => 'btn btn-info btn-sm ml-1']
                        ) ?>
                        <?= Html::a('<i class="fas fa-download"></i> ' . Yii::t('app', 'Export'),
                            ['export-employee', 'id' => $model->id, 'year' => $year, 'month' => $month],
                            ['class' => 'btn btn-success btn-sm ml-1']
                        ) ?>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Основная информация о сотруднике -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card card-primary card-outline">
                            <div class="card-body box-profile">
                                <div class="text-center">
                                    <div class="profile-user-img img-circle img-fluid"
                                         style="width: 100px; height: 100px; background-color: #6c757d;
                                            color: white; display: flex; align-items: center;
                                            justify-content: center; font-size: 36px; margin: 0 auto;">
                                        <?= strtoupper(substr($model->name, 0, 1)) ?>
                                    </div>
                                </div>

                                <h3 class="profile-username text-center"><?= Html::encode($model->name) ?></h3>

                                <p class="text-muted text-center"><?= Html::encode($model->email) ?></p>

                                <ul class="list-group list-group-unbordered mb-3">
                                    <li class="list-group-item">
                                        <b><?= Yii::t('app', 'Status') ?></b>
                                        <span class="float-right">
                                        <?php
                                        $statusClass = $model->status == $model::STATUS_ACTIVE ? 'success' : 'warning';
                                        echo Html::tag('span', $model->getStatusName(), [
                                            'class' => 'badge badge-' . $statusClass
                                        ]);
                                        ?>
                                    </span>
                                    </li>
                                    <li class="list-group-item">
                                        <b><?= Yii::t('app', 'Priority') ?></b>
                                        <span class="float-right">
                                        <?php
                                        $priorityClass = [
                                            $model::PRIORITY_LOW => 'secondary',
                                            $model::PRIORITY_NORMAL => 'info',
                                            $model::PRIORITY_HIGH => 'danger',
                                        ][$model->priority_level] ?? 'secondary';

                                        echo Html::tag('span', $model->priority_level, [
                                            'class' => 'badge badge-' . $priorityClass
                                        ]);
                                        ?>
                                    </span>
                                    </li>
                                    <li class="list-group-item">
                                        <b><?= Yii::t('app', 'Hourly Rate') ?></b>
                                        <span class="float-right">
                                        <?= Yii::$app->formatter->asCurrency($model->price->price) ?>
                                    </span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Статистика за месяц -->
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info"><i class="fas fa-clock"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= Yii::t('app', 'Paid Hours') ?></span>
                                        <span class="info-box-number"><?= $statistics['debitHours'] ?></span>
                                        <div class="progress">
                                            <div class="progress-bar bg-info"
                                                 style="width: <?= min(100, ($statistics['debitHours'] / max(1, $statistics['debitHours'] + $statistics['creditHours'])) * 100) ?>%">
                                            </div>
                                        </div>
                                        <span class="progress-description">
                                        <?= Yii::t('app', '{percent}% of total hours', [
                                            'percent' => number_format(($statistics['debitHours'] / max(1, $statistics['debitHours'] + $statistics['creditHours'])) * 100, 1)
                                        ]) ?>
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-warning"><i class="fas fa-hourglass-half"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= Yii::t('app', 'Unpaid Hours') ?></span>
                                        <span class="info-box-number"><?= $statistics['creditHours'] ?></span>
                                        <div class="progress">
                                            <div class="progress-bar bg-warning"
                                                 style="width: <?= min(100, ($statistics['creditHours'] / max(1, $statistics['debitHours'] + $statistics['creditHours'])) * 100) ?>%">
                                            </div>
                                        </div>
                                        <span class="progress-description">
                                        <?= Yii::t('app', 'Waiting for payment') ?>
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-success"><i class="fas fa-money-bill-wave"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= Yii::t('app', 'Paid Amount') ?></span>
                                        <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($statistics['debitAmount']) ?></span>
                                        <div class="progress">
                                            <div class="progress-bar bg-success"
                                                 style="width: <?= min(100, ($statistics['debitAmount'] / max(1, $statistics['debitAmount'] + $statistics['creditAmount'])) * 100) ?>%">
                                            </div>
                                        </div>
                                        <span class="progress-description">
                                        <?= Yii::t('app', 'Average: {amount}/hour', [
                                            'amount' => Yii::$app->formatter->asCurrency($statistics['debitAmount'] / max(1, $statistics['debitHours']))
                                        ]) ?>
                                    </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 col-sm-6 col-12">
                                <div class="info-box">
                                    <span class="info-box-icon bg-danger"><i class="fas fa-file-invoice-dollar"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text"><?= Yii::t('app', 'Unpaid Amount') ?></span>
                                        <span class="info-box-number"><?= Yii::$app->formatter->asCurrency($statistics['creditAmount']) ?></span>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger"
                                                 style="width: <?= min(100, ($statistics['creditAmount'] / max(1, $statistics['debitAmount'] + $statistics['creditAmount'])) * 100) ?>%">
                                            </div>
                                        </div>
                                        <span class="progress-description">
                                        <?= Yii::t('app', 'To be paid') ?>
                                    </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Детальная статистика -->
                        <div class="card mt-3">
                            <div class="card-header">
                                <h3 class="card-title"><?= Yii::t('app', 'Detailed Statistics') ?></h3>
                            </div>
                            <div class="card-body">
                                <?= DetailView::widget([
                                    'model' => $statistics,
                                    'attributes' => [
                                        [
                                            'label' => Yii::t('app', 'Total Hours Worked'),
                                            'value' => function($data) {
                                                return $data['debitHours'] + $data['creditHours'];
                                            },
                                            'format' => 'decimal',
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Total Amount'),
                                            'value' => function($data) {
                                                return $data['debitAmount'] + $data['creditAmount'];
                                            },
                                            'format' => 'currency',
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Average Hourly Rate'),
                                            'value' => function($data) {
                                                $totalHours = $data['debitHours'] + $data['creditHours'];
                                                $totalAmount = $data['debitAmount'] + $data['creditAmount'];
                                                return $totalHours > 0 ? $totalAmount / $totalHours : 0;
                                            },
                                            'format' => 'currency',
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Completed Orders'),
                                            'value' => 'ordersCount',
//                                            'format' => 'decimal',
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Reports Submitted'),
                                            'value' => 'reportsCount',
//                                            'format' => 'decimal',
                                        ],
                                        [
                                            'label' => Yii::t('app', 'Payment Percentage'),
                                            'value' => function($data) {
                                                $total = $data['debitAmount'] + $data['creditAmount'];
                                                return $total > 0 ? ($data['debitAmount'] / $total * 100) : 0;
                                            },
                                            'format' => ['percent', 1],
                                        ],
                                    ],
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Список заказов за период -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title"><?= Yii::t('app', 'Orders for {month} {year}', [
                                'month' => $monthName,
                                'year' => $year
                            ]) ?></h3>
                    </div>
                    <div class="card-body p-0">
                        <?= GridView::widget([
                            'dataProvider' => $ordersDataProvider,
                            'tableOptions' => ['class' => 'table table-hover'],
                            'layout' => "{items}\n{pager}",
                            'columns' => [
                                [
                                    'attribute' => 'id',
                                    'label' => Yii::t('app', 'Order ID'),
                                    'format' => 'raw',
                                    'value' => function($model) {
                                        return Html::a(
                                            '#' . $model['id'],
                                            ['/order/view', 'id' => $model['id']],
                                            ['target' => '_blank']
                                        );
                                    },
                                ],
                                [
                                    'attribute' => 'title',
                                    'label' => Yii::t('app', 'Title'),
                                ],
                                [
                                    'attribute' => 'date',
                                    'label' => Yii::t('app', 'Date'),
                                    'format' => 'date',
                                ],
                                [
                                    'attribute' => 'building',
                                    'label' => Yii::t('app', 'Building'),
                                ],
                                [
                                    'attribute' => 'mode',
                                    'label' => Yii::t('app', 'Payment Mode'),
                                ],
                                [
                                    'attribute' => 'hours',
                                    'label' => Yii::t('app', 'Hours'),
                                    'format' => 'decimal',
                                ],
                                [
                                    'attribute' => 'amount',
                                    'label' => Yii::t('app', 'Amount'),
                                    'format' => 'currency',
                                ],
                                [
                                    'attribute' => 'reports',
                                    'label' => Yii::t('app', 'Reports'),
                                    'format' => 'decimal',
                                ],
                                [
                                    'attribute' => 'status',
                                    'label' => Yii::t('app', 'Status'),
                                    'format' => 'raw',
                                    'value' => function($model) {
                                        $statusColors = [
                                            'New Order' => 'primary',
                                            'Order in process' => 'info',
                                            'Order building' => 'warning',
                                            'Order completed' => 'success',
                                        ];

                                        $color = $statusColors[$model['status']] ?? 'secondary';
                                        return Html::tag('span', $model['status'], [
                                            'class' => "badge badge-$color"
                                        ]);
                                    },
                                ],
                            ],
                        ]); ?>
                    </div>
                </div>

                <!-- График по дням месяца -->
                <?php if (!empty($dailyData)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3 class="card-title"><?= Yii::t('app', 'Daily Activity') ?></h3>
                        </div>
                        <div class="card-body">
                            <div id="daily-chart" style="height: 300px;"></div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php
// JavaScript для дневного графика
if (!empty($dailyData)) {
    $dailyChartLabels = json_encode(array_keys($dailyData));
    $dailyChartHours = json_encode(array_column($dailyData, 'hours'));
    $dailyChartAmount = json_encode(array_column($dailyData, 'amount'));

    $this->registerJs(<<<JS
// Данные для дневного графика
var dailyData = {
    labels: $dailyChartLabels,
    datasets: [
        {
            label: 'Hours Worked',
            backgroundColor: 'rgba(54, 162, 235, 0.5)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            data: $dailyChartHours
        },
        {
            label: 'Amount ($)',
            backgroundColor: 'rgba(255, 99, 132, 0.5)',
            borderColor: 'rgba(255, 99, 132, 1)',
            borderWidth: 1,
            data: $dailyChartAmount,
            yAxisID: 'y1'
        }
    ]
};

// Создаем дневной график
var dailyCtx = document.getElementById('daily-chart').getContext('2d');
var dailyChart = new Chart(dailyCtx, {
    type: 'bar',
    data: dailyData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Day of Month'
                }
            },
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Hours'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Amount ($)'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        },
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});
JS);
}
?>