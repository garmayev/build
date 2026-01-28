/**
 * Компонент календаря для выбора даты
 * @class CalendarV2
 */
class CalendarV2 {
    constructor(container, options = {}) {
        this.container = typeof container === 'string'
            ? document.querySelector(container)
            : container;

        if (!this.container) throw new Error('Контейнер для календаря не найден');

        // Устанавливаем значения по умолчанию
        this.options = {
            initialDate: new Date(),
            minDate: null,
            maxDate: null,
            onDateSelect: null,
            monthsNames: DateUtils.getMonthsNames(),
            weekdaysNames: DateUtils.getWeekdaysNames(),
            showOtherMonthsDays: true,
            showNavigation: true,
            locale: 'ru-RU',
            startWeekOnMonday: true,
            enableYearNavigation: false,
            enableMonthNavigation: false,
            yearNavigationRange: 100,
            allowPastDates: false,
            allowFutureDates: true,
            shortMonthNames: DateUtils.getShortMonthsNames(),
            preserveContainerContent: false,
            mode: 'full', // 'full', 'month-only', 'year-only', 'month-year'
            showDays: true, // Показывать дни (работает только в режиме 'full')
            positionSelector: null, // Селектор элемента для позиционирования
            ...options
        };

        this.initDates();
        this.init();
        this.render();

        // Флаг для отслеживания кликов
        this.isClickInProgress = false;
        // Флаг для отслеживания того, была ли выбрана дата
        this.dateWasSelected = false;
    }

    initDates() {
        this.currentDate = this.parseDate(this.options.initialDate) || new Date();
        this.minDate = this.parseDate(this.options.minDate);
        this.maxDate = this.parseDate(this.options.maxDate);

        if (!this.options.allowPastDates && !this.minDate) {
            this.minDate = DateUtils.getToday();
        }

        this.currentYear = this.currentDate.getFullYear();
        this.currentMonth = this.currentDate.getMonth();
        this.MONTHS = this.options.monthsNames;
        this.SHORT_MONTHS = this.options.shortMonthNames || this.options.monthsNames.map(m => m.substring(0, 3));
        this.WEEKDAYS = this.options.weekdaysNames;
    }

    parseDate(date) {
        if (!date) return null;
        return DateUtils.cloneDate(date);
    }

    init() {
        if (!this.options.preserveContainerContent) {
            this.container.innerHTML = '';
        }

        this.calendarRoot = document.createElement('div');
        this.calendarRoot.className = 'calendar-container';

        // Добавляем класс в зависимости от режима
        if (this.options.mode === 'month-only') {
            this.calendarRoot.classList.add('calendar-mode-month-only');
        } else if (this.options.mode === 'year-only') {
            this.calendarRoot.classList.add('calendar-mode-year-only');
        } else if (this.options.mode === 'month-year') {
            this.calendarRoot.classList.add('calendar-mode-month-year');
        }

        this.container.appendChild(this.calendarRoot);

        // Закрытие по клику вне календаря
        this.outsideClickHandler = (e) => {
            if (!this.calendarRoot.contains(e.target) &&
                (!this.positionElement || !this.positionElement.contains(e.target))) {
                // Если дата была выбрана, скрываем без колбэка, иначе скрываем
                this.hide();
            }
        };
    }

    render() {
        this.calendarRoot.innerHTML = '';
        // В зависимости от режима рендерим разные части
        switch (this.options.mode) {
            case 'month-only':
                this.renderMonthOnly();
                break;
            case 'year-only':
                this.renderYearOnly();
                break;
            case 'month-year':
                this.renderMonthYear();
                break;
            case 'full':
            default:
                this.renderFull();
                break;
        }
    }

    renderFull() {
        if (this.options.showNavigation) {
            this.calendarRoot.appendChild(this.createHeader());
        }

        if (this.options.showDays) {
            this.calendarRoot.appendChild(this.createWeekdays());
            this.calendarRoot.appendChild(this.createDaysGrid());
        }
    }

    renderMonthOnly() {
        // Рендерим только выбор месяца
        const monthSelector = this.createMonthSelector();
        monthSelector.classList.add('calendar-month-only-selector');
        this.calendarRoot.appendChild(monthSelector);

        // Добавляем обработчики для выбора месяца
        this.setupMonthOnlySelector(monthSelector);
    }

    renderYearOnly() {
        // Рендерим только выбор года
        const yearSelector = this.createYearSelector();
        yearSelector.classList.add('calendar-year-only-selector');
        this.calendarRoot.appendChild(yearSelector);

        // Добавляем обработчики для выбора года
        this.setupYearOnlySelector(yearSelector);
    }

    renderMonthYear() {
        console.log('renderMonthYear called');

        // Рендерим выбор месяца и года вместе
        const container = document.createElement('div');
        container.className = 'calendar-month-year-container';

        // Год с навигацией
        const yearRow = this.createYearRow();
        container.appendChild(yearRow);

        // Месяцы сеткой
        const monthGrid = this.createMonthsGrid();
        monthGrid.classList.add('calendar-month-year-grid');
        container.appendChild(monthGrid);

        this.calendarRoot.appendChild(container);

        // Добавляем обработчики
        this.setupMonthYearHandlers();
    }

    createHeader() {
        const header = document.createElement('div');
        header.className = 'calendar-header';

        // Год с навигацией
        const yearRow = this.createYearRow();
        header.appendChild(yearRow);

        // Месяц с навигацией
        const monthRow = this.createMonthRow();
        header.appendChild(monthRow);

        return header;
    }

    createYearRow() {
        const yearRow = document.createElement('div');
        yearRow.className = 'calendar-header-row calendar-header-row-year';

        // Предыдущий год
        const prevYear = this.currentYear - 1;
        const prevYearBtn = document.createElement('button');
        prevYearBtn.className = 'calendar-nav-btn calendar-nav-btn-prev calendar-nav-year-btn';
        prevYearBtn.textContent = prevYear;
        prevYearBtn.title = `Предыдущий год (${prevYear})`;
        prevYearBtn.type = 'button';

        if (this.isPrevYearDisabled()) {
            prevYearBtn.disabled = true;
            prevYearBtn.classList.add('calendar-nav-btn-disabled');
        } else {
            prevYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (this.isClickInProgress) return;
                this.isClickInProgress = true;

                setTimeout(() => {
                    this.navigateYear(-1);
                    this.isClickInProgress = false;
                }, 50);
            });
        }

        yearRow.appendChild(prevYearBtn);

        // Контейнер для года (как у месяца)
        const yearContainer = document.createElement('div');
        yearContainer.className = 'calendar-year-container';

        // Текущий год
        const yearSpan = document.createElement('div');
        yearSpan.className = 'calendar-header-main calendar-header-year';

        // Добавляем класс для текущего элемента (как у месяца)
        if (this.currentYear === this.currentDate.getFullYear()) {
            yearSpan.classList.add('calendar-header-year-current');
        }

        yearSpan.textContent = this.currentYear;

        if (this.isYearNavigationDisabled()) {
            yearSpan.classList.add('calendar-header-year-disabled');
        } else if (this.options.enableYearNavigation) {
            yearSpan.style.cursor = 'pointer';
            yearSpan.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.showYearSelector();
            });
        }

        yearContainer.appendChild(yearSpan);
        yearRow.appendChild(yearContainer);

        // Следующий год
        const nextYear = this.currentYear + 1;
        const nextYearBtn = document.createElement('button');
        nextYearBtn.className = 'calendar-nav-btn calendar-nav-btn-next calendar-nav-year-btn';
        nextYearBtn.textContent = nextYear;
        nextYearBtn.title = `Следующий год (${nextYear})`;
        nextYearBtn.type = 'button';

        if (this.isNextYearDisabled()) {
            nextYearBtn.disabled = true;
            nextYearBtn.classList.add('calendar-nav-btn-disabled');
        } else {
            nextYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (this.isClickInProgress) return;
                this.isClickInProgress = true;

                setTimeout(() => {
                    this.navigateYear(1);
                    this.isClickInProgress = false;
                }, 50);
            });
        }

        yearRow.appendChild(nextYearBtn);

        return yearRow;
    }

    createMonthRow() {
        const monthRow = document.createElement('div');
        monthRow.className = 'calendar-header-row calendar-header-row-month';

        // Предыдущий месяц
        const prevMonthIndex = (this.currentMonth - 1 + 12) % 12;
        const prevMonthBtn = document.createElement('button');
        prevMonthBtn.className = 'calendar-nav-btn calendar-nav-btn-prev calendar-nav-month-btn';
        prevMonthBtn.textContent = this.SHORT_MONTHS[prevMonthIndex];
        prevMonthBtn.title = `Предыдущий месяц (${this.MONTHS[prevMonthIndex]})`;
        prevMonthBtn.type = 'button';

        if (this.isPrevMonthDisabled()) {
            prevMonthBtn.disabled = true;
            prevMonthBtn.classList.add('calendar-nav-btn-disabled');
        } else {
            prevMonthBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (this.isClickInProgress) return;
                this.isClickInProgress = true;

                setTimeout(() => {
                    this.navigateMonth(-1);
                    this.isClickInProgress = false;
                }, 50);
            });
        }

        monthRow.appendChild(prevMonthBtn);

        // Контейнер для месяца (обновляем структуру)
        const monthContainer = document.createElement('div');
        monthContainer.className = 'calendar-month-container';

        const monthSpan = document.createElement('div');
        monthSpan.className = 'calendar-header-main calendar-header-month';

        // Добавляем класс для текущего элемента
        monthSpan.classList.add('calendar-header-month-current');
        monthSpan.textContent = this.MONTHS[this.currentMonth];

        if (this.options.enableMonthNavigation) {
            monthSpan.style.cursor = 'pointer';
            monthSpan.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.showMonthSelector();
            });
        }

        monthContainer.appendChild(monthSpan);
        monthRow.appendChild(monthContainer);

        // Следующий месяц
        const nextMonthIndex = (this.currentMonth + 1) % 12;
        const nextMonthBtn = document.createElement('button');
        nextMonthBtn.className = 'calendar-nav-btn calendar-nav-btn-next calendar-nav-month-btn';
        nextMonthBtn.textContent = this.SHORT_MONTHS[nextMonthIndex];
        nextMonthBtn.title = `Следующий месяц (${this.MONTHS[nextMonthIndex]})`;
        nextMonthBtn.type = 'button';

        if (this.isNextMonthDisabled()) {
            nextMonthBtn.disabled = true;
            nextMonthBtn.classList.add('calendar-nav-btn-disabled');
        } else {
            nextMonthBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (this.isClickInProgress) return;
                this.isClickInProgress = true;

                setTimeout(() => {
                    this.navigateMonth(1);
                    this.isClickInProgress = false;
                }, 50);
            });
        }

        monthRow.appendChild(nextMonthBtn);

        return monthRow;
    }

    setupMonthOnlySelector(container) {
        // Находим все кнопки месяцев и добавляем обработчики
        const monthButtons = container.querySelectorAll('.month-selector-month-btn');

        monthButtons.forEach((btn, index) => {
            if (!btn.disabled) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const monthIndex = index;
                    const date = new Date(this.currentYear, monthIndex, 1);

                    // Устанавливаем дату
                    this.setDate(date);

                    // Вызываем колбэк если есть
                    if (this.options.onDateSelect) {
                        this.options.onDateSelect(this.currentDate);
                    }

                    // Помечаем, что дата была выбрана
                    this.dateWasSelected = true;

                    // Скрываем календарь
                    setTimeout(() => {
                        this.hide();
                        this.dateWasSelected = false;
                    }, 100);
                });
            }
        });

        // Обработчики для навигации по годам
        const prevYearBtn = container.querySelector('.month-selector-year-btn-prev');
        const nextYearBtn = container.querySelector('.month-selector-year-btn-next');
        const currentYearEl = container.querySelector('.month-selector-current-year');

        if (prevYearBtn && !prevYearBtn.disabled) {
            prevYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.navigateYearInSelector(-1);
            });
        }

        if (nextYearBtn && !nextYearBtn.disabled) {
            nextYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.navigateYearInSelector(1);
            });
        }

        if (currentYearEl) {
            currentYearEl.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                // Показываем селектор года
                this.showYearSelector();
            });
        }
    }

    setupYearOnlySelector(container) {
        // Добавляем обработчики для выбора года
        const yearButtons = container.querySelectorAll('.year-btn');
        const decadeButtons = container.querySelectorAll('.year-decade-btn');

        yearButtons.forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    const yearText = btn.textContent;
                    const year = parseInt(yearText);
                    const date = new Date(year, this.currentMonth, 1);

                    // Устанавливаем дату
                    this.setDate(date);

                    // Вызываем колбэк если есть
                    if (this.options.onDateSelect) {
                        this.options.onDateSelect(this.currentDate);
                    }

                    // Помечаем, что дата была выбрана
                    this.dateWasSelected = true;

                    // Скрываем календарь
                    setTimeout(() => {
                        this.hide();
                        this.dateWasSelected = false;
                    }, 100);
                });
            }
        });

        // Обработчики для декад
        decadeButtons.forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    // Декады обрабатываются в showYearsInDecade
                });
            }
        });
    }

    // Добавьте этот метод в класс Calendar
    setupMonthYearHandlers() {
        console.log('setupMonthYearHandlers called');

        const container = this.calendarRoot.querySelector('.calendar-month-year-container');
        if (!container) {
            console.log('No month-year container found');
            return;
        }

        // Обработчики для навигации по годам
        const yearButtons = container.querySelectorAll('.calendar-nav-year-btn');
        const monthButtons = container.querySelectorAll('.month-selector-month-btn');

        console.log('Found month buttons:', monthButtons.length);
        console.log('Found year buttons:', yearButtons.length);

        yearButtons.forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    console.log('Year nav button clicked');
                    const isPrev = btn.classList.contains('calendar-nav-btn-prev');
                    this.navigateYear(isPrev ? -1 : 1);
                    this.updateMonthYearView();
                    // После обновления нужно заново добавить обработчики
                    this.setupMonthYearHandlers();
                });
            }
        });

        // Обработчики для выбора месяца
        monthButtons.forEach((btn, index) => {
            if (!btn.disabled) {
                // Удаляем старые обработчики, если есть
                btn.replaceWith(btn.cloneNode(true));
                const newBtn = container.querySelectorAll('.month-selector-month-btn')[index];

                newBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    console.log('Month button clicked:', index);
                    const monthIndex = index;
                    const date = new Date(this.currentYear, monthIndex, 1);

                    console.log('Setting date to:', date);
                    // Устанавливаем дату
                    this.setDate(date);

                    // Вызываем колбэк если есть
                    console.log('Checking for onDateSelect callback');
                    if (this.options.onDateSelect) {
                        console.log('Calling onDateSelect with:', this.currentDate);
                        this.options.onDateSelect(this.currentDate);
                    } else {
                        console.log('No onDateSelect callback defined');
                    }

                    // Помечаем, что дата была выбрана
                    this.dateWasSelected = true;

                    // Скрываем календарь
                    setTimeout(() => {
                        console.log('Hiding calendar after selection');
                        this.hide();
                        this.dateWasSelected = false;
                    }, 100);
                });
            }
        });

        // Обработчик для клика на текущий год
        const yearSpan = container.querySelector('.calendar-header-year');
        if (yearSpan && this.options.enableYearNavigation) {
            yearSpan.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.showYearSelector();
            });
        }
    }

    // В методе setupMonthYearSelector() исправьте обработчик клика на кнопки месяцев:
    setupMonthYearSelector(container) {
        // Обработчики для навигации по годам
        const yearButtons = container.querySelectorAll('.calendar-nav-year-btn');
        const monthButtons = container.querySelectorAll('.month-selector-month-btn');

        yearButtons.forEach(btn => {
            if (!btn.disabled) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const isPrev = btn.classList.contains('calendar-nav-btn-prev');
                    this.navigateYear(isPrev ? -1 : 1);
                    this.updateMonthYearView();
                });
            }
        });

        // Обработчики для выбора месяца - ДОБАВЬТЕ ВЫЗОВ КОЛБЭКА
        monthButtons.forEach((btn, index) => {
            if (!btn.disabled) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    const monthIndex = index;
                    const date = new Date(this.currentYear, monthIndex, 1);

                    // Устанавливаем дату
                    this.setDate(date);

                    // ВЫЗЫВАЕМ КОЛБЭК ЕСЛИ ЕСТЬ - ДОБАВЬТЕ ЭТО
                    if (this.options.onDateSelect) {
                        this.options.onDateSelect(this.currentDate);
                    }

                    // Помечаем, что дата была выбрана
                    this.dateWasSelected = true;

                    // Скрываем календарь
                    setTimeout(() => {
                        this.hide();
                        this.dateWasSelected = false;
                    }, 100);
                });
            }
        });

        // Обработчик для клика на текущий год
        const yearSpan = container.querySelector('.calendar-header-year');
        if (yearSpan && this.options.enableYearNavigation) {
            yearSpan.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.showYearSelector();
            });
        }
    }

    updateMonthOnlyView() {
        // Обновляем только вид выбора месяца
        const container = this.calendarRoot.querySelector('.calendar-month-only-selector');
        if (container) {
            container.remove();
            this.renderMonthOnly();
        }
    }

    updateMonthYearView() {
        console.log('updateMonthYearView called');

        // Обновляем вид выбора месяца и года
        const container = this.calendarRoot.querySelector('.calendar-month-year-container');
        if (container) {
            console.log('Removing old container');
            container.remove();
        }

        console.log('Rendering new month-year view');
        this.renderMonthYear();
    }

    isPrevYearDisabled() {
        if (!this.minDate) return false;
        return this.currentYear - 1 < this.minDate.getFullYear();
    }

    isNextYearDisabled() {
        if (!this.maxDate) return false;
        return this.currentYear + 1 > this.maxDate.getFullYear();
    }

    isPrevMonthDisabled() {
        if (!this.minDate) return false;

        const prevDate = new Date(this.currentYear, this.currentMonth - 1, 1);
        if (this.currentMonth === 0) {
            prevDate.setFullYear(this.currentYear - 1);
            prevDate.setMonth(11);
        }

        const prevMonthStart = DateUtils.setStartOfDay(prevDate);
        const minDateStart = DateUtils.setStartOfDay(this.minDate);

        return prevMonthStart < minDateStart;
    }

    isNextMonthDisabled() {
        if (!this.maxDate) return false;

        const nextDate = new Date(this.currentYear, this.currentMonth + 1, 1);
        if (this.currentMonth === 11) {
            nextDate.setFullYear(this.currentYear + 1);
            nextDate.setMonth(0);
        }

        const nextMonthStart = DateUtils.setStartOfDay(nextDate);
        const maxDateStart = DateUtils.setStartOfDay(this.maxDate);

        return nextMonthStart > maxDateStart;
    }

    navigateYear(direction) {
        const oldYear = this.currentYear;
        const newYear = this.currentYear + direction;

        // Проверяем ограничения
        if (this.minDate && newYear < this.minDate.getFullYear()) {
            this.currentYear = this.minDate.getFullYear();
        } else if (this.maxDate && newYear > this.maxDate.getFullYear()) {
            this.currentYear = this.maxDate.getFullYear();
        } else {
            this.currentYear = newYear;
        }

        // Если год не изменился из-за ограничений
        if (oldYear === this.currentYear) {
            return;
        }

        // Если месяц стал невалидным, корректируем
        this.adjustMonthForYear();

        // В зависимости от режима рендерим по-разному
        switch (this.options.mode) {
            case 'month-only':
                this.updateMonthOnlyView();
                break;
            case 'year-only':
                this.render();
                break;
            case 'month-year':
                this.updateMonthYearView();
                // После обновления нужно заново добавить обработчики
                this.setupMonthYearHandlers();
                break;
            case 'full':
            default:
                this.render();
                break;
        }
    }

    navigateYearInSelector(direction) {
        const oldYear = this.currentYear;
        const newYear = this.currentYear + direction;

        // Проверяем ограничения
        if (this.minDate && newYear < this.minDate.getFullYear()) {
            this.currentYear = this.minDate.getFullYear();
        } else if (this.maxDate && newYear > this.maxDate.getFullYear()) {
            this.currentYear = this.maxDate.getFullYear();
        } else {
            this.currentYear = newYear;
        }

        // Если год не изменился из-за ограничений
        if (oldYear === this.currentYear) {
            return;
        }

        // Если месяц стал невалидным, корректируем
        this.adjustMonthForYear();

        // Обновляем только вид выбора месяца
        this.updateMonthOnlyView();
    }

    adjustMonthForYear() {
        if (this.minDate && this.currentYear === this.minDate.getFullYear()) {
            if (this.currentMonth < this.minDate.getMonth()) {
                this.currentMonth = this.minDate.getMonth();
            }
        }

        if (this.maxDate && this.currentYear === this.maxDate.getFullYear()) {
            if (this.currentMonth > this.maxDate.getMonth()) {
                this.currentMonth = this.maxDate.getMonth();
            }
        }
    }

    navigateMonth(direction) {
        let newMonth = this.currentMonth + direction;
        let newYear = this.currentYear;

        if (newMonth < 0) {
            newMonth = 11;
            newYear--;
        } else if (newMonth > 11) {
            newMonth = 0;
            newYear++;
        }

        // Проверяем ограничения
        if (this.isMonthAvailable(newYear, newMonth)) {
            this.currentMonth = newMonth;
            this.currentYear = newYear;
            this.render();
        }
    }

    isMonthAvailable(year, month) {
        const monthDate = new Date(year, month, 1);

        if (this.minDate && monthDate < this.minDate) {
            return false;
        }

        if (this.maxDate) {
            const nextMonthDate = new Date(year, month + 1, 0);
            return nextMonthDate <= this.maxDate;
        }

        return true;
    }

    createWeekdays() {
        const weekdaysRow = document.createElement('div');
        weekdaysRow.className = 'calendar-weekdays';

        this.WEEKDAYS.forEach(day => {
            const el = document.createElement('div');
            el.className = 'calendar-weekday';
            el.textContent = day;
            weekdaysRow.appendChild(el);
        });

        return weekdaysRow;
    }

    createDaysGrid() {
        const daysGrid = document.createElement('div');
        daysGrid.className = 'calendar-days';

        const firstDay = DateUtils.getFirstDayOfMonth(new Date(this.currentYear, this.currentMonth, 1));
        const lastDay = DateUtils.getLastDayOfMonth(new Date(this.currentYear, this.currentMonth, 1));

        const firstDayIndex = this.calculateFirstDayIndex(firstDay);
        const prevMonthDaysCount = firstDayIndex - 1;
        const prevMonthLastDay = new Date(this.currentYear, this.currentMonth, 0).getDate();
        const currentMonthDaysCount = lastDay.getDate();

        const totalCells = this.calculateTotalCells(prevMonthDaysCount, currentMonthDaysCount);

        for (let cell = 0; cell < totalCells; cell++) {
            const dayEl = this.createDayCell(cell, prevMonthDaysCount, prevMonthLastDay, currentMonthDaysCount);
            daysGrid.appendChild(dayEl);
        }

        return daysGrid;
    }

    calculateFirstDayIndex(firstDay) {
        if (this.options.startWeekOnMonday) {
            return DateUtils.getDayOfWeek(firstDay);
        }
        return firstDay.getDay() === 0 ? 7 : firstDay.getDay();
    }

    calculateTotalCells(prevMonthDays, currentMonthDays) {
        if (this.options.showOtherMonthsDays) {
            const totalDays = prevMonthDays + currentMonthDays;
            return Math.ceil(totalDays / 7) * 7;
        }
        return Math.ceil(currentMonthDays / 7) * 7;
    }

    createDayCell(cellIndex, prevMonthDaysCount, prevMonthLastDay, currentMonthDaysCount) {
        const dayEl = document.createElement('div');
        dayEl.className = 'calendar-day';

        const { dayNumber, cellDate, isOtherMonth } = this.calculateDayInfo(
            cellIndex, prevMonthDaysCount, prevMonthLastDay, currentMonthDaysCount
        );

        const inner = document.createElement('div');
        inner.className = 'calendar-day-inner';
        inner.textContent = dayNumber;

        this.setupDayCell(dayEl, inner, cellDate, isOtherMonth);

        dayEl.appendChild(inner);
        return dayEl;
    }

    calculateDayInfo(cellIndex, prevMonthDaysCount, prevMonthLastDay, currentMonthDaysCount) {
        let dayNumber, cellDate, isOtherMonth = false;

        if (cellIndex < prevMonthDaysCount) {
            if (!this.options.showOtherMonthsDays) {
                return { dayNumber: '', cellDate: null, isOtherMonth: true };
            }
            dayNumber = prevMonthLastDay - prevMonthDaysCount + 1 + cellIndex;
            isOtherMonth = true;
            const prevMonthIndex = this.currentMonth === 0 ? 11 : this.currentMonth - 1;
            const prevYear = this.currentMonth === 0 ? this.currentYear - 1 : this.currentYear;
            cellDate = new Date(prevYear, prevMonthIndex, dayNumber);
        } else {
            dayNumber = cellIndex - prevMonthDaysCount + 1;
            if (dayNumber > currentMonthDaysCount) {
                if (!this.options.showOtherMonthsDays) {
                    return { dayNumber: '', cellDate: null, isOtherMonth: true };
                }
                isOtherMonth = true;
                const nextDayNumber = dayNumber - currentMonthDaysCount;
                const nextMonthIndex = this.currentMonth === 11 ? 0 : this.currentMonth + 1;
                const nextYear = this.currentMonth === 11 ? this.currentYear + 1 : this.currentYear;
                cellDate = new Date(nextYear, nextMonthIndex, nextDayNumber);
                dayNumber = nextDayNumber;
            } else {
                cellDate = new Date(this.currentYear, this.currentMonth, dayNumber);
            }
        }

        return { dayNumber, cellDate, isOtherMonth };
    }

    setupDayCell(dayEl, inner, cellDate, isOtherMonth) {
        if (isOtherMonth) {
            dayEl.classList.add('calendar-day-other');
        }

        if (cellDate) {
            if (this.isDateSelectable(cellDate)) {
                inner.style.cursor = 'pointer';
                inner.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.selectDate(cellDate);
                });

                if (DateUtils.isSameDay(cellDate, this.currentDate)) {
                    dayEl.classList.add('calendar-day-selected');
                }
            } else {
                dayEl.classList.add('calendar-day-disabled');
            }

            const dayOfWeek = cellDate.getDay();
            if (dayOfWeek === 0 || dayOfWeek === 6) {
                dayEl.classList.add('calendar-day-weekend');
            }

            if (DateUtils.isSameDay(cellDate, DateUtils.getToday())) {
                dayEl.classList.add('calendar-day-today');
            }
        }
    }

    isDateSelectable(date) {
        const dateStart = DateUtils.setStartOfDay(date);
        const currentYear = date.getFullYear();
        const todayYear = new Date().getFullYear();

        if (!this.options.allowPastDates && currentYear < todayYear) return false;
        if (!this.options.allowFutureDates && currentYear > todayYear) return false;

        if (this.minDate) {
            const minDateStart = DateUtils.setStartOfDay(this.minDate);
            if (dateStart < minDateStart) return false;
        }

        if (this.maxDate) {
            const maxDateStart = DateUtils.setStartOfDay(this.maxDate);
            if (dateStart > maxDateStart) return false;
        }

        return true;
    }

    selectDate(date) {
        if (!this.isDateSelectable(date)) return;

        this.currentDate = DateUtils.cloneDate(date);
        this.currentYear = this.currentDate.getFullYear();
        this.currentMonth = this.currentDate.getMonth();

        if (this.options.onDateSelect) {
            this.options.onDateSelect(this.currentDate);
        }

        // Помечаем, что дата была выбрана
        this.dateWasSelected = true;

        this.render();

        // Скрываем календарь после выбора даты (если это режим month/year)
        if (this.options.mode !== 'full') {
            setTimeout(() => {
                this.hide();
                this.dateWasSelected = false;
            }, 100);
        }
    }

    showMonthSelector() {
        if (this.monthSelector) {
            this.hideMonthSelector();
            return;
        }

        this.calendarRoot.classList.add('month-selector-visible');
        this.monthSelector = this.createMonthSelector();
        this.calendarRoot.appendChild(this.monthSelector);

        this.monthSelectorKeyHandler = (e) => {
            if (e.key === 'Escape') this.hideMonthSelector();
        };
        document.addEventListener('keydown', this.monthSelectorKeyHandler);
    }

    createMonthSelector() {
        const container = document.createElement('div');
        container.className = 'month-selector-container';

        // Заголовок
        const header = document.createElement('div');
        header.className = 'month-selector-header';

        const title = document.createElement('div');
        title.className = 'month-selector-title';
        title.textContent = 'Выберите год и месяц';

        const closeBtn = document.createElement('button');
        closeBtn.className = 'month-selector-close';
        closeBtn.innerHTML = '×';
        closeBtn.type = 'button';
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.hideMonthSelector();
        });

        header.append(title, closeBtn);
        container.appendChild(header);

        // Навигация по годам
        const yearNav = this.createYearNavigation();
        container.appendChild(yearNav);

        // Сетка месяцев
        const monthsGrid = this.createMonthsGrid();
        container.appendChild(monthsGrid);

        // Футер
        const footer = document.createElement('div');
        footer.className = 'month-selector-footer';

        const todayBtn = document.createElement('button');
        todayBtn.className = 'month-selector-btn';
        todayBtn.textContent = 'Сегодня';
        todayBtn.type = 'button';
        todayBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.selectToday();
            this.hideMonthSelector();
        });

        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'month-selector-btn primary';
        confirmBtn.textContent = 'Готово';
        confirmBtn.type = 'button';
        confirmBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.hideMonthSelector();
        });

        footer.append(todayBtn, confirmBtn);
        container.appendChild(footer);

        return container;
    }

    createYearNavigation() {
        const yearNav = document.createElement('div');
        yearNav.className = 'month-selector-year-nav';

        // Предыдущий год
        const prevYearBtn = document.createElement('button');
        prevYearBtn.className = 'month-selector-year-btn month-selector-year-btn-prev';
        prevYearBtn.textContent = (this.currentYear - 1).toString();
        prevYearBtn.type = 'button';

        if (this.isPrevYearDisabled()) {
            prevYearBtn.disabled = true;
        } else {
            prevYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.navigateYearInSelector(-1);
            });
        }

        yearNav.appendChild(prevYearBtn);

        // Текущий год
        const currentYear = document.createElement('div');
        currentYear.className = 'month-selector-current-year';
        currentYear.textContent = this.currentYear.toString();
        currentYear.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.showYearSelectorFromMonth();
        });
        yearNav.appendChild(currentYear);

        // Следующий год
        const nextYearBtn = document.createElement('button');
        nextYearBtn.className = 'month-selector-year-btn month-selector-year-btn-next';
        nextYearBtn.textContent = (this.currentYear + 1).toString();
        nextYearBtn.type = 'button';

        if (this.isNextYearDisabled()) {
            nextYearBtn.disabled = true;
        } else {
            nextYearBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.navigateYearInSelector(1);
            });
        }

        yearNav.appendChild(nextYearBtn);

        return yearNav;
    }

    showYearSelectorFromMonth() {
        this.hideMonthSelector();
        this.showYearSelector();
    }

    createMonthsGrid() {
        console.log('createMonthsGrid called, currentYear:', this.currentYear, 'currentMonth:', this.currentMonth);

        const grid = document.createElement('div');
        grid.className = 'month-selector-months-grid';

        this.MONTHS.forEach((monthName, index) => {
            const monthBtn = document.createElement('button');
            monthBtn.className = 'month-selector-month-btn';
            monthBtn.textContent = monthName;
            monthBtn.type = 'button';
            monthBtn.dataset.monthIndex = index;

            // Проверяем, доступен ли месяц
            if (!this.isMonthAvailable(this.currentYear, index)) {
                monthBtn.classList.add('month-selector-month-btn-disabled');
                monthBtn.disabled = true;
            } else if (index === this.currentMonth) {
                monthBtn.classList.add('month-selector-month-btn-current');
            }

            grid.appendChild(monthBtn);
        });

        console.log('Created', this.MONTHS.length, 'month buttons');
        return grid;
    }

    populateMonthsGrid(grid) {
        this.MONTHS.forEach((monthName, index) => {
            const monthBtn = document.createElement('button');
            monthBtn.className = 'month-selector-month-btn';
            monthBtn.textContent = monthName;
            monthBtn.type = 'button';

            // Проверяем, доступен ли месяц
            if (!this.isMonthAvailable(this.currentYear, index)) {
                monthBtn.classList.add('month-selector-month-btn-disabled');
                monthBtn.disabled = true;
            } else if (index === this.currentMonth) {
                monthBtn.classList.add('month-selector-month-btn-current');
            }

            monthBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                this.selectMonth(index);
                this.hideMonthSelector();
            });

            grid.appendChild(monthBtn);
        });
    }

    selectMonth(monthIndex) {
        this.currentMonth = monthIndex;
        this.adjustMonthForYear();
        this.render();
    }

    hideMonthSelector() {
        if (this.monthSelector) {
            this.monthSelector.remove();
            this.monthSelector = null;
            this.calendarRoot.classList.remove('month-selector-visible');
        }

        if (this.monthSelectorKeyHandler) {
            document.removeEventListener('keydown', this.monthSelectorKeyHandler);
            this.monthSelectorKeyHandler = null;
        }
    }

    showYearSelector() {
        if (this.yearSelector) {
            this.hideYearSelector();
            return;
        }

        this.calendarRoot.classList.add('year-selector-visible');
        this.yearSelector = this.createYearSelector();
        this.calendarRoot.appendChild(this.yearSelector);

        this.yearSelectorKeyHandler = (e) => {
            if (e.key === 'Escape') this.hideYearSelector();
        };
        document.addEventListener('keydown', this.yearSelectorKeyHandler);
    }

    createYearSelector() {
        const container = document.createElement('div');
        container.className = 'year-selector-container';

        container.appendChild(this.createYearSelectorHeader());
        container.appendChild(this.createYearSelectorContent());
        container.appendChild(this.createYearSelectorFooter());

        return container;
    }

    createYearSelectorHeader() {
        const header = document.createElement('div');
        header.className = 'year-selector-header';

        const title = document.createElement('div');
        title.className = 'year-selector-title';
        title.textContent = 'Выберите десятилетие';

        const closeBtn = document.createElement('button');
        closeBtn.className = 'year-selector-close';
        closeBtn.innerHTML = '×';
        closeBtn.type = 'button';
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.hideYearSelector();
        });

        header.append(title, closeBtn);
        return header;
    }

    createYearSelectorContent() {
        const content = document.createElement('div');
        content.className = 'year-selector-content';

        // Контейнер для декад (как было изначально)
        const decadesContainer = document.createElement('div');
        decadesContainer.className = 'year-selector-decades';
        this.decadesContainer = decadesContainer;

        // Генерируем доступные декады
        const decades = this.generateAvailableDecades();

        // Добавляем декады
        decades.forEach(decade => {
            const btn = document.createElement('button');
            btn.className = 'year-decade-btn';
            btn.textContent = decade.label;
            btn.type = 'button';

            // Проверяем, активна ли текущая декада
            if (this.currentYear >= decade.start && this.currentYear <= decade.end) {
                btn.classList.add('active');
            }

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                // Переходим ко второму этапу - выбору года в декаде
                this.showYearsInDecade(decade);
            });

            decadesContainer.appendChild(btn);
        });

        content.appendChild(decadesContainer);
        return content;
    }

    generateAvailableDecades() {
        const { minYear, maxYear } = this.getYearLimits();

        // Начинаем с ближайшей декады, кратной 10
        let decadeStart = Math.floor(minYear / 10) * 10;
        const endDecade = Math.ceil(maxYear / 10) * 10;

        const decades = [];

        while (decadeStart <= endDecade) {
            const decadeEnd = decadeStart + 9;

            // Проверяем, есть ли доступные годы в этой декаде
            const hasAvailableYears = this.hasAvailableYearsInRange(
                Math.max(decadeStart, minYear),
                Math.min(decadeEnd, maxYear)
            );

            if (hasAvailableYears) {
                decades.push({
                    start: decadeStart,
                    end: decadeEnd,
                    label: `${decadeStart} - ${decadeEnd}`
                });
            }

            decadeStart += 10;
        }

        return decades;
    }

    getYearLimits() {
        const currentYear = new Date().getFullYear();
        let minYear = 1900; // Минимальный год по умолчанию
        let maxYear = currentYear + 50; // Максимальный год по умолчанию

        // Если не разрешены прошлые даты, устанавливаем текущий год как минимальный
        if (!this.options.allowPastDates && !this.minDate) {
            minYear = currentYear;
        }

        // Если установлена минимальная дата
        if (this.minDate) {
            minYear = Math.max(minYear, this.minDate.getFullYear());
        }

        // Если установлена максимальная дата
        if (this.maxDate) {
            maxYear = Math.min(maxYear, this.maxDate.getFullYear());
        }

        // Если не разрешены будущие даты и не установлена максимальная дата
        if (!this.options.allowFutureDates && !this.maxDate) {
            maxYear = currentYear;
        }

        return { minYear, maxYear };
    }

    hasAvailableYearsInRange(startYear, endYear) {
        for (let year = startYear; year <= endYear; year++) {
            const yearDate = new Date(year, 0, 1);
            if (this.isYearAvailable(year)) {
                return true;
            }
        }
        return false;
    }

    showYearsInDecade(decade) {
        if (!this.yearSelector || !this.decadesContainer) return;

        // Обновляем заголовок
        const title = this.yearSelector.querySelector('.year-selector-title');
        if (title) {
            title.textContent = `Выберите год (${decade.label})`;
        }

        // Очищаем контент
        const content = this.yearSelector.querySelector('.year-selector-content');
        content.innerHTML = '';

        // Добавляем кнопку "Назад к декадам"
        const backBtnContainer = document.createElement('div');
        backBtnContainer.className = 'year-selector-back';

        const backBtn = document.createElement('button');
        backBtn.className = 'year-back-btn';
        backBtn.innerHTML = '← Назад к десятилетиям';
        backBtn.type = 'button';
        backBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            // Возвращаемся к первому этапу
            this.showDecadesAgain();
        });

        backBtnContainer.appendChild(backBtn);
        content.appendChild(backBtnContainer);

        // Создаем контейнер для годов
        const yearsContainer = document.createElement('div');
        yearsContainer.className = 'year-selector-years';

        // Добавляем годы
        this.addYearsToContainer(decade.start, decade.end, yearsContainer);

        content.appendChild(yearsContainer);

        this.currentDecade = decade;
    }

    addYearsToContainer(startYear, endYear, container) {
        const currentYear = new Date().getFullYear();
        const today = DateUtils.getToday();

        // Получаем ограничения по годам
        const { minYear, maxYear } = this.getYearLimits();

        // Корректируем диапазон с учетом ограничений
        const actualStart = Math.max(startYear, minYear);
        const actualEnd = Math.min(endYear, maxYear);

        for (let year = actualStart; year <= actualEnd; year++) {
            const btn = document.createElement('button');
            btn.className = 'year-btn';
            btn.textContent = year;
            btn.type = 'button';

            // Проверяем, является ли год сегодняшним
            if (year === today.getFullYear()) {
                btn.classList.add('current-year');
            }

            // Проверяем, является ли год текущим выбранным
            if (year === this.currentYear) {
                btn.classList.add('selected-year');
            }

            // Проверяем, доступен ли год для выбора
            if (!this.isYearAvailable(year)) {
                btn.classList.add('year-disabled');
                btn.disabled = true;
            } else {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    this.selectYear(year);
                });
            }

            container.appendChild(btn);
        }

        // Если не нашлось ни одного года в диапазоне, показываем сообщение
        if (container.children.length === 0) {
            const message = document.createElement('div');
            message.className = 'year-no-results';
            message.textContent = 'Нет доступных лет в этом десятилетии';
            message.style.textAlign = 'center';
            message.style.padding = '20px';
            message.style.color = '#999';
            message.style.width = '100%';
            container.appendChild(message);
        }
    }

    showDecadesAgain() {
        if (!this.yearSelector) return;

        // Обновляем заголовок
        const title = this.yearSelector.querySelector('.year-selector-title');
        if (title) {
            title.textContent = 'Выберите десятилетие';
        }

        // Очищаем контент
        const content = this.yearSelector.querySelector('.year-selector-content');
        content.innerHTML = '';

        // Создаем контейнер для декад
        const decadesContainer = document.createElement('div');
        decadesContainer.className = 'year-selector-decades';
        this.decadesContainer = decadesContainer;

        // Генерируем декады с учетом ограничений
        const decades = this.generateAvailableDecades();

        // Добавляем декады
        decades.forEach(decade => {
            const btn = document.createElement('button');
            btn.className = 'year-decade-btn';
            btn.textContent = decade.label;
            btn.type = 'button';

            // Проверяем, активна ли текущая декада
            if (this.currentYear >= decade.start && this.currentYear <= decade.end) {
                btn.classList.add('active');
            }

            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopImmediatePropagation();
                // Переходим ко второму этапу - выбору года в декаде
                this.showYearsInDecade(decade);
            });

            decadesContainer.appendChild(btn);
        });

        // Добавляем декады в контент
        content.appendChild(decadesContainer);
    }

    isYearAvailable(year) {
        if (this.minDate && year < this.minDate.getFullYear()) return false;
        if (this.maxDate && year > this.maxDate.getFullYear()) return false;
        if (!this.options.allowPastDates && year < new Date().getFullYear()) return false;
        return !(!this.options.allowFutureDates && year > new Date().getFullYear());
    }

    selectYear(year) {
        console.log('selectYear called for:', year);
        this.currentYear = year;

        // В зависимости от режима рендерим по-разному
        switch (this.options.mode) {
            case 'month-only':
                this.updateMonthOnlyView();
                break;
            case 'year-only':
                this.render();
                break;
            case 'month-year':
                this.updateMonthYearView();
                // После обновления нужно заново добавить обработчики
                this.setupMonthYearHandlers();
                break;
            case 'full':
            default:
                this.render();
                break;
        }

        // Вызываем колбэк если есть
        if (this.options.onDateSelect) {
            console.log('Calling onDateSelect from selectYear');
            const date = new Date(this.currentYear, this.currentMonth, 1);
            this.options.onDateSelect(date);
        }

        this.hideYearSelector();
    }

    createYearSelectorFooter() {
        const footer = document.createElement('div');
        footer.className = 'year-selector-footer';

        const todayBtn = document.createElement('button');
        todayBtn.className = 'year-selector-btn';
        todayBtn.textContent = 'Сегодня';
        todayBtn.type = 'button';
        todayBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.selectToday();
        });

        const confirmBtn = document.createElement('button');
        confirmBtn.className = 'year-selector-btn primary';
        confirmBtn.textContent = 'Готово';
        confirmBtn.type = 'button';
        confirmBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.hideYearSelector();
        });

        footer.append(todayBtn, confirmBtn);
        return footer;
    }

    hideYearSelector() {
        if (this.yearSelector) {
            this.yearSelector.remove();
            this.yearSelector = null;
            this.calendarRoot.classList.remove('year-selector-visible');
            this.currentDecade = null;
        }

        if (this.yearSelectorKeyHandler) {
            document.removeEventListener('keydown', this.yearSelectorKeyHandler);
            this.yearSelectorKeyHandler = null;
        }
    }

    selectToday() {
        const today = new Date();
        this.currentYear = today.getFullYear();
        this.currentMonth = today.getMonth();
        this.setDate(today);
        this.hideMonthSelector();
        this.hideYearSelector();
    }

    // Public API
    setDate(date) {
        const newDate = DateUtils.cloneDate(date);
        if (this.isDateSelectable(newDate)) {
            this.currentDate = newDate;
            this.currentYear = this.currentDate.getFullYear();
            this.currentMonth = this.currentDate.getMonth();

            // В зависимости от режима рендерим по-разному
            switch (this.options.mode) {
                case 'month-only':
                    this.updateMonthOnlyView();
                    break;
                case 'year-only':
                    this.render();
                    break;
                case 'month-year':
                    this.updateMonthYearView();
                    break;
                case 'full':
                default:
                    this.render();
                    break;
            }

            // ВЫЗЫВАЕМ КОЛБЭК ЕСЛИ ЕСТЬ - УБЕДИТЕСЬ, ЧТО ЭТО ЕСТЬ
            if (this.options.onDateSelect) {
                this.options.onDateSelect(this.currentDate);
            }

            return true;
        }
        return false;
    }

    getDate() {
        return DateUtils.cloneDate(this.currentDate);
    }

    setMinDate(date) {
        this.minDate = this.parseDate(date);
        this.render();
    }

    setMaxDate(date) {
        this.maxDate = this.parseDate(date);
        this.render();
    }

    updateOptions(newOptions) {
        const oldMode = this.options.mode;
        this.options = { ...this.options, ...newOptions };

        if (newOptions.mode && newOptions.mode !== oldMode) {
            // Если изменился режим, полностью переинициализируем
            this.init();
        }

        if (newOptions.initialDate !== undefined) this.setDate(newOptions.initialDate);
        if (newOptions.minDate !== undefined) this.setMinDate(newOptions.minDate);
        if (newOptions.maxDate !== undefined) this.setMaxDate(newOptions.maxDate);
        if (newOptions.monthsNames !== undefined) this.MONTHS = newOptions.monthsNames;
        if (newOptions.weekdaysNames !== undefined) this.WEEKDAYS = newOptions.weekdaysNames;
        if (newOptions.shortMonthNames !== undefined) this.SHORT_MONTHS = newOptions.shortMonthNames;
        if (newOptions.showDays !== undefined) this.options.showDays = newOptions.showDays;

        this.render();
    }

    show() {
        // Позиционируем календарь
        if (this.options.positionSelector) {
            this.positionElement = document.querySelector(this.options.positionSelector);
            if (this.positionElement) {
                this.positionCalendar();
            }
        }

        this.calendarRoot.style.display = 'block';
        this.calendarRoot.style.visibility = 'visible';

        // Добавляем обработчик клика вне календаря
        setTimeout(() => {
            document.addEventListener('click', this.outsideClickHandler);
        }, 10);
    }

    hide() {
        this.calendarRoot.style.display = 'none';
        // Удаляем обработчик клика вне календаря
        document.removeEventListener('click', this.outsideClickHandler);
    }

    toggle() {
        if (this.calendarRoot.style.display === 'none' || !this.calendarRoot.style.display) {
            this.show();
        } else {
            this.hide();
        }
    }

    positionCalendar() {
        if (!this.positionElement) return;

        const elementRect = this.positionElement.getBoundingClientRect();
        const calendarRect = this.calendarRoot.getBoundingClientRect();
        const windowHeight = window.innerHeight;
        const windowWidth = window.innerWidth;

        // Сбрасываем стили позиционирования
        this.calendarRoot.style.position = 'fixed';
        this.calendarRoot.style.left = '';
        this.calendarRoot.style.right = '';
        this.calendarRoot.style.top = '';
        this.calendarRoot.style.bottom = '';
        this.calendarRoot.style.transform = '';

        // Рассчитываем доступное пространство сверху и снизу
        const spaceAbove = elementRect.top;
        const spaceBelow = windowHeight - elementRect.bottom;
        const calendarHeight = calendarRect.height || 300; // Примерная высота

        // Определяем, куда лучше поместить календарь
        if (spaceBelow >= calendarHeight || spaceBelow >= spaceAbove) {
            // Помещаем под элементом
            this.calendarRoot.style.top = (elementRect.bottom + window.scrollY) + 'px';
        } else {
            // Помещаем над элементом
            this.calendarRoot.style.bottom = (windowHeight - elementRect.top + window.scrollY) + 'px';
        }

        // Горизонтальное позиционирование
        const spaceLeft = elementRect.left;
        const spaceRight = windowWidth - elementRect.right;
        const calendarWidth = calendarRect.width || 300; // Примерная ширина

        if (spaceRight >= calendarWidth || spaceRight >= spaceLeft) {
            // Выравниваем по левому краю элемента
            this.calendarRoot.style.left = (elementRect.left + window.scrollX) + 'px';
        } else {
            // Выравниваем по правому краю элемента
            this.calendarRoot.style.right = (windowWidth - elementRect.right + window.scrollX) + 'px';
        }

        // Добавляем небольшой отступ
        this.calendarRoot.style.margin = '5px 0';
        this.calendarRoot.style.zIndex = '10000';
    }

    destroy() {
        this.hide();
        this.container.innerHTML = '';
        this.outsideClickHandler = null;
    }

    isYearNavigationDisabled() {
        if (!this.options.enableYearNavigation) return true;
        if (this.minDate && this.maxDate) {
            return this.minDate.getFullYear() === this.maxDate.getFullYear();
        }
        return false;
    }
}

// Экспорт для использования в других файлах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Calendar;
}
