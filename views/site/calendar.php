<?php


/**
 * @var \yii\web\View $this
 */

$this->title = \Yii::t('app', 'Calendar');
$resources = [];
$coworkers = app\models\Coworker::find()->where(['created_by' => \Yii::$app->user->getId()])->all();
$orders = app\models\Order::find()->where(['created_by' => \Yii::$app->user->getId()])->all();
$hours = app\models\Hours::find()
    ->where(['IN', 'coworker_id', \yii\helpers\ArrayHelper::map($coworkers, 'id', 'id')])
    ->andWhere(['IN', 'order_id', \yii\helpers\ArrayHelper::map($orders, 'id', 'id')])
    ->all();

foreach ($hours as $index => $hour) {
    $resources[] = [
        'id' => $index + 1,
        'title' => $hour->coworker->firstname.' '.$hour->coworker->lastname,
        'start' => $hour->date,
        'count' => $hour->count,
    ];
    echo "<p>";
    var_dump($hour->attributes);
    echo "</p>";
}

$this->registerJsVar('resources', $resources);
$this->registerJsFile("//cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js");
$this->registerJs(<<<JS
    const handleEventClick = (info) => {
        console.log(info.event.count);
    }
    const calendarEl = document.getElementById('calendar');
    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        events: resources,
        eventClick: handleEventClick,
    });
    calendar.render();
JS, \yii\web\View::POS_READY);
echo "<div id='calendar' style='width: 50%'></div><div style='width: 50%'><canvas id='myChart'></canvas></div>";

$this->registerJsFile("//cdn.jsdelivr.net/npm/chart.js");
$this->registerJs(<<<JS
  const ctx = document.getElementById('myChart');

  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple', 'Orange'],
      datasets: [{
        label: '# of Votes',
        data: [12, 19, 3, 5, 2, 3],
        borderWidth: 1
      }]
    },
    options: {
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  });
JS);