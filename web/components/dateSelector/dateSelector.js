class DateSelector {
    /**
     * Создает экземпляр компонента выбора даты
     */
    constructor(containerId, options = {}) {
        this.container = document.querySelector(containerId);

        if (!this.container) {
            throw new Error(`Контейнер с селектором "${containerId}" не найден`);
        }

        this.today = DateUtils.getToday();
        this.options = {
            initialDate: new Date(),
            useLeftRightButtons: true,
            onChange: null,
            showValidationErrors: true, // Новая опция для показа ошибок
            errorMessage: null, // Сообщение об ошибке
            calendarOptions: {
                showOtherMonthsDays: true,
                showNavigation: true,
                allowPastDates: false,
                locale: 'ru-RU'
            },
            ...options
        };

        const inputDate = DateUtils.setStartOfDay(new Date(this.options.initialDate));

        if (inputDate < this.today && !this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
        } else {
            this.currentDate = DateUtils.cloneDate(inputDate);
        }

        this.calendarVisible = false;
        this.calendarPopup = null;
        this.calendarInner = null;
        this.errorElement = null;
        this.originalInput = null;
        this.lastScrollPosition = window.scrollY;
        this.scrollTimeout = null;

        this.init();
    }

    init() {
        // Находим оригинальный input (если есть)
        this.originalInput = this.container.querySelector('input[type="text"], input[type="hidden"]');

        this.wrapper = document.createElement('div');
        this.wrapper.className = 'date-selector-wrapper';

        // Добавляем класс ошибки если есть
        // if (this.options.errorMessage) {
        //     this.wrapper.classList.add('has-error');
        // }

        this.dateSelector = document.createElement('div');
        this.dateSelector.className = 'date-selector';

        if (this.options.useLeftRightButtons) {
            this.prevBtn = document.createElement('button');
            this.prevBtn.className = 'date-arrow date-prev';
            this.prevBtn.innerHTML = '<';
            this.prevBtn.setAttribute('type', 'button');
            this.prevBtn.setAttribute('role', 'button');
            this.prevBtn.setAttribute('aria-label', 'Предыдущий день');
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.changeDate(-1);
            });
        }

        this.dateDisplay = document.createElement('div');
        this.dateDisplay.className = 'date-display';

        this.dateText = document.createElement('button');
        this.dateText.className = 'date-text';
        this.dateText.setAttribute('type', 'button');
        this.dateText.setAttribute('aria-label', 'Выбрать дату');

        // Добавляем класс ошибки если есть
        // if (this.options.errorMessage) {
        //     this.dateText.classList.add('has-error');
        // }

        this.dateText.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleCalendar();
        });

        // Добавляем обработчики клавиатуры для доступности
        this.dateText.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.toggleCalendar();
            }
            if (e.key === 'ArrowLeft' && this.options.useLeftRightButtons) {
                e.preventDefault();
                this.changeDate(-1);
            }
            if (e.key === 'ArrowRight' && this.options.useLeftRightButtons) {
                e.preventDefault();
                this.changeDate(1);
            }
        });

        this.dateDisplay.appendChild(this.dateText);

        if (this.options.useLeftRightButtons) {
            this.nextBtn = document.createElement('button');
            this.nextBtn.className = 'date-arrow date-next';
            this.nextBtn.innerHTML = '>';
            this.nextBtn.setAttribute('type', 'button');
            this.nextBtn.setAttribute('role', 'button');
            this.nextBtn.setAttribute('aria-label', 'Следующий день');
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

        // Добавляем элемент для отображения ошибок
        if (this.options.showValidationErrors) {
            this.errorElement = document.createElement('div');
            this.errorElement.className = 'date-selector-error';
            this.errorElement.setAttribute('role', 'alert');
            this.errorElement.setAttribute('aria-live', 'polite');

            if (this.options.errorMessage) {
                this.errorElement.textContent = this.options.errorMessage;
                this.errorElement.style.display = 'block';
            } else {
                this.errorElement.style.display = 'none';
            }

            this.wrapper.appendChild(this.errorElement);
        }

        // Вставляем после оригинального input или заменяем контейнер
        if (this.originalInput && this.originalInput.parentNode) {
            this.originalInput.style.display = 'none';
            this.originalInput.parentNode.insertBefore(this.wrapper, this.originalInput.nextSibling);
        } else {
            this.container.appendChild(this.wrapper);
        }

        // Обновляем отображение даты
        this.updateDateDisplay();
        this.updateButtonsState();
        this.syncWithOriginalInput();

        // Обработчики событий
        this.documentClickListener = (e) => {
            if (this.calendarVisible && this.calendarPopup) {
                const clickedOnCalendar = this.calendarPopup.contains(e.target);
                const clickedOnDateSelector = this.wrapper.contains(e.target);

                if (!clickedOnCalendar && !clickedOnDateSelector) {
                    this.hideCalendar();
                }
            }
        };

        this.scrollHandler = () => {
            if (this.calendarVisible) {
                const currentScrollPosition = window.scrollY;
                const scrollDelta = Math.abs(currentScrollPosition - this.lastScrollPosition);

                if (scrollDelta > 5) {
                    this.hideCalendar();
                }

                this.lastScrollPosition = currentScrollPosition;
            }
        };

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

    updateDateDisplay() {
        // Форматируем дату в формате DD.MM.YYYY
        const formattedDate = DateUtils.formatDate(this.currentDate, 'DD.MM.YYYY');
        this.dateText.textContent = formattedDate;

        // Обновляем оригинальный input если есть
        this.syncWithOriginalInput();

        // Вызываем callback при изменении даты
        if (this.options.onChange) {
            this.options.onChange(this.currentDate, formattedDate);
        }

        // Очищаем ошибку при изменении значения
        this.clearError();
    }

    syncWithOriginalInput() {
        if (this.originalInput) {
            // Преобразуем дату в формат для input
            const dateString = DateUtils.formatDate(this.currentDate, 'YYYY-MM-DD');

            // В зависимости от типа input устанавливаем значение
            if (this.originalInput.type === 'text') {
                // Для текстового поля - формат DD.MM.YYYY
                this.originalInput.value = DateUtils.formatDate(this.currentDate, 'DD.MM.YYYY');
            } else {
                // Для hidden поля - формат YYYY-MM-DD
                this.originalInput.value = dateString;
            }

            // Триггерим событие change на оригинальном input
            this.originalInput.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    updateButtonsState() {
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

    changeDate(days) {
        const newDate = DateUtils.addDays(this.currentDate, days);
        const newDateStart = DateUtils.setStartOfDay(newDate);

        if (newDateStart >= this.today || this.options.calendarOptions.allowPastDates) {
            this.currentDate = newDate;
        } else if (!this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
        }

        this.updateDateDisplay();
        this.updateButtonsState();
        this.updateCalendarDate();
    }

    toggleCalendar() {
        if (this.calendarVisible) {
            this.hideCalendar();
        } else {
            this.showCalendar();
        }
    }

    createCalendarPopup() {
        if (this.calendarPopup) {
            this.calendarPopup.remove();
        }

        this.calendarPopup = document.createElement('div');
        this.calendarPopup.className = 'calendar-popup date-selector-calendar-popup';
        this.calendarPopup.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 1050;
            pointer-events: none;
        `;

        this.calendarInner = document.createElement('div');
        this.calendarInner.className = 'calendar-popup-inner';
        this.calendarInner.style.cssText = `
            position: absolute;
            pointer-events: auto;
        `;

        this.calendarPopup.appendChild(this.calendarInner);
        document.body.appendChild(this.calendarPopup);

        const calendarOptions = {
            minDate: this.options.calendarOptions.allowPastDates ? null : this.today,
            onDateSelect: (date) => this.handleCalendarDateSelect(date),
            ...this.options.calendarOptions
        };

        // Используем глобальный объект Calendar или window.Calendar
        const CalendarClass = window.Calendar || Calendar;
        this.calendar = new CalendarClass(
            this.calendarInner,
            calendarOptions
        );

        this.calendar.setDate(this.currentDate);

        this.calendarInner.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        return this.calendarInner;
    }

    showCalendar() {
        if (document.querySelector('.modal.show')) {
            console.warn('Календарь не может быть открыт при активном модальном окне');
            return;
        }

        if (this.calendarVisible && this.calendarPopup) {
            this.hideCalendar();
            return;
        }

        document.querySelectorAll('.calendar-popup').forEach(popup => {
            if (popup !== this.calendarPopup) {
                popup.remove();
            }
        });

        this.createCalendarPopup();
        this.calendarPopup.style.display = 'block';
        this.calendarVisible = true;
        this.lastScrollPosition = window.scrollY;
        this.positionCalendar();
    }

    positionCalendar() {
        const dateTextRect = this.dateText.getBoundingClientRect();
        const calendarContainer = this.calendarInner.querySelector('.calendar-container');

        if (!calendarContainer) return;

        calendarContainer.style.width = '220px';
        calendarContainer.style.height = 'auto';

        const calendarRect = calendarContainer.getBoundingClientRect();

        let top = dateTextRect.bottom + window.scrollY + 5;
        let left = dateTextRect.left + window.scrollX;

        if (left + calendarRect.width > window.innerWidth) {
            left = window.innerWidth - calendarRect.width - 10;
        }

        if (top + calendarRect.height > window.innerHeight + window.scrollY) {
            top = dateTextRect.top + window.scrollY - calendarRect.height - 5;
        }

        if (left < 10) {
            left = 10;
        }

        this.calendarInner.style.top = top + 'px';
        this.calendarInner.style.left = left + 'px';

        if (this.calendar) {
            this.calendar.setDate(this.currentDate);
        }
        this.calendarVisible = true;
    }

    hideCalendar() {
        if (this.calendarPopup) {
            this.calendarPopup.style.display = 'none';
            this.calendarPopup.remove();
            this.calendarPopup = null;
            this.calendarInner = null;
        }
        this.calendarVisible = false;

        if (this.scrollTimeout) {
            clearTimeout(this.scrollTimeout);
            this.scrollTimeout = null;
        }
    }

    handleCalendarDateSelect(date) {
        this.currentDate = DateUtils.cloneDate(date);
        this.updateDateDisplay();
        this.updateButtonsState();
        this.hideCalendar();

        if (this.options.onChange) {
            const formattedDate = DateUtils.formatDate(this.currentDate, 'DD.MM.YYYY');
            this.options.onChange(this.currentDate, formattedDate);
        }
    }

    updateCalendarDate() {
        if (this.calendar) {
            this.calendar.setDate(this.currentDate);
        }
    }

    /**
     * Устанавливает ошибку валидации
     * @param {string} message - Сообщение об ошибке
     */
    setError(message) {
        if (this.errorElement) {
            this.errorElement.textContent = message;
            this.errorElement.style.display = 'block';
        }

        this.wrapper.classList.add('has-error');
        this.dateText.classList.add('has-error');

        this.options.errorMessage = message;
    }

    /**
     * Очищает ошибку валидации
     */
    clearError() {
        if (this.errorElement) {
            this.errorElement.textContent = '';
            this.errorElement.style.display = 'none';
        }

        this.wrapper.classList.remove('has-error');
        this.dateText.classList.remove('has-error');

        this.options.errorMessage = null;
    }

    setDate(date) {
        const newDate = DateUtils.cloneDate(date);
        const newDateStart = DateUtils.setStartOfDay(newDate);

        if (newDateStart >= this.today || this.options.calendarOptions.allowPastDates) {
            this.currentDate = newDate;
        } else if (!this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
        }

        this.updateDateDisplay();
        this.updateButtonsState();
        this.updateCalendarDate();
    }

    getDate() {
        return DateUtils.cloneDate(this.currentDate);
    }

    getFormattedDate(format = 'DD.MM.YYYY') {
        return DateUtils.formatDate(this.currentDate, format);
    }

    updateToday() {
        const oldToday = DateUtils.cloneDate(this.today);
        this.today = DateUtils.getToday();

        const currentDateStart = DateUtils.setStartOfDay(this.currentDate);

        if (currentDateStart < this.today && !this.options.calendarOptions.allowPastDates) {
            this.currentDate = DateUtils.cloneDate(this.today);
            this.updateDateDisplay();
        }

        if (this.calendar && !this.options.calendarOptions.allowPastDates) {
            this.calendar.setMinDate(this.today);
        }

        this.updateButtonsState();

        return oldToday.getTime() !== this.today.getTime();
    }

    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };

        if (newOptions.calendarOptions) {
            this.options.calendarOptions = {
                ...this.options.calendarOptions,
                ...newOptions.calendarOptions
            };
        }

        // Обновляем ошибку если передана
        if (newOptions.errorMessage !== undefined) {
            if (newOptions.errorMessage) {
                this.setError(newOptions.errorMessage);
            } else {
                this.clearError();
            }
        }

        this.updateDateDisplay();
        this.updateButtonsState();
    }

    destroy() {
        this.hideCalendar();
        document.removeEventListener('click', this.documentClickListener);
        window.removeEventListener('scroll', this.scrollHandler);
        window.removeEventListener('resize', this.resizeHandler);

        // Восстанавливаем оригинальный input
        if (this.originalInput) {
            this.originalInput.style.display = '';
        }

        // Удаляем обертку
        if (this.wrapper && this.wrapper.parentNode) {
            this.wrapper.parentNode.removeChild(this.wrapper);
        }
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = DateSelector;
}