// @app/web/js/calendar-navigation.js

$(document).ready(function() {
    $('.calendar-nav-button').on('click', function(e) {
        e.preventDefault();

        let month = $(this).data('month');
        let year = $(this).data('year');

        // Отправляем AJAX запрос для обновления календаря
        $.ajax({
            url: window.location.pathname,
            type: 'GET',
            data: {
                month: month,
                year: year
            },
            success: function(response) {
                // Обновляем содержимое календаря
                $('.horizontal-calendar-widget').replaceWith(response);
                // Инициализируем обработчики событий для нового контента
                initCalendarEvents();
            },
            error: function() {
                alert('Ошибка при загрузке календаря');
            }
        });
    });

    function initCalendarEvents() {
        // Инициализация кликов по ячейкам
        $('.day-cell').on('click', function() {
            console.log($(this));
            var employeeId = $(this).data('employee-id');
            var date = $(this).data('date');

            // Можно открыть модальное окно для добавления часов или редактирования
            if (!$(this).hasClass('merged-cell')) {
                openDayModal(employeeId, date);
            }
        });

        // Инициализация подсказок
        $('[title]').tooltip();
    }

    function openDayModal(employeeId, date) {
        console.log(employeeId, date)
    }

    // Инициализация при загрузке
    initCalendarEvents();
});