<?php

use app\models\Hours;
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * @var int $month
 * @var int $year
 * @var array $employees
 * @var array $buildings
 * @var string $currentDate
 */

$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
$monthNames = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
    5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
    9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
];

$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}
?>

<style>
    .schedule-table { width: max-content; min-width: 100%; border-collapse: collapse; }
    .schedule-table th, .schedule-table td { border: 1px solid #e0e0e0; padding: 6px; min-width: 120px; vertical-align: top; }
    .employee-col { position: sticky; left: 0; background: #f8f9fa; z-index: 2; min-width: 220px; }
    .day-header { position: sticky; top: 0; background: #f5f5f5; z-index: 3; text-align: center; }
    .day-cell { height: 90px; cursor: pointer; }
    .day-cell.today { background: #e8f4fd; }
    .badge { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 11px; margin-right: 4px; }
    .badge.work { background: #e8f5e9; color: #2e7d32; }
    .badge.lunch { background: #fff3cd; color: #856404; }
    .badge.sick { background: #f8d7da; color: #721c24; }
    .badge.dayoff { background: #e2e3e5; color: #383d41; }
    .schedule-entry { margin-bottom: 4px; font-size: 12px; }
    .schedule-entry .meta { color: #6c757d; font-size: 11px; }
    .controls { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .scroll-container { overflow: auto; border: 1px solid #ddd; border-radius: 4px; }
    .btn-xs { padding: 2px 6px; font-size: 11px; }
</style>

<div class="controls">
    <div class="h5 mb-0"><?= Html::encode($monthNames[$month] . ' ' . $year) ?></div>
    <div>
        <?= Html::a('←', ['schedule/index', 'month' => $prevMonth, 'year' => $prevYear], ['class' => 'btn btn-default btn-sm']) ?>
        <?= Html::a('→', ['schedule/index', 'month' => $nextMonth, 'year' => $nextYear], ['class' => 'btn btn-default btn-sm']) ?>
    </div>
</div>

<div class="scroll-container" id="schedule-scroll">
    <table class="schedule-table">
        <thead>
        <tr>
            <th class="employee-col">Сотрудник</th>
            <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                <?php
                $dayOfWeek = date('N', mktime(0, 0, 0, $month, $day, $year));
                $isToday = ($year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day)) === $currentDate;
                $classes = 'day-header' . ($isToday ? ' today' : '');
                ?>
                <th class="<?= $classes ?>" data-day="<?= $day ?>">
                    <?= $weekDays[$dayOfWeek - 1] . ', ' . $day ?>
                </th>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($employees as $employee): ?>
            <tr>
                <td class="employee-col" data-employee-id="<?= $employee->id ?>">
                    <div><strong><?= Html::encode($employee->getName()) ?></strong></div>
                    <div class="text-muted" style="font-size:12px;">ID: <?= $employee->id ?></div>
                </td>
                <?php for ($day = 1; $day <= $daysInMonth; $day++): ?>
                    <?php
                    $dateStr = $year . '-' . sprintf('%02d', $month) . '-' . sprintf('%02d', $day);
                    $isToday = $dateStr === $currentDate;
                    ?>
                    <td class="day-cell<?= $isToday ? ' today' : '' ?>"
                        data-employee-id="<?= $employee->id ?>"
                        data-date="<?= $dateStr ?>">
                        <div class="cell-content" data-employee-id="<?= $employee->id ?>" data-date="<?= $dateStr ?>"></div>
                        <div class="text-right">
                            <button class="btn btn-primary btn-xs js-add-entry" data-employee-id="<?= $employee->id ?>" data-date="<?= $dateStr ?>">+</button>
                        </div>
                    </td>
                <?php endfor; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="schedule-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Редактировать день</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <form id="schedule-form">
                    <input type="hidden" name="id" id="schedule-id">
                    <input type="hidden" name="user_id" id="schedule-user">
                    <input type="hidden" name="date" id="schedule-date">

                    <div class="form-group">
                        <label for="schedule-type">Тип записи</label>
                        <select class="form-control" name="type" id="schedule-type" required>
                            <option value="<?= Hours::TYPE_WORK ?>">Работа</option>
                            <option value="<?= Hours::TYPE_LUNCH ?>">Обед</option>
                            <option value="<?= Hours::TYPE_SICK ?>">Больничный</option>
                            <option value="<?= Hours::TYPE_DAY_OFF ?>">Выходной</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="schedule-building">Филиал</label>
                        <select class="form-control" name="building_id" id="schedule-building">
                            <option value="">Не выбран</option>
                            <?php foreach ($buildings as $building): ?>
                                <option value="<?= $building->id ?>"><?= Html::encode($building->title) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="schedule-order">Заказ (опционально)</label>
                        <input type="number" class="form-control" name="order_id" id="schedule-order" placeholder="ID заказа">
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label for="schedule-start">Начало</label>
                            <input type="datetime-local" class="form-control" name="start_time" id="schedule-start">
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="schedule-stop">Конец</label>
                            <input type="datetime-local" class="form-control" name="stop_time" id="schedule-stop">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="schedule-count">Часы</label>
                        <input type="number" step="0.25" min="0" class="form-control" name="count" id="schedule-count" value="0">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="schedule-delete" style="display:none;">Удалить</button>
                <button type="button" class="btn btn-primary" id="schedule-save">Сохранить</button>
            </div>
        </div>
    </div>
</div>

<?php
$saveUrl = Url::to(['schedule/save']);
$deleteUrl = Url::to(['schedule/delete']);
$monthUrl = Url::to(['schedule/get-month-schedule', 'month' => $month, 'year' => $year]);
?>

<script>
    const TYPE_WORK = <?= Hours::TYPE_WORK ?>;
    const TYPE_LUNCH = <?= Hours::TYPE_LUNCH ?>;
    const TYPE_SICK = <?= Hours::TYPE_SICK ?>;
    const TYPE_DAY_OFF = <?= Hours::TYPE_DAY_OFF ?>;

    const typeLabels = {
        [TYPE_WORK]: {text: 'Работа', cls: 'badge work'},
        [TYPE_LUNCH]: {text: 'Обед', cls: 'badge lunch'},
        [TYPE_SICK]: {text: 'Больничный', cls: 'badge sick'},
        [TYPE_DAY_OFF]: {text: 'Выходной', cls: 'badge dayoff'},
    };

    function renderSchedule(data) {
        $('.cell-content').empty();
        Object.keys(data).forEach(userId => {
            const days = data[userId];
            Object.keys(days).forEach(date => {
                const cell = $('.cell-content[data-employee-id="'+userId+'"][data-date="'+date+'"]');
                if (!cell.length) return;
                days[date].forEach(entry => {
                    const label = typeLabels[entry.type] || {text: 'Неизвестно', cls: 'badge'};
                    const time = (entry.start_time && entry.stop_time)
                        ? new Date(entry.start_time).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) + ' - ' +
                          new Date(entry.stop_time).toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})
                        : (entry.count ? entry.count + ' ч.' : '');
                    const building = entry.building ? (entry.building.title || ('ID ' + entry.building.id)) : '';
                    const order = entry.order_id ? 'Заказ #' + entry.order_id : '';
                    const info = [time, building, order].filter(Boolean).join(' · ');
                    const div = $('<div class="schedule-entry" />')
                        .attr('data-id', entry.id)
                        .append('<span class="'+label.cls+'">'+label.text+'</span> ')
                        .append('<span class="meta">'+info+'</span>');
                    cell.append(div);
                });
            });
        });
    }

    function loadMonth() {
        $.get('<?= $monthUrl ?>').done(function(resp) {
            if (resp.success) {
                renderSchedule(resp.data);
            }
        });
    }

    function openModal(userId, date, entry) {
        $('#schedule-id').val(entry ? entry.id : '');
        $('#schedule-user').val(userId);
        $('#schedule-date').val(date);
        $('#schedule-type').val(entry ? entry.type : TYPE_WORK);
        $('#schedule-building').val(entry ? entry.building_id : '');
        $('#schedule-order').val(entry ? entry.order_id : '');
        $('#schedule-start').val(entry && entry.start_time ? entry.start_time.replace(' ', 'T') : '');
        $('#schedule-stop').val(entry && entry.stop_time ? entry.stop_time.replace(' ', 'T') : '');
        $('#schedule-count').val(entry ? entry.count : 0);
        $('#schedule-delete').toggle(!!entry);
        $('#schedule-modal').modal('show');
    }

    $(document).ready(function() {
        loadMonth();

        // открыть создание
        $('.js-add-entry').on('click', function(e) {
            e.stopPropagation();
            const userId = $(this).data('employee-id');
            const date = $(this).data('date');
            openModal(userId, date, null);
        });

        // редактирование по клику на запись
        $(document).on('click', '.schedule-entry', function(e) {
            e.stopPropagation();
            const id = $(this).data('id');
            const cell = $(this).closest('.cell-content');
            const userId = cell.data('employee-id');
            const date = cell.data('date');
            // Для простоты: загрузим месяц и найдём запись
            $.get('<?= $monthUrl ?>').done(resp => {
                if (!resp.success) return;
                const entries = resp.data?.[userId]?.[date] || [];
                const entry = entries.find(i => i.id == id);
                if (entry) openModal(userId, date, entry);
            });
        });

        // Сохранить
        $('#schedule-save').on('click', function() {
            const formData = $('#schedule-form').serialize();
            $.post('<?= $saveUrl ?>', formData).done(resp => {
                if (resp.success) {
                    $('#schedule-modal').modal('hide');
                    loadMonth();
                } else {
                    alert(resp.message || 'Ошибка');
                    console.error(resp.errors);
                }
            });
        });

        // Удалить
        $('#schedule-delete').on('click', function() {
            const id = $('#schedule-id').val();
            if (!id) return;
            $.post('<?= $deleteUrl ?>', {id}).done(resp => {
                if (resp.success) {
                    $('#schedule-modal').modal('hide');
                    loadMonth();
                } else {
                    alert(resp.message || 'Ошибка удаления');
                }
            });
        });
    });
</script>

