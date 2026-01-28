/**
 * Компонент календаря для выбора даты
 *
 * @class Calendar
 * @description Представляет собой интерактивный календарь для выбора дат с поддержкой навигации по месяцам,
 * ограничением по минимальной/максимальной дате и кастомизацией отображения.
 *
 * @example
 * // Базовое использование
 * const calendar = new Calendar('#calendar-container', {
 *     initialDate: new Date(),
 *     onDateSelect: (date) => console.log('Выбрана дата:', date)
 * });
 *
 * @example
 * // Расширенное использование
 * const calendar = new Calendar('#calendar-container', {
 *     initialDate: new Date('2024-03-15'),
 *     minDate: new Date('2024-01-01'),
 *     maxDate: new Date('2024-12-31'),
 *     onDateSelect: (date) => {
 *         console.log('Выбрана дата:', DateUtils.formatDate(date));
 *     },
 *     showOtherMonthsDays: true,
 *     showNavigation: true,
 *     allowPastDates: false,
 *     locale: 'ru-RU',
 *     startWeekOnMonday: true,
 *     monthsNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
 *     weekdaysNames: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
 * });
 *
 * // Изменение даты программно
 * calendar.setDate(new Date('2024-05-20'));
 *
 * // Получение текущей даты
 * const selectedDate = calendar.getDate();
 *
 * // Скрытие/показа календаря
 * calendar.hide();
 * calendar.show();
 * calendar.toggle();
 */
class Calendar {
    /**
     * Создает экземпляр календаря
     *
     * @constructor
     * @param {string|HTMLElement} container - Селектор CSS или DOM-элемент для размещения календаря
     * @param {Object} [options={}] - Опции конфигурации календаря
     * @param {Date|string} [options.initialDate=new Date()] - Начальная дата для отображения
     * @param {Date|string} [options.minDate=new Date()] - Минимальная доступная дата
     * @param {Date|string} [options.maxDate=null] - Максимальная доступная дата (null - без ограничений)
     * @param {Function} [options.onDateSelect=null] - Callback-функция, вызываемая при выборе даты
     * @param {string[]} [options.monthsNames=DateUtils.getMonthsNames()] - Массив названий месяцев
     * @param {string[]} [options.weekdaysNames=DateUtils.getWeekdaysNames()] - Массив сокращенных названий дней недели
     * @param {boolean} [options.showOtherMonthsDays=true] - Показывать дни других месяцев в сетке
     * @param {boolean} [options.showNavigation=true] - Показывать навигацию по месяцам
     * @param {boolean} [options.allowPastDates=false] - Разрешать выбор прошедших дат
     * @param {string} [options.locale='ru-RU'] - Локаль для отображения
     * @param {boolean} [options.startWeekOnMonday=true] - Начинать неделю с понедельника
     *
     * @throws {Error} Если контейнер не найден или не является валидным элементом
     */
    constructor(container, options = {}) {
        // Если container - это строка (селектор), получаем элемент
        if (typeof container === 'string') {
            this.container = document.querySelector(container);
        } else {
            this.container = container;
        }

        // Проверяем наличие контейнера
        if (!this.container) {
            throw new Error('Контейнер для календаря не найден');
        }

        // Устанавливаем значения по умолчанию
        this.options = {
            initialDate: new Date(),
            minDate: undefined,
            maxDate: null,
            onDateSelect: null,
            monthsNames: DateUtils.getMonthsNames(),
            weekdaysNames: DateUtils.getWeekdaysNames(),
            showOtherMonthsDays: true,
            showNavigation: true,
            allowPastDates: false,
            locale: 'ru-RU',
            startWeekOnMonday: true,
            ...options
        };

        // Инициализируем даты с проверкой на null
        this.currentDate = this.options.initialDate ?
            DateUtils.cloneDate(this.options.initialDate) : new Date();

        this.minDate = this.options.minDate ?
            DateUtils.cloneDate(this.options.minDate) : null;

        this.maxDate = this.options.maxDate ?
            DateUtils.cloneDate(this.options.maxDate) : null;

        // Если не разрешены прошедшие даты и minDate не установлен, устанавливаем минимальную дату как сегодня
        if (!this.options.allowPastDates && !this.minDate) {
            this.minDate = DateUtils.getToday();
        }

        // Для навигации по месяцам
        this.currentYear = this.currentDate.getFullYear();
        this.currentMonth = this.currentDate.getMonth();

        // Названия месяцев и дней недели
        this.MONTHS = this.options.monthsNames;
        this.WEEKDAYS = this.options.weekdaysNames;

        this.init();
        this.render();
    }

    /**
     * Инициализирует DOM-структуру календаря
     * @private
     */
    init() {
        // Очищаем контейнер
        this.container.innerHTML = '';

        this.calendarRoot = document.createElement('div');
        this.calendarRoot.className = 'calendar-container';
        this.container.appendChild(this.calendarRoot);
    }

    /**
     * Отрисовывает календарь
     * @private
     */
    render() {
        this.calendarRoot.innerHTML = "";

        // Заголовок календаря (месяц и навигация)
        if (this.options.showNavigation) {
            const header = this.createHeader();
            this.calendarRoot.appendChild(header);
        }

        // Дни недели
        const weekdaysRow = this.createWeekdays();
        this.calendarRoot.appendChild(weekdaysRow);

        // Сетка дней
        const daysGrid = this.createDaysGrid();
        this.calendarRoot.appendChild(daysGrid);
    }

    /**
     * Создает заголовок календаря с навигацией
     * @private
     * @returns {HTMLElement} Элемент заголовка
     */
    createHeader() {
        const header = document.createElement("div");
        header.className = "calendar-header";

        const prevMonthIndex = (this.currentMonth - 1 + 12) % 12;
        const nextMonthIndex = (this.currentMonth + 1) % 12;

        // Предыдущий месяц
        const prevSpan = document.createElement("span");
        prevSpan.className = "calendar-header-muted calendar-header-month calendar-header-month-prev";
        prevSpan.textContent = this.MONTHS[prevMonthIndex];

        // Текущий месяц
        const centerSpan = document.createElement("span");
        centerSpan.className = "calendar-header-main calendar-header-month calendar-header-month-current";
        centerSpan.textContent = `${this.MONTHS[this.currentMonth]} ${this.currentYear}`;

        // Следующий месяц
        const nextSpan = document.createElement("span");
        nextSpan.className = "calendar-header-muted calendar-header-month calendar-header-month-next";
        nextSpan.textContent = this.MONTHS[nextMonthIndex];

        // Проверка, можно ли перейти к предыдущему месяцу
        const isPrevDisabled = this.isPrevMonthDisabled();
        if (isPrevDisabled) {
            prevSpan.classList.add("calendar-header-month-disabled");
        } else {
            prevSpan.addEventListener("click", () => {
                this.navigateMonth(-1);
            });
        }

        // Проверка, можно ли перейти к следующему месяцу
        const isNextDisabled = this.isNextMonthDisabled();
        if (isNextDisabled) {
            nextSpan.classList.add("calendar-header-month-disabled");
        } else {
            nextSpan.addEventListener("click", () => {
                this.navigateMonth(1);
            });
        }

        header.appendChild(prevSpan);
        header.appendChild(centerSpan);
        header.appendChild(nextSpan);

        return header;
    }

    /**
     * Создает строку с днями недели
     * @private
     * @returns {HTMLElement} Элемент с днями недели
     */
    createWeekdays() {
        const weekdaysRow = document.createElement("div");
        weekdaysRow.className = "calendar-weekdays";

        this.WEEKDAYS.forEach(day => {
            const el = document.createElement("div");
            el.className = "calendar-weekday";
            el.textContent = day;
            weekdaysRow.appendChild(el);
        });

        return weekdaysRow;
    }

    /**
     * Создает сетку дней календаря
     * @private
     * @returns {HTMLElement} Элемент сетки дней
     */
    createDaysGrid() {
        const daysGrid = document.createElement("div");
        daysGrid.className = "calendar-days";

        const firstDayOfMonth = DateUtils.getFirstDayOfMonth(new Date(this.currentYear, this.currentMonth, 1));
        const lastDayOfMonth = DateUtils.getLastDayOfMonth(new Date(this.currentYear, this.currentMonth, 1));

        // Определяем начало недели
        let firstDayIndex;
        if (this.options.startWeekOnMonday) {
            firstDayIndex = DateUtils.getDayOfWeek(firstDayOfMonth); // 1-7, где 1 - понедельник
        } else {
            firstDayIndex = firstDayOfMonth.getDay(); // 0-6, где 0 - воскресенье
            if (firstDayIndex === 0 && !this.options.startWeekOnMonday) {
                firstDayIndex = 7; // Для отображения воскресенья в конце
            }
        }

        const prevMonthDaysCount = firstDayIndex - 1;
        const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
        const currentMonthDaysCount = lastDayOfMonth.getDate();

        // Рассчитываем общее количество ячеек
        let totalCells;
        if (this.options.showOtherMonthsDays) {
            const totalDays = prevMonthDaysCount + currentMonthDaysCount;
            const rows = Math.ceil(totalDays / 7);
            totalCells = rows * 7;
        } else {
            totalCells = Math.ceil(currentMonthDaysCount / 7) * 7;
        }

        // Создаем ячейки календаря
        for (let cell = 0; cell < totalCells; cell++) {
            const dayEl = document.createElement("div");
            dayEl.className = "calendar-day";

            const inner = document.createElement("div");
            inner.className = "calendar-day-inner";

            let dayNumber;
            let cellDate;
            let isOtherMonth = false;

            // Дни предыдущего месяца
            if (cell < prevMonthDaysCount) {
                if (!this.options.showOtherMonthsDays) {
                    // Пустая ячейка, если не показываем дни других месяцев
                    inner.textContent = "";
                    dayEl.appendChild(inner);
                    daysGrid.appendChild(dayEl);
                    continue;
                }
                dayNumber = prevMonthLastDay - prevMonthDaysCount + 1 + cell;
                isOtherMonth = true;
                const prevMonthIndex = this.currentMonth === 0 ? 11 : this.currentMonth - 1;
                const prevYear = this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear;
                cellDate = new Date(prevYear, prevMonthIndex, dayNumber);
            }
            // Дни текущего и следующего месяца
            else {
                dayNumber = cell - prevMonthDaysCount + 1;
                if (dayNumber > currentMonthDaysCount) {
                    // Дни следующего месяца
                    if (!this.options.showOtherMonthsDays) {
                        // Пустая ячейка, если не показываем дни других месяцев
                        inner.textContent = "";
                        dayEl.appendChild(inner);
                        daysGrid.appendChild(dayEl);
                        continue;
                    }
                    isOtherMonth = true;
                    const nextDayNumber = dayNumber - currentMonthDaysCount;
                    const nextMonthIndex = this.currentMonth === 11 ? 0 : this.currentMonth + 1;
                    const nextYear = this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear;
                    cellDate = new Date(nextYear, nextMonthIndex, nextDayNumber);
                    dayNumber = nextDayNumber;
                } else {
                    // Дни текущего месяца
                    cellDate = new Date(this.currentYear, this.currentMonth, dayNumber);
                }
            }

            inner.textContent = dayNumber;

            if (isOtherMonth) {
                dayEl.classList.add("calendar-day-other");
            }

            // Проверяем, можно ли выбрать эту дату
            if (this.isDateSelectable(cellDate)) {
                // Делаем дату кликабельной
                inner.style.cursor = "pointer";
                inner.addEventListener("click", () => {
                    this.selectDate(cellDate);
                });

                // Подсвечиваем текущую выбранную дату
                if (DateUtils.isSameDay(cellDate, this.currentDate)) {
                    dayEl.classList.add("calendar-day-selected");
                }
            } else {
                dayEl.classList.add("calendar-day-disabled");
            }

            // Добавляем класс для выходных дней
            const dayOfWeek = cellDate.getDay();
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                dayEl.classList.add("calendar-day-weekend");
            }

            // Добавляем класс для сегодняшнего дня
            if (DateUtils.isSameDay(cellDate, DateUtils.getToday())) {
                dayEl.classList.add("calendar-day-today");
            }

            dayEl.appendChild(inner);
            daysGrid.appendChild(dayEl);
        }

        return daysGrid;
    }

    /**
     * Проверяет, отключена ли навигация к предыдущему месяцу
     * @private
     * @returns {boolean} true если навигация отключена
     */
    isPrevMonthDisabled() {
        if (this.minDate === null) return false;

        // Правильно вычисляем предыдущий месяц
        let prevYear = this.currentYear;
        let prevMonth = this.currentMonth - 1;

        if (prevMonth < 0) {
            prevMonth = 11;
            prevYear--;
        }
        console.log(prevMonth)
        // Создаем дату для начала предыдущего месяца
        const prevMonthDate = new Date(prevYear, prevMonth, 1);
        const prevMonthStart = DateUtils.setStartOfDay(prevMonthDate);
        const minDateStart = DateUtils.setStartOfDay(this.minDate);
        console.log(minDateStart)
        console.log(prevMonthStart)
        if (minDateStart) {
            return prevMonthStart <= minDateStart;
        } else {
            return false;
        }
    }

    /**
     * Проверяет, отключена ли навигация к следующему месяцу
     * @private
     * @returns {boolean} true если навигация отключена
     */
    isNextMonthDisabled() {
        if (this.maxDate === null) return false;

        // Правильно вычисляем следующий месяц
        let nextYear = this.currentYear;
        let nextMonth = this.currentMonth + 1;

        if (nextMonth > 11) {
            nextMonth = 0;
            nextYear++;
        }

        // Создаем дату для начала следующего месяца
        const nextMonthDate = new Date(nextYear, nextMonth, 1);
        const nextMonthStart = DateUtils.setStartOfDay(nextMonthDate);
        const maxDateStart = DateUtils.setStartOfDay(this.maxDate);

        return nextMonthStart > maxDateStart;
    }

    /**
     * Переключает месяц календаря
     * @private
     * @param {number} direction - Направление (-1 для предыдущего, 1 для следующего)
     */
    navigateMonth(direction) {
        this.currentMonth += direction;

        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        } else if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        }

        this.render();
    }

    /**
     * Проверяет, доступна ли дата для выбора
     * @private
     * @param {Date} date - Проверяемая дата
     * @returns {boolean} true если дата доступна
     */
    isDateSelectable(date) {
        const dateStart = DateUtils.setStartOfDay(date);

        // Проверяем минимальную дату, если она установлена
        if (this.minDate) {
            const minDateStart = DateUtils.setStartOfDay(this.minDate);
            if (dateStart < minDateStart) {
                return false;
            }
        }

        // Проверяем максимальную дату, если она установлена
        if (this.maxDate) {
            const maxDateStart = DateUtils.setStartOfDay(this.maxDate);
            if (dateStart > maxDateStart) {
                return false;
            }
        }

        return true;
    }

    /**
     * Выбирает дату в календаре
     *
     * @param {Date} date - Дата для выбора
     * @fires Calendar#dateSelect
     *
     * @example
     * // Выбор даты при клике пользователя (вызывается автоматически)
     * calendar.selectDate(new Date('2024-03-15'));
     */
    selectDate(date) {
        if (!this.isDateSelectable(date)) {
            return;
        }

        this.currentDate = DateUtils.cloneDate(date);

        // Обновляем текущий месяц и год для отображения
        this.currentYear = this.currentDate.getFullYear();
        this.currentMonth = this.currentDate.getMonth();

        // Вызываем callback если он установлен
        if (this.options.onDateSelect) {
            /**
             * Событие выбора даты
             * @event Calendar#dateSelect
             * @type {Date}
             */
            this.options.onDateSelect(this.currentDate);
        }

        // Перерисовываем календарь
        this.render();
    }

    /**
     * Устанавливает текущую дату календаря программно
     *
     * @param {Date|string} date - Новая дата
     * @returns {boolean} true если дата была установлена успешно
     *
     * @example
     * calendar.setDate(new Date('2024-05-20'));
     * calendar.setDate('2024-12-25');
     */
    setDate(date) {
        const newDate = DateUtils.cloneDate(date);

        if (this.isDateSelectable(newDate)) {
            this.currentDate = newDate;
            this.currentYear = this.currentDate.getFullYear();
            this.currentMonth = this.currentDate.getMonth();
            this.render();
            return true;
        }
        return false;
    }

    /**
     * Возвращает текущую выбранную дату
     *
     * @returns {Date} Текущая выбранная дата
     *
     * @example
     * const selectedDate = calendar.getDate();
     * console.log('Выбрана дата:', DateUtils.formatDate(selectedDate));
     */
    getDate() {
        return DateUtils.cloneDate(this.currentDate);
    }

    /**
     * Устанавливает минимальную доступную дату
     *
     * @param {Date|string} date - Минимальная дата
     *
     * @example
     * calendar.setMinDate(new Date('2024-01-01'));
     */
    setMinDate(date) {
        this.minDate = date ? DateUtils.cloneDate(date) : null;
        this.render();
    }

    /**
     * Устанавливает максимальную доступную дату
     *
     * @param {Date|string} date - Максимальная дата (null для снятия ограничения)
     *
     * @example
     * calendar.setMaxDate(new Date('2024-12-31'));
     * calendar.setMaxDate(null); // Снять ограничение
     */
    setMaxDate(date) {
        this.maxDate = date ? DateUtils.cloneDate(date) : null;
        this.render();
    }

    /**
     * Обновляет опции календаря
     *
     * @param {Object} newOptions - Новые опции (частичное обновление)
     *
     * @example
     * calendar.updateOptions({
     *     showOtherMonthsDays: false,
     *     locale: 'en-US'
     * });
     */
    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };

        // Обновляем даты, если они изменились
        if (newOptions.initialDate !== undefined) {
            this.setDate(newOptions.initialDate);
        }

        if (newOptions.minDate !== undefined) {
            this.setMinDate(newOptions.minDate);
        }

        if (newOptions.maxDate !== undefined) {
            this.setMaxDate(newOptions.maxDate);
        }

        // Обновляем названия, если они изменились
        if (newOptions.monthsNames !== undefined) {
            this.MONTHS = newOptions.monthsNames;
        }

        if (newOptions.weekdaysNames !== undefined) {
            this.WEEKDAYS = newOptions.weekdaysNames;
        }

        this.render();
    }

    /**
     * Показывает календарь
     *
     * @example
     * calendar.show();
     */
    show() {
        this.calendarRoot.style.display = "block";
    }

    /**
     * Скрывает календарь
     *
     * @example
     * calendar.hide();
     */
    hide() {
        this.calendarRoot.style.display = "none";
    }

    /**
     * Переключает видимость календаря
     *
     * @example
     * calendar.toggle();
     */
    toggle() {
        if (this.calendarRoot.style.display === "none" || !this.calendarRoot.style.display) {
            this.show();
        } else {
            this.hide();
        }
    }

    /**
     * Уничтожает календарь, очищая DOM
     *
     * @example
     * calendar.destroy();
     */
    destroy() {
        this.container.innerHTML = '';
    }
}

// Экспорт для использования в других файлах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Calendar;
}