/**
 * Компонент выбора даты с выпадающим календарем
 *
 * @class DateSelector
 * @description Представляет собой поле выбора даты с кнопками навигации и выпадающим календарем.
 * Поддерживает ограничение по датам, кастомизацию и интеграцию с расписанием.
 *
 * @example
 * // Базовое использование
 * const dateSelector = new DateSelector('#date-selector-container', {
 *     initialDate: new Date(),
 *     onChange: (date) => console.log('Дата изменена:', date)
 * });
 *
 * @example
 * // Расширенное использование с кастомизацией календаря
 * const dateSelector = new DateSelector('#date-selector-container', {
 *     initialDate: new Date('2024-03-15'),
 *     useLeftRightButtons: true,
 *     onChange: (date) => {
 *         console.log('Выбрана дата:', DateUtils.formatDate(date, 'DD.MM.YYYY'));
 *         // Обновить расписание и т.д.
 *     },
 *     calendarOptions: {
 *         showOtherMonthsDays: false,
 *         showNavigation: true,
 *         allowPastDates: false,
 *         locale: 'ru-RU',
 *         startWeekOnMonday: true
 *     }
 * });
 *
 * // Программное управление
 * dateSelector.setDate(new Date('2024-05-20'));
 * const currentDate = dateSelector.getDate();
 * dateSelector.updateToday(); // Обновить "сегодняшнюю" дату
 *
 * // Обновление опций
 * dateSelector.updateOptions({
 *     useLeftRightButtons: false,
 *     calendarOptions: {
 *         allowPastDates: true
 *     }
 * });
 *
 * // Уничтожение компонента при необходимости
 * dateSelector.destroy();
 */
class DateSelector {
    /**
     * Создает экземпляр компонента выбора даты
     *
     * @constructor
     * @param {string} containerId - CSS-селектор контейнера для компонента
     * @param {Object} [options={}] - Опции конфигурации компонента
     * @param {Date|string} [options.initialDate=new Date()] - Начальная дата
     * @param {boolean} [options.useLeftRightButtons=true] - Показывать кнопки навигации "влево/вправо"
     * @param {Function} [options.onChange=null] - Callback-функция при изменении даты
     * @param {Object} [options.calendarOptions={}] - Опции для встроенного календаря
     * @param {boolean} [options.calendarOptions.showOtherMonthsDays=true] - Показывать дни других месяцев
     * @param {boolean} [options.calendarOptions.showNavigation=true] - Показывать навигацию календаря
     * @param {boolean} [options.calendarOptions.allowPastDates=false] - Разрешать выбор прошедших дат
     * @param {string} [options.calendarOptions.locale='ru-RU'] - Локаль календаря
     *
     * @throws {Error} Если контейнер не найден
     */
    constructor(containerId, options = {}) {
        this.container = document.querySelector(containerId);

        // Проверяем наличие контейнера
        if (!this.container) {
            throw new Error(`Контейнер с селектором "${containerId}" не найден`);
        }

        this.today = DateUtils.getToday();
        this.options = {
            initialDate: new Date(),
            useLeftRightButtons: true,
            onChange: null,
            calendarOptions: {
                showOtherMonthsDays: true,
                showNavigation: true,
                allowPastDates: false,
                locale: 'ru-RU'
            },
            ...options
        };

        // Проверяем, что initialDate не раньше сегодняшнего дня
        const inputDate = DateUtils.setStartOfDay(new Date(this.options.initialDate));

        if (inputDate < this.today && !this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
        } else {
            this.currentDate = DateUtils.cloneDate(inputDate);
        }

        this.calendarVisible = false;
        this.calendarPopup = null;
        this.calendarInner = null;

        // Переменные для отслеживания скролла
        this.lastScrollPosition = window.scrollY;
        this.scrollTimeout = null;

        this.init();
    }

    /**
     * Инициализирует компонент и создает DOM-структуру
     * @private
     */
    init() {
        // Создаем обертку с position: relative для правильного позиционирования календаря
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'date-selector-wrapper';

        // Создаем основные элементы
        this.dateSelector = document.createElement('div');
        this.dateSelector.className = 'date-selector';

        if (this.options.useLeftRightButtons) {
            // Кнопка предыдущего дня
            this.prevBtn = document.createElement('button');
            this.prevBtn.className = 'date-arrow date-prev';
            this.prevBtn.innerHTML = '<';
            this.prevBtn.setAttribute('role', 'button')
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.changeDate(-1);
            });
        }

        // Контейнер для отображения даты
        this.dateDisplay = document.createElement('div');
        this.dateDisplay.className = 'date-display';

        this.dateText = document.createElement('span');
        this.dateText.className = 'date-text';
        this.dateText.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleCalendar();
        });

        this.dateDisplay.appendChild(this.dateText);

        if (this.options.useLeftRightButtons) {
            // Кнопка следующего дня
            this.nextBtn = document.createElement('button');
            this.nextBtn.className = 'date-arrow date-next';
            this.nextBtn.innerHTML = '>';
            this.nextBtn.setAttribute('role', 'button');
            this.nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.changeDate(1);
            });
        }

        // Собираем компонент
        if (this.options.useLeftRightButtons) {
            this.dateSelector.appendChild(this.prevBtn);
        }
        this.dateSelector.appendChild(this.dateDisplay);
        if (this.options.useLeftRightButtons) {
            this.dateSelector.appendChild(this.nextBtn);
        }

        this.wrapper.appendChild(this.dateSelector);
        this.container.appendChild(this.wrapper);

        // Обновляем отображение даты
        this.updateDateDisplay();
        this.updateButtonsState();

        // Обработчик клика на документе для закрытия календаря
        this.documentClickListener = (e) => {
            if (this.calendarVisible && this.calendarPopup) {
                // Проверяем, кликнули ли мы на сам календарь или на элементы date-selector
                const clickedOnCalendar = this.calendarPopup.contains(e.target);
                const clickedOnDateSelector = this.wrapper.contains(e.target);

                if (!clickedOnCalendar && !clickedOnDateSelector) {
                    this.hideCalendar();
                }
            }
        };

        // Обработчик для скролла - скрываем календарь при начале скролла
        this.scrollHandler = () => {
            if (this.calendarVisible) {
                // Определяем направление и величину скролла
                const currentScrollPosition = window.scrollY;
                const scrollDelta = Math.abs(currentScrollPosition - this.lastScrollPosition);

                // Если скролл значительный (больше 5px), скрываем календарь
                if (scrollDelta > 5) {
                    this.hideCalendar();
                }

                this.lastScrollPosition = currentScrollPosition;
            }
        };

        // Обработчик для ресайза - скрываем календарь при изменении размера окна
        this.resizeHandler = () => {
            if (this.calendarVisible) {
                this.hideCalendar();
            }
        };

        // Добавляем обработчики
        document.addEventListener('click', this.documentClickListener);
        window.addEventListener('scroll', this.scrollHandler, { passive: true });
        window.addEventListener('resize', this.resizeHandler);
    }

    /**
     * Обновляет отображение текущей даты в компоненте
     * @private
     * @fires DateSelector#change
     */
    updateDateDisplay() {
        // Форматируем дату в формате DD.MM.YYYY
        this.dateText.textContent = DateUtils.formatDate(this.currentDate, 'DD.MM.YYYY');

        // Вызываем callback при изменении даты
        if (this.options.onChange) {
            /**
             * Событие изменения даты
             * @event DateSelector#change
             * @type {Date}
             */
            this.options.onChange(this.currentDate);
        }
    }

    /**
     * Обновляет состояние кнопок навигации
     * @private
     */
    updateButtonsState() {
        // Отключаем кнопку "назад", если текущая дата - сегодня и не разрешены прошедшие даты
        const currentDateStart = DateUtils.setStartOfDay(this.currentDate);
        if (this.options.useLeftRightButtons) {
            if (currentDateStart <= this.today && !this.options.calendarOptions.allowPastDates) {
                this.prevBtn.disabled = true;
                this.prevBtn.classList.add('date-arrow-disabled');
            } else {
                this.prevBtn.disabled = false;
                this.prevBtn.classList.remove('date-arrow-disabled');
            }
        }
    }

    /**
     * Изменяет текущую дату на указанное количество дней
     *
     * @param {number} days - Количество дней для изменения (положительное или отрицательное)
     *
     * @example
     * dateSelector.changeDate(1);  // Перейти на завтра
     * dateSelector.changeDate(-1); // Перейти на вчера
     * dateSelector.changeDate(7);  // Перейти на неделю вперед
     */
    changeDate(days) {
        const newDate = DateUtils.addDays(this.currentDate, days);

        // Проверяем, не пытаемся ли перейти на прошедший день
        const newDateStart = DateUtils.setStartOfDay(newDate);

        if (newDateStart >= this.today || this.options.calendarOptions.allowPastDates) {
            this.currentDate = newDate;
            this.updateDateDisplay();
            this.updateButtonsState();
            this.updateCalendarDate();
        } else {
            // Если пытаемся перейти на прошедший день и не разрешены прошедшие даты, устанавливаем сегодняшнюю дату
            this.currentDate = DateUtils.cloneDate(this.today);
            this.updateDateDisplay();
            this.updateButtonsState();
            this.updateCalendarDate();
        }
    }

    /**
     * Переключает видимость выпадающего календаря
     *
     * @example
     * dateSelector.toggleCalendar(); // Открыть/закрыть календарь
     */
    toggleCalendar() {
        if (this.calendarVisible) {
            this.hideCalendar();
        } else {
            this.showCalendar();
        }
    }

    /**
     * Создает всплывающее окно с календарем
     * @private
     * @returns {HTMLElement} Элемент календаря
     */
    createCalendarPopup() {
        // Удаляем старый календарь, если есть
        if (this.calendarPopup) {
            this.calendarPopup.remove();
        }

        // Создаем новый контейнер для календаря в body
        this.calendarPopup = document.createElement('div');
        this.calendarPopup.className = 'calendar-popup date-selector-calendar-popup';
        this.calendarPopup.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 10050;
            pointer-events: none;
        `;
        this.calendarPopup.addEventListener('click', this.toggleCalendar.bind(this))

        // Создаем внутренний контейнер для календаря
        this.calendarInner = document.createElement('div');
        this.calendarInner.className = 'calendar-popup-inner';
        this.calendarInner.style.cssText = `
            position: absolute;
            pointer-events: auto;
        `;

        this.calendarPopup.appendChild(this.calendarInner);
        document.body.appendChild(this.calendarPopup);

        // Настраиваем опции для календаря
        const calendarOptions = {
            minDate: this.options.calendarOptions.allowPastDates ? null : this.today,
            onDateSelect: (date) => this.handleCalendarDateSelect(date),
            ...this.options.calendarOptions
        };

        // Инициализируем календарь
        this.calendar = new Calendar(
            this.calendarInner,
            calendarOptions
        );

        // Устанавливаем текущую дату в календарь
        this.calendar.setDate(this.currentDate);

        // Добавляем обработчик для остановки всплытия событий внутри календаря
        this.calendarInner.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        return this.calendarInner;
    }

    /**
     * Показывает выпадающий календарь
     * @private
     */
    showCalendar() {
        // Проверяем, не открыто ли модальное окно
        if (document.querySelector('.modal.show')) {
            console.warn('Календарь не может быть открыт при активном модальном окне');
            return;
        }

        // Если календарь уже видим, скрываем его
        if (this.calendarVisible && this.calendarPopup) {
            this.hideCalendar();
            return;
        }

        // Скрываем другие открытые календари
        document.querySelectorAll('.calendar-popup').forEach(popup => {
            if (popup !== this.calendarPopup) {
                popup.remove();
            }
        });

        // Создаем календарь
        this.createCalendarPopup();
        this.calendarPopup.style.display = 'block';
        this.calendarVisible = true;

        // Запоминаем текущую позицию скролла
        this.lastScrollPosition = window.scrollY;

        // Позиционируем календарь относительно dateText
        this.positionCalendar();
    }

    /**
     * Позиционирует календарь относительно элемента отображения даты
     * @private
     */
    positionCalendar() {
        const dateTextRect = this.dateText.getBoundingClientRect();
        const calendarContainer = this.calendarInner.querySelector('.calendar-container');

        if (!calendarContainer) return;

        // Устанавливаем размеры календаря перед расчетом
        calendarContainer.style.width = '220px';
        calendarContainer.style.height = 'auto';

        const calendarRect = calendarContainer.getBoundingClientRect();

        // Рассчитываем позицию
        let top = dateTextRect.bottom + window.scrollY + 5;
        let left = dateTextRect.left + window.scrollX;

        // Проверяем, не выходит ли календарь за правый край экрана
        if (left + calendarRect.width > window.innerWidth) {
            left = window.innerWidth - calendarRect.width - 10;
        }

        // Проверяем, не выходит ли календарь за нижний край экрана
        if (top + calendarRect.height > window.innerHeight + window.scrollY) {
            top = dateTextRect.top + window.scrollY - calendarRect.height - 5;
        }

        // Проверяем, не выходит ли календарь за левый край экрана
        if (left < 10) {
            left = 10;
        }

        // Применяем позицию
        this.calendarInner.style.top = top + 'px';
        this.calendarInner.style.left = left + 'px';

        // Обновляем дату в календаре перед показом
        if (this.calendar) {
            this.calendar.setDate(this.currentDate);
        }
        this.calendarVisible = true;
    }

    /**
     * Скрывает выпадающий календарь
     * @private
     */
    hideCalendar() {
        if (this.calendarPopup) {
            this.calendarPopup.style.display = 'none';
            this.calendarPopup.remove();
            this.calendarPopup = null;
            this.calendarInner = null;
        }
        this.calendarVisible = false;

        // Очищаем таймаут если был
        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = null;
        }
    }

    /**
     * Обрабатывает выбор даты в календаре
     * @private
     * @param {Date} date - Выбранная дата
     */
    handleCalendarDateSelect(date) {
        this.currentDate = DateUtils.cloneDate(date);
        this.updateDateDisplay();
        this.updateButtonsState();
        this.hideCalendar();

        if (this.options.onChange) {
            this.options.onChange(this.currentDate);
        }
    }

    /**
     * Обновляет дату в выпадающем календаре
     * @private
     */
    updateCalendarDate() {
        if (this.calendar) {
            this.calendar.setDate(this.currentDate);
        }
    }

    /**
     * Устанавливает текущую дату программно
     *
     * @param {Date|string} date - Новая дата
     *
     * @example
     * dateSelector.setDate(new Date('2024-05-20'));
     * dateSelector.setDate('2024-12-25');
     */
    setDate(date) {
        const newDate = DateUtils.cloneDate(date);
        const newDateStart = DateUtils.setStartOfDay(newDate);

        // Проверяем, что устанавливаемая дата не раньше сегодняшнего дня (если не разрешены прошедшие даты)
        if (newDateStart >= this.today || this.options.calendarOptions.allowPastDates) {
            this.currentDate = newDate;
        } else if (!this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
        }

        this.updateDateDisplay();
        this.updateButtonsState();
        this.updateCalendarDate();
    }

    /**
     * Возвращает текущую выбранную дату
     *
     * @returns {Date} Текущая дата
     *
     * @example
     * const currentDate = dateSelector.getDate();
     * console.log('Текущая дата:', DateUtils.formatDate(currentDate));
     */
    getDate() {
        return DateUtils.cloneDate(this.currentDate);
    }

    /**
     * Обновляет "сегодняшнюю" дату в компоненте
     *
     * @returns {boolean} true если "сегодняшняя" дата изменилась
     *
     * @example
     * const dateChanged = dateSelector.updateToday();
     * if (dateChanged) {
     *     console.log('Дата "сегодня" была обновлена');
     * }
     */
    updateToday() {
        const oldToday = DateUtils.cloneDate(this.today);
        this.today = DateUtils.getToday();

        // Если текущая дата раньше обновленного "сегодня" и не разрешены прошедшие даты, обновляем ее
        const currentDateStart = DateUtils.setStartOfDay(this.currentDate);

        if (currentDateStart < this.today && !this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
            this.updateDateDisplay();
        }

        // Обновляем минимальную дату в календаре (если не разрешены прошедшие даты)
        if (this.calendar && !this.options.calendarOptions.allowPastDates) {
            this.calendar.setMinDate(this.today);
        }

        this.updateButtonsState();

        return oldToday.getTime() !== this.today.getTime();
    }

    /**
     * Обновляет опции компонента
     *
     * @param {Object} newOptions - Новые опции (частичное обновление)
     *
     * @example
     * dateSelector.updateOptions({
     *     useLeftRightButtons: false,
     *     onChange: (date) => console.log('Новая дата:', date),
     *     calendarOptions: {
     *         allowPastDates: true
     *     }
     * });
     */
    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };

        if (newOptions.calendarOptions) {
            this.options.calendarOptions = {
                ...this.options.calendarOptions,
                ...newOptions.calendarOptions
            };
        }

        this.updateDateDisplay();
        this.updateButtonsState();
    }

    /**
     * Уничтожает компонент, очищая все обработчики и DOM-элементы
     *
     * @example
     * dateSelector.destroy();
     */
    destroy() {
        this.hideCalendar();
        document.removeEventListener('click', this.documentClickListener);
        window.removeEventListener('scroll', this.scrollHandler);
        window.removeEventListener('resize', this.resizeHandler);

        // Очищаем контейнер
        if (this.container && this.wrapper) {
            this.container.removeChild(this.wrapper);
        }
    }
}

// Экспорт для использования в других файлах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DateSelector;
}