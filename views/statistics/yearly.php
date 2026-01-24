<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Coworker */
/* @var $year int */
/* @var $yearlyData array */
/* @var $monthlyDataJson string */

$this->title = Yii::t('app', 'Yearly Statistics for {name} - {year}', [
    'name' => $model->name,
    'year' => $year
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Statistics'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
    <div class="statistics-yearly">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= Html::encode($this->title) ?></h3>
                <div class="card-tools">
                    <div class="btn-group">
                        <?php foreach ([$year-1, $year, $year+1] as $y): ?>
                            <?= Html::a(
                                $y,
                                ['yearly', 'id' => $model->id, 'year' => $y],
                                ['class' => 'btn btn-sm ' . ($y == $year ? 'btn-primary' : 'btn-default')]
                            ) ?>
                        <?php endforeach; ?>
                    </div>

                    <?= Html::a('<i class="fas fa-arrow-left"></i> ' . Yii::t('app', 'Back to Monthly'),
                        ['view', 'id' => $model->id],
                        ['class' => 'btn btn-default btn-sm ml-2']
                    ) ?>
                </div>
            </div>

            <div class="card-body">
                <!-- Годовая сводка -->
                <div class="row mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= Yii::t('app', 'Total Hours') ?></span>
                                <span class="info-box-number">
                                <?= $yearlyData['totalDebitHours'] + $yearlyData['totalCreditHours'] ?>
                            </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                <?= Yii::t('app', '{paid} paid, {unpaid} unpaid', [
                                    'paid' => $yearlyData['totalDebitHours'],
                                    'unpaid' => $yearlyData['totalCreditHours']
                                ]) ?>
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= Yii::t('app', 'Total Amount') ?></span>
                                <span class="info-box-number">
                                <?= Yii::$app->formatter->asCurrency($yearlyData['totalDebitAmount'] + $yearlyData['totalCreditAmount']) ?>
                            </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                <?= Yii::t('app', '{paid} paid', [
                                    'paid' => Yii::$app->formatter->asCurrency($yearlyData['totalDebitAmount'])
                                ]) ?>
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= Yii::t('app', 'Total Orders') ?></span>
                                <span class="info-box-number"><?= $yearlyData['totalOrders'] ?></span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                <?= Yii::t('app', '{reports} reports', [
                                    'reports' => $yearlyData['totalReports']
                                ]) ?>
                            </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="info-box bg-gradient-danger">
                            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= Yii::t('app', 'Average Monthly') ?></span>
                                <span class="info-box-number">
                                <?= Yii::$app->formatter->asCurrency(
                                    ($yearlyData['totalDebitAmount'] + $yearlyData['totalCreditAmount']) / 12
                                ) ?>
                            </span>
                                <div class="progress">
                                    <div class="progress-bar" style="width: 100%"></div>
                                </div>
                                <span class="progress-description">
                                <?= Yii::t('app', 'Per month average') ?>
                            </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Месячная статистика -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><?= Yii::t('app', 'Monthly Breakdown') ?></h3>
                    </div>
                    <div class="card-body p-0">
                        <?= GridView::widget([
                            'dataProvider' => new \yii\data\ArrayDataProvider([
                                'allModels' => $yearlyData['monthlyData'],
                                'pagination' => false,
                            ]),
                            'tableOptions' => ['class' => 'table table-hover'],
                            'columns' => [
                                [
                                    'attribute' => 'monthName',
                                    'label' => Yii::t('app', 'Month'),
                                    'format' => 'raw',
                                    'value' => function($data) use ($model, $year) {
                                        return Html::a(
                                            $data['monthName'],
                                            ['view', 'id' => $model->id, 'year' => $year, 'month' => $data['month']],
                                            ['data-pjax' => 0]
                                        );
                                    },
                                ],
                                [
                                    'attribute' => 'debitHours',
                                    'label' => Yii::t('app', 'Paid Hours'),
                                    'format' => 'decimal',
                                ],
                                [
                                    'attribute' => 'creditHours',
                                    'label' => Yii::t('app', 'Unpaid Hours'),
                                    'format' => 'decimal',
                                ],
                                [
                                    'attribute' => 'debitAmount',
                                    'label' => Yii::t('app', 'Paid Amount'),
                                    'format' => 'currency',
                                ],
                                [
                                    'attribute' => 'creditAmount',
                                    'label' => Yii::t('app', 'Unpaid Amount'),
                                    'format' => 'currency',
                                ],
                                [
                                    'attribute' => 'ordersCount',
                                    'label' => Yii::t('app', 'Orders'),
                                    'format' => 'decimal',
                                ],
                                [
                                    'attribute' => 'reportsCount',
                                    'label' => Yii::t('app', 'Reports'),
                                    'format' => 'decimal',
                                ],
                                [
                                    'label' => Yii::t('app', 'Hourly Rate'),
                                    'value' => function($data) {
                                        $totalHours = $data['debitHours'] + $data['creditHours'];
                                        $totalAmount = $data['debitAmount'] + $data['creditAmount'];
                                        return $totalHours > 0 ? $totalAmount / $totalHours : 0;
                                    },
                                    'format' => 'currency',
                                ],
                            ],
                        ]); ?>
                    </div>
                </div>

                <!-- График годовой статистики -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h3 class="card-title"><?= Yii::t('app', 'Annual Chart') ?></h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div id="annual-chart" style="height: 400px;"></div>
                            </div>
                            <div class="col-md-4">
                                <div id="annual-pie-chart" style="height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php
$this->registerJs(<<<JS
// Подготавливаем данные для годового графика
var monthlyData = $monthlyDataJson;
var labels = [];
var paidAmounts = [];
var unpaidAmounts = [];
var paidHours = [];

monthlyData.forEach(function(month) {
    labels.push(month.monthName);
    paidAmounts.push(month.debitAmount);
    unpaidAmounts.push(month.creditAmount);
    paidHours.push(month.debitHours);
});

// Линейный график по месяцам
var annualCtx = document.getElementById('annual-chart').getContext('2d');
var annualChart = new Chart(annualCtx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [
            {
                label: 'Paid Amount',
                data: paidAmounts,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1,
                yAxisID: 'y'
            },
            {
                label: 'Unpaid Amount',
                data: unpaidAmounts,
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)',
                tension: 0.1,
                yAxisID: 'y'
            },
            {
                label: 'Paid Hours',
                data: paidHours,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.1,
                yAxisID: 'y1'
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: {
            mode: 'index',
            intersect: false,
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Month'
                }
            },
            y: {
                type: 'linear',
                display: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Amount ($)'
                }
            },
            y1: {
                type: 'linear',
                display: true,
                position: 'right',
                title: {
                    display: true,
                    text: 'Hours'
                },
                grid: {
                    drawOnChartArea: false,
                },
            }
        }
    }
});

// Круговая диаграмма
var pieCtx = document.getElementById('annual-pie-chart').getContext('2d');
var pieChart = new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: ['Paid Amount', 'Unpaid Amount'],
        datasets: [{
            data: [{$yearlyData['totalDebitAmount']}, {$yearlyData['totalCreditAmount']}],
            backgroundColor: [
                'rgb(75, 192, 192)',
                'rgb(255, 99, 132)'
            ],
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom',
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        var label = context.label || '';
                        if (label) {
                            label += ': ';
                        }
                        label += '\$' + context.raw.toFixed(2);
                        return label;
                    }
                }
            }
        }
    }
});
JS);
?>