<?php

use yii\web\View;

/**
 * @var $this View
 */

$this->registerJsFile('/components/timeline/jquery.timeline.min.js', ['depends' => 'yii\web\JqueryAsset']);
$this->registerCssFile('/components/timeline/jquery.timeline.min.css');
$this->registerJsVar('token', \Yii::$app->user->identity->access_token);

\app\assets\GalleryAsset::register($this);

$this->title = \Yii::t('app', 'Calendar');

$current = isset($_GET['date']) ? strtotime($_GET['date']) : time();

$firstDate = date('Y-m-06', $current);
$lastDate = date('Y-m-05', strtotime('+1 month', $current));

$this->registerJsVar('firstDate', $firstDate);
$this->registerJsVar('lastDate', $lastDate);
$this->registerJs(<<<JS
let timelineContainer;
let allCoworkersData = []; // Глобальная переменная для хранения данных
let events = []; // Глобальная переменная для хранения событий

// Функция для открытия модального окна с одним заказом
const openSingleOrderModal = (order_id) => {
    $('#event-modal').modal('show').find('#event-modal-content').load(`/order/view?id=\${order_id}`, () => {
        const titleElement = $(this).find('.order-title');
        let title = 'Просмотр заказа';
        
        if (titleElement.length) {
            title = titleElement.first().text();
            // Опционально: удалить заголовок из контента, если он там есть
            titleElement.first().remove();
        }
        $('#event-modal .modal-title').text(title);
    })
}

const buildEvent = (order, coworker_id, coworkerIndex) => {
    let finish, content, color, id;
    if (order.mode !== 2) {
        if (order.mode === 0) {
            finish = order.finish_datetime !== null ? DateUtils.formatDate(new Date(order.finish_datetime), 'YYYY-MM-DD 23:59:59') : DateUtils.formatDate(new Date(), 'YYYY-MM-DD 23:59:59');
        } else {
            finish = order.finish_datetime !== null ? order.finish_datetime : DateUtils.formatDate(new Date(), 'YYYY-MM-DD 23:59:59');
        }
        content = `<div class='event text-center'>
<span class='title text-white'>\${'Заказ #' + order.id} \${order.title ? `(\${order.title})` : ""}</span>
<span class='price text-white justify-content-center'>\${formatter.format(order.price)}</span>
</div>`;
        id = `order-\${order.id}-\${coworker_id}`;
        color = order.mode === 1 ? '#28A745' : '#6C757D';
    } else {
        finish = order.finish_datetime !== null ? order.finish_datetime : DateUtils.formatDate(new Date(), 'YYYY-MM-DD 23:59:59');
        content = `<div class='event text-center'>
<span class='title text-white'>\${'Заказ #' + order.id} \${order.title ? `(\${order.title})` : ""}</span>
<div class='justify-content-center price'>
<span class='text-white'>\${formatter.format(order.price[coworker_id].debit)} / \${formatter.format(order.price[coworker_id].credit)} / \${formatter.format(order.price[coworker_id].total)}</span>
</div></div>`;
        order.is_payed = order.price[coworker_id].debit === order.price[coworker_id].total ? 1 : 0;
        id = `order-\${order.id}-\${coworker_id}`
        color = '#007BFF';
    }
    return{
        id: order.id,
        eventID: id,
        start: order.start_datetime,
        end: finish,
        row: coworkerIndex + 1,
        label: content,
        bgColor: color,
        callback: (a, b, c) => {
            console.log("Callback")
            console.log(a, b, c)
        },
        extend: order
    };
}

// Функция инициализации плагина
const initializeTimeline = (coworkers) => {
    allCoworkersData = coworkers; // Сохраняем данные для повторного использования
    let sidebarItems = [];

    coworkers.forEach((coworker, coworkerIndex) => {
        const coworkerOrders = coworker.active_orders
        // Все заказы сотрудника на одной строке
        coworkerOrders.forEach(order => events.push(buildEvent(order, coworker.id, coworkerIndex)));
        
        // Добавляем имя сотрудника в sidebar
        sidebarItems.push(`<span data-key="\${coworker.id}" class="worker-row px-2">\${coworker.name}</span>`);
    });

    // Создаем Timeline
    const options = {
        type: "mixed",
        scale: "days",
        startDatetime: new Date( firstDate ),
        endDatetime: new Date( lastDate ),
        autoScale: true,
        locale: "ru-RU",
        headline: {
            display: false
        },
        minGridSize: 60,
        sidebar: {
            list: sidebarItems,
            sticky: true,
            position: "left"
        },
        eventData: events,
        ruler: {
            top: {
                lines: ["day", "weekday"],
                format: {
                    timeZone: "Asia/Tokyo",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    weekday: "short"
                },
                locale: "ru"
            },
            bottom: {
                lines: ["weekday", "day"],
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
            display: true
        },
        effects: {
            sticky: true,
            hoverEvent: false
        },
        rows: "auto",
        scrollSensitivity: 1
    };
    console.log( events )
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
        .Timeline('alignment', 'now')
        .Timeline('dateback', {
            scale: "day",
            range: 5,
            shift: true
        })
};

const coworkersPromise = $.ajax(`/coworker/list?month=\${DateUtils.formatDate(new Date(firstDate), "MM")}&year=\${DateUtils.formatDate(new Date(firstDate), "YYYY")}`, {
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

// Функция для обновления только данных событий
const updateTimelineEvents = () => {
    $.ajax({
        url: `/coworker/list?month=\${DateUtils.formatDate(new Date(firstDate), "MM")}&year=\${DateUtils.formatDate(new Date(firstDate), "YYYY")}`,
        method: "GET",
        headers: {
            Authorization: `Bearer \${token}`
        },
        success: function(coworkers) {
            // Собираем новые события
            let updates = [];
            coworkers.forEach((coworker, coworkerIndex) => {
                const coworkerOrders = coworker.active_orders
                coworkerOrders.forEach(order => {
                    const t = buildEvent(order, coworker.id, coworkerIndex)
                    events.map(event => {
                        return event.eventID === t.eventID ? t : event
                    })
                    updates.push(t);
                    
                });
            });
            timelineContainer.Timeline('addEvent', updates)
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error updating timeline events:', textStatus, errorThrown);
        }
    });
};

// Используем эту функцию вместо refreshTimelineData
const REFRESH_INTERVAL = 10000; // 30 секунд
setInterval(updateTimelineEvents, REFRESH_INTERVAL);

// $('#fin').on('change',function() {
//     if ($(this).is(":checked")) {
//         $('.event .title').hide()
//         $('.event .price').toggleClass(["d-flex", "d-none"])
//     } else {
//         $('.event .price').toggleClass(["d-flex", "d-none"])
//         $('.event .title').show()
//     }
// })
    
// В calendar.php добавьте этот код в существующий блок JS
$(document).on('change', '.hour-payed-switch', function() {
    $.ajax({
        'url': '/coworker/set-hours',
        'type': 'POST',
        'data': {
            'user_id': $(this).attr('data-user_id'),
            'order_id': $(this).attr('data-order_id'),
            'date': $(this).attr('data-date'),
            'is_payed': $(this).prop('checked') ? 1 : 0
        },
        'success': function(data) {
            console.log(data);
        }
    })
});

$(document).on('change', '.order-payed-switch', function() {
    $.ajax({
        'url': '/order/set-payed',
        'type': 'POST',
        'data': {
            'id': $(this).attr('data-id'),
            'is_payed': $(this).prop('checked') ? 1 : 0
        },
        'success': function(data) {
            console.log(data);
        }
    })
});

$(".modal").on("hidden.bs.modal", updateTimelineEvents)

// В calendar.php убедитесь, что onDateSelect правильно обрабатывается
const monthYearPicker = new Calendar('#month-year-selector', {
    mode: 'month-year',
    initialDate: new Date(firstDate),
    allowPastDates: true,
    positionSelector: '#date-container',
    onDateSelect: (date) => {
        console.log('onDateSelect triggered:', date);
        if (DateUtils.getMonthsNames()[date.getMonth()] !== DateUtils.getMonthsNames()[(new Date(firstDate)).getMonth()]) {
            window.location.href = '/site/calendar?date=' + DateUtils.formatDate(date, 'YYYY-MM-DD');
        }
    }
});

$("#date-container").on("click", () => {
    console.log('Date container clicked, showing picker');
    monthYearPicker.toggle(); // Используем toggle вместо show
});

JS, \yii\web\View::POS_READY);

$this->registerCss(<<<CSS
#month-year-selector {
z-index: 99999;
}
.calendar-container {
    position: fixed;
    background: white;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    z-index: 10000;
}

.calendar-month-year-container {
    padding: 10px;
    min-width: 200px;
}

.calendar-month-year-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 5px;
    margin-top: 10px;
}
/* Стили для событий */
.jqtl-event-node {
    justify-content: center;
    border-radius: 5px;
    min-height: 40px;
    transition: all 0.3s ease-in-out;
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
    border-radius: 5px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.worker-row.px-2 {
    user-select: none;
}
.table-striped tbody tr:nth-of-type(odd) {
    background-color: transparent !important;
}
.table-striped tbody tr:nth-of-type(even) {
    background-color: transparent !important;
}
.table-striped {
    background-color: transparent !important;
}
.table {
    background-color: transparent !important;
}
.table-info, 
.table-info>td, 
.table-info>th {
    background: none !important;
    background-color: transparent !important;
    --bs-table-bg-type: transparent !important;
    --bs-table-accent-bg: transparent !important;
    box-shadow: none !important;
}

.table-striped tbody tr:nth-of-type(odd) {
    --bs-table-bg-type: transparent !important;
    --bs-table-accent-bg: transparent !important;
    box-shadow: none !important;
}

.table-striped tbody tr:nth-of-type(even) {
    --bs-table-bg-type: transparent !important;
    --bs-table-accent-bg: transparent !important;
    box-shadow: none !important;
}

/* Или более простой вариант - для всех строк таблицы */
.table-striped tbody tr {
    --bs-table-bg-type: transparent !important;
    --bs-table-accent-bg: transparent !important;
    box-shadow: none !important;
}

/* Также для самой таблицы */
.table-striped {
    --bs-table-bg-type: transparent !important;
    --bs-table-accent-bg: transparent !important;
}
.calendar-container {
display: none;
}
#date-container {
cursor: pointer;
}
CSS
);

\yii\bootstrap4\Modal::begin([
    'id' => 'event-modal',
    'size' => 'modal-xl',
    'title' => '',
]);
echo \yii\helpers\Html::tag('div', '', ['id' => 'event-modal-content']);
\yii\bootstrap4\Modal::end();
?>
<div class="row">
    <div class="row">
        <div class="col-4">
            <div class="btn btn-primary h-2 w-100 event text-center">
                <span class="title text-white"><?= \Yii::t('app', 'mode_long_daily') ?></span>
                <div class='justify-content-center price'>
                    <span class='text-white'><?= \Yii::t('app', 'Debit Amount') ?> / <?= \Yii::t('app', 'Credit Amount') ?> / <?= \Yii::t('app', 'Total Amount') ?></span>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="btn btn-success h-2 w-100 event text-center">
                <span class="title text-white"><?= \Yii::t('app', 'mode_long_fixed') ?></span>
                <div class='justify-content-center price'><?= Yii::t('app', 'Price') ?></div>
            </div>
        </div>
        <div class="col-4">
            <div class="btn btn-secondary h-2 w-100 event text-center">
                <span class="title text-white"><?= \Yii::t('app', 'mode_single_fixed') ?></span>
                <div class='justify-content-center price'><?= Yii::t('app', 'Price') ?></div>
            </div>
        </div>
    </div>
    <div class="row my-3 px-5">
        <div class="d-flex justify-content-between">
            <a href="?date=<?= date('Y-m-06', strtotime('-1 month', strtotime($firstDate))) ?>" class="btn btn-primary align-middle"><?= \Yii::t('app', 'Previous') ?></a>
            <div class="col-10 d-flex flex-column position-relative" id="date-container">
                <div id="month-year-selector" class="justify-content-between position-absolute top-0 start-middle"></div>
                <span id="year" class="text-center"><?= \Yii::$app->formatter->asDate($firstDate, 'php:Y') ?></span>
                <span id="month" class="text-center text-capitalize"><?= \Yii::$app->formatter->asDate($firstDate, 'LLLL') ?></span>
            </div>
            <a href="?date=<?= date('Y-m-06', strtotime('+1 month', strtotime($firstDate))) ?>" class="btn btn-primary col-1"><?= \Yii::t('app', 'Next') ?></a>
        </div>
    </div>
    <div id="timeline"></div>
</div>