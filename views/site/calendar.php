<?php

use yii\web\View;

/**
 * @var $this View
 */
// Подключаем необходимые библиотеки
$this->registerJsFile('/components/timeline/jquery.timeline.min.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile('/components/timeline/jquery.timeline.min.css');
$this->registerJsVar('token', \Yii::$app->user->identity->access_token);

\app\assets\GalleryAsset::register($this);

$this->title = \Yii::t('app', 'Calendar');
$firstDayOfMonth = (new DateTime('-2 months'))->format('Y-m-d 00:00:00');

$this->registerJs(<<<JS
let timelineContainer;
let allCoworkersData = []; // Глобальная переменная для хранения данных

const newDateString = () => {
    const now = new Date();
    const futureDate = new Date(now.getFullYear(), now.getMonth() + 2, now.getDate());
    const year = futureDate.getFullYear();
    const month = String(futureDate.getMonth() + 1).padStart(2, '0');
    const day = String(futureDate.getDate()).padStart(2, '0');
    return `\${year}-\${month}-\${day} 23:59`;
}

// Функция для открытия модального окна с одним заказом
const openSingleOrderModal = (order_id) => {
    $('#event-modal').modal('show').find('#event-modal-content').load(`/order/view?id=\${order_id}`)
}

// Функция инициализации плагина
const initializeTimeline = (coworkers) => {
    allCoworkersData = coworkers; // Сохраняем данные для повторного использования
    let finalEvents = [];
    let sidebarItems = [];

    coworkers.forEach((coworker, coworkerIndex) => {
        const coworkerOrders = coworker.active_orders
        
        // Все заказы сотрудника на одной строке
        coworkerOrders.forEach(order => {
            finalEvents.push({
                start: order.start_datetime,
                end: order.finish_datetime,
                row: coworkerIndex + 1,
                label: `<div class='event text-center'><span class='title text-white'>\${order.title || 'Заказ #' + order.id}</span><span class='price text-white'>\${formatter.format(order.price)}</span></div>`,
                bgColor: order.mode === 0 ? '#28A745' : order.mode === 1 ? '#007BFF' : '#6C757D',
                extend: order
            });
        });
        
        // Добавляем имя сотрудника в sidebar
        sidebarItems.push(`<span data-key="\${coworker.id}" class="worker-row px-2">\${coworker.name}</span>`);
    });
            
    // Создаем Timeline
    const options = {
        type: "mixed",
        scale: "days",
        startDatetime: "{$firstDayOfMonth}",
        endDatetime: newDateString(),
        autoScale: true,
        locale: "ru-RU",
        headline: {
            display: false
        },
        minGridSize: 120,
        sidebar: {
            list: sidebarItems,
            sticky: true,
            position: "left"
        },
        eventData: finalEvents,
        ruler: {
            top: {
                lines: ["year", "month", "day", "weekday"],
                format: {
                    timeZone: "Asia/Tokyo",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    weekday: "short"
                },
                locale: "ru"
            }
        },
        format: {
            time: { hour: "numeric", minute: "numeric" },
            full: { year: "numeric", month: "long", day: "numeric" },
            month: "long",
            day: "numeric",
            weekday: "short"
        },
        eventMeta: {
            display: false
        },
        rows: "auto",
        scrollSensitivity: 1
    };
          
    timelineContainer = $("#timeline")
        .Timeline(options)
        .Timeline('openEvent', (event, timelineEvent) => {
        let order_id;
        if (Object.hasOwn(event.extend, 'debit')) {
            order_id = event.extend.order_id;
        } else {
            order_id = event.extend.id;
        }
        openSingleOrderModal(order_id);
    })
        .Timeline('alignment', 'currently')
        .Timeline('dateback', {
            scale: "day",
            range: 5,
            shift: true
        })
};

const coworkersPromise = $.ajax("/coworker/list", {
    headers: {
        Authorization: `Bearer \${token}`
    }
});

const formatter = new Intl.NumberFormat('ru-RU',{
    style: 'currency',
    currency: 'RUB'
});

$.when(coworkersPromise)
    .done(function(coworkers) {
        initializeTimeline(coworkers);
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Error loading data:', textStatus, errorThrown);
        $("#timeline").html('<div class="alert alert-danger">Ошибка загрузки данных. Пожалуйста, обновите страницу.</div>');
    });

    $('#fin').on('change',function() {
        if ($(this).is(":checked")) {
            $('.event .title').hide()
            $('.event .price').show()
        } else {
            $('.event .price').hide()
            $('.event .title').show()
        }
    })
JS, \yii\web\View::POS_READY);


$this->registerCss(<<<CSS
/* Стили для событий */
.jqtl-event-node {
    justify-content: center;
    border-radius: 14px;
    min-height: 40px;
    margin-top: 5px;
}
.jqtl-event-node[data-is_payed="0"]::before {
    font-family: "Font Awesome 5 Free", serif;
    color: red;
    content: "\\f00d";
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: 0;
    right: 0;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    border: 1px solid #ccc;
    background-color: white;
    font-weight: 900;
    font-size: 10px;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.jqtl-event-node[data-is_payed="1"]::before {
    font-family: "Font Awesome 5 Free", serif;
    color: green;
    content: "\\f00c";
    display: flex;
    justify-content: center;
    align-items: center;
    position: absolute;
    top: 0;
    right: 0;
    height: 20px;
    width: 20px;
    border-radius: 50%;
    border: 1px solid #ccc;
    background-color: white;
    font-weight: 900;
    font-size: 10px;
    z-index: 10;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}
.jqtl-event-label {
    width: 100%;
}
.event {
    margin: 0 auto;
    padding: 5px 10px;
    border-radius: 8px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.event > .price {
    display: none;
    font-size: 12px;
    font-weight: bold;
}
.event > .title {
    font-size: 12px;
    font-weight: bold;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
CSS
);

\yii\bootstrap5\Modal::begin([
    'id' => 'event-modal',
    'size' => 'modal-xl'
]);
echo \yii\helpers\Html::tag('div', '', ['id' => 'event-modal-content']);
\yii\bootstrap5\Modal::end();

?>
<div class="row">
    <div class="form-check form-switch mx-5">
        <input class="form-check-input" type="checkbox" role="switch" id="fin">
        <label class="form-check-label" for="fin"><?= \Yii::t('app', 'Finance') ?></label>
    </div>
    <div id="timeline"></div>
</div>