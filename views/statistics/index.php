<?php

use yii\helpers\Html;
use yii\grid\GridView;

/**
 * @var $this yii\web\View
 * @var $years array
 * @var $searchModel \app\models\search\StatisticsSearch
 * @var $dataProvider yii\data\ActiveDataProvider
 */

\app\assets\ChartJsAsset::register($this);

$this->title = Yii::t('app', 'Statistics');
$this->params['breadcrumbs'][] = $this->title;

?>
    <div class="statistics-index">
        <!-- Таблица сотрудников -->
        <div class="row">
            <div class="col-md-12">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'filterModel' => $searchModel,
                    'tableOptions' => ['class' => 'table table-striped table-hover'],
                    'layout' => '{items}{pager}',
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'headerOptions' => ['class' => 'col-1 text-center'],
                            'contentOptions' => ['class' => 'col-1 text-center'],
                        ],
                        [
                            'attribute' => 'name',
                            'label' => Yii::t('app', 'Name'),
                            'format' => 'raw',
                            'value' => function (\app\models\Coworker $model) {
                                return Html::a(
                                    $model->name,
                                    ['view', 'id' => $model->id],
                                    ['data-pjax' => 0]
                                );
                            },
                        ],
                        [
                            'attribute' => 'email',
                            'label' => Yii::t('app', 'Email'),
                        ],
                        [
                            'attribute' => 'debitAmount',
                            'label' => \Yii::t('app', 'Debit Amount'),
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'col-1 text-center'],
                            'contentOptions' => ['class' => 'col-1 text-center'],
                            'value' => function (\app\models\Coworker $model) {
                                return Html::tag('span', $model->getDebitAmount(date('Y-m-d', strtotime('start month')), date('Y-m-d')), ['class' => 'btn btn-success w-100']);
                            },
                        ],
                        [
                            'attribute' => 'creditAmount',
                            'label' => \Yii::t('app', 'Credit Amount'),
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'col-1 text-center'],
                            'contentOptions' => ['class' => 'col-1 text-center'],
                            'value' => function (\app\models\Coworker $model) {
                                return Html::tag('span', $model->getCreditAmount(date('Y-m-d', strtotime('start month')), date('Y-m-d')), ['class' => 'btn btn-danger w-100']);
                            },
                        ],
                        [
                            'attribute' => 'totalMount',
                            'label' => \Yii::t('app', 'Total Amount'),
                            'format' => 'raw',
                            'headerOptions' => ['class' => 'col-1 text-center'],
                            'contentOptions' => ['class' => 'col-1 text-center'],
                            'value' => function (\app\models\Coworker $model) {
                                return Html::tag('span',
                                    $model->getCreditAmount(date('Y-m-d', strtotime('start month')), date('Y-m-d')) + $model->getDebitAmount(date('Y-m-d', strtotime('start month')), date('Y-m-d')),
                                    ['class' => 'btn btn-secondary w-100']);
                            },
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'template' => '{view}',
                        ],
                    ],
                    'pager' => [
                        'class' => \yii\bootstrap5\LinkPager::class,
                        'options' => ['class' => 'pagination pagination-sm'],
                        'linkOptions' => ['class' => 'page-link'],
                        'activePageCssClass' => 'active',
                        'disabledPageCssClass' => 'disabled',
                    ],
                ]); ?>
            </div>
        </div>
    </div>

<?php
// JavaScript для графика
$js = <<<JS
// Данные для графика
var chartData = {
    labels: [],
    datasets: [
        {
            label: 'Paid Amount',
            backgroundColor: 'rgba(60, 141, 188, 0.9)',
            borderColor: 'rgba(60, 141, 188, 0.8)',
            pointBackgroundColor: 'rgba(60, 141, 188, 1)',
            pointBorderColor: '#fff',
            data: []
        },
        {
            label: 'Unpaid Amount',
            backgroundColor: 'rgba(245, 105, 84, 0.9)',
            borderColor: 'rgba(245, 105, 84, 0.8)',
            pointBackgroundColor: 'rgba(245, 105, 84, 1)',
            pointBorderColor: '#fff',
            data: []
        },
        {
            label: 'Paid Hours',
            backgroundColor: 'rgba(0, 166, 90, 0.9)',
            borderColor: 'rgba(0, 166, 90, 0.8)',
            pointBackgroundColor: 'rgba(0, 166, 90, 1)',
            pointBorderColor: '#fff',
            data: [],
            yAxisID: 'y1'
        }
    ]
};

// Заполняем данные из PHP
JS;

// Если есть данные для графика
if (isset($chartData)) {
    $js .= "chartData.labels = " . json_encode(array_keys($chartData)) . ";\n";
    $js .= "chartData.datasets[0].data = " . json_encode(array_column($chartData, 'paidAmount')) . ";\n";
    $js .= "chartData.datasets[1].data = " . json_encode(array_column($chartData, 'unpaidAmount')) . ";\n";
    $js .= "chartData.datasets[2].data = " . json_encode(array_column($chartData, 'paidHours')) . ";\n";
}

$js .= <<<JS

// Создаем график
var ctx = document.getElementById('chart-container').getContext('2d');
var chart = new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            x: {
                stacked: false,
            },
            y: {
                beginAtZero: true,
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
        },
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false
            }
        }
    }
});

// Обновление графика при изменении фильтров
$('#year, #month').on('change', function() {
    var year = $('#year').val();
    var month = $('#month').val();
    
    if (year && month) {
        $.ajax({
            url: '/statistics/chart-data',
            data: {year: year, month: month},
            success: function(data) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.datasets[0].data;
                chart.data.datasets[1].data = data.datasets[1].data;
                chart.data.datasets[2].data = data.datasets[2].data;
                chart.update();
            }
        });
    }
});
JS;

$this->registerJs($js);
$this->registerCss('
.info-box {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    border-radius: .25rem;
    background: #fff;
    display: flex;
    margin-bottom: 1rem;
    min-height: 80px;
    padding: .5rem;
    position: relative;
}
.info-box .info-box-icon {
    border-radius: .25rem;
    align-items: center;
    display: flex;
    font-size: 1.875rem;
    justify-content: center;
    text-align: center;
    width: 70px;
}
.info-box .info-box-content {
    flex: 1;
    padding: 5px 10px;
}
.info-box .info-box-text, .info-box .progress-description {
    display: block;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.info-box .info-box-number {
    display: block;
    font-weight: 700;
    margin-top: .25rem;
}
.bg-info { background-color: #17a2b8!important; color: #fff; }
.bg-success { background-color: #28a745!important; color: #fff; }
.bg-warning { background-color: #ffc107!important; color: #212529; }
.bg-danger { background-color: #dc3545!important; color: #fff; }
');
?>