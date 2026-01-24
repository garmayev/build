/**
 * Компонент выбора времени с выпадающим списком
 *
 * @class TimeSelector
 * @description Представляет собой поле выбора времени с выпадающим списком.
 * Поддерживает настройку интервалов, ограничение по времени, блокировку пунктов и интеграцию с другими компонентами.
 *
 * @example
 * // Базовое использование
 * const timeSelector = new TimeSelector('#time-selector-container', {
 *     initialTime: '09:00',
 *     intervals: 30,
 *     onChange: (time) => console.log('Время изменено:', time)
 * });
 *
 * @example
 * // Расширенное использование с кастомизацией
 * const timeSelector = new TimeSelector('#time-selector-container', {
 *     initialTime: '09:00',
 *     intervals: 15,
 *     startTime: '08:00',
 *     endTime: '20:00',
 *     format24h: true,
 *     showCurrentTime: true,
 *     blockedTimes: ['10:30', '11:00', '14:00'], // Заблокированные времена
 *     onChange: (time) => {
 *         console.log('Выбрано время:', time);
 *         // Обновить расписание и т.д.
 *     },
 *     dropdownOptions: {
 *         maxHeight: '200px',
 *         showScrollbar: true,
 *         highlightSelected: true
 *     }
 * });
 *
 * // Программное управление
 * timeSelector.setTime('14:30');
 * const currentTime = timeSelector.getTime();
 * timeSelector.updateCurrentTime(); // Обновить "текущее" время
 *
 * // Блокировка/разблокировка времен
 * timeSelector.setBlockedTimes(['10:00', '11:30', '15:00']); // Заблокировать
 * timeSelector.addBlockedTime('16:00'); // Заблокировать одно время
 * timeSelector.removeBlockedTime('11:30'); // Разблокировать время
 * timeSelector.clearBlockedTimes(); // Разблокировать все
 *
 * // Обновление опций
 * timeSelector.updateOptions({
 *     intervals: 60,
 *     startTime: '07:00',
 *     dropdownOptions: {
 *         maxHeight: '300px'
 *     }
 * });
 *
 * // Уничтожение компонента при необходимости
 * timeSelector.destroy();
 */
class TimeSelector {
    /**
     * Создает экземпляр компонента выбора времени
     *
     * @constructor
     * @param {string} containerId - CSS-селектор контейнера для компонента
     * @param {Object} [options={}] - Опции конфигурации компонента
     * @param {string} [options.initialTime=''] - Начальное время в формате HH:MM
     * @param {number} [options.intervals=30] - Интервал времени в минутах (15, 30, 60 и т.д.)
     * @param {string} [options.startTime='00:00'] - Начало временного диапазона
     * @param {string} [options.endTime='23:59'] - Конец временного диапазона
     * @param {boolean} [options.format24h=true] - Использовать 24-часовой формат
     * @param {boolean} [options.showCurrentTime=true] - Показывать текущее время в списке
     * @param {string[]} [options.blockedTimes=[]] - Массив заблокированных времен в формате HH:MM
     * @param {Function} [options.onChange=null] - Callback-функция при изменении времени
     * @param {Object} [options.dropdownOptions={}] - Опции для выпадающего списка
     * @param {string} [options.dropdownOptions.maxHeight='250px'] - Максимальная высота списка
     * @param {boolean} [options.dropdownOptions.showScrollbar=true] - Показывать полосу прокрутки
     * @param {boolean} [options.dropdownOptions.highlightSelected=true] - Подсвечивать выбранное время
     *
     * @throws {Error} Если контейнер не найден
     */
    constructor(containerId, options = {}) {
        this.container = document.querySelector(containerId);

        // Проверяем наличие контейнера
        if (!this.container) {
            throw new Error(`Контейнер с селектором "${containerId}" не найден`);
        }

        this.options = {
            initialTime: '',
            intervals: 30,
            startTime: '00:00',
            endTime: '23:59',
            format24h: true,
            showCurrentTime: true,
            blockedTimes: [],
            onChange: null,
            dropdownOptions: {
                maxHeight: '250px',
                showScrollbar: true,
                highlightSelected: true
            },
            ...options
        };

        this.currentTime = this.options.initialTime;
        this.blockedTimes = new Set(this.options.blockedTimes);
        this.timeList = this.generateTimeList();
        this.dropdownVisible = false;
        this.dropdownElement = null;
        this.dropdownInner = null;

        this.init();
    }

    /**
     * Инициализирует компонент и создает DOM-структуру
     * @private
     */
    init() {
        // Создаем обертку с position: relative для правильного позиционирования dropdown
        this.wrapper = document.createElement('div');
        this.wrapper.className = 'time-selector-wrapper';

        // Создаем основные элементы
        this.timeSelector = document.createElement('div');
        this.timeSelector.className = 'time-selector';

        // Контейнер для отображения времени
        this.timeDisplay = document.createElement('div');
        this.timeDisplay.className = 'time-display';

        this.timeText = document.createElement('span');
        this.timeText.className = 'time-text';
        this.timeSelector.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        // Иконка стрелки вниз (опционально)
        this.arrowIcon = document.createElement('span');
        this.arrowIcon.className = 'time-arrow';
        this.arrowIcon.innerHTML = '▼';
        this.arrowIcon.addEventListener('click', (e) => {
            e.stopPropagation();
            this.toggleDropdown();
        });

        this.timeDisplay.appendChild(this.timeText);
        this.timeDisplay.appendChild(this.arrowIcon);
        this.timeSelector.appendChild(this.timeDisplay);
        this.wrapper.appendChild(this.timeSelector);
        this.container.appendChild(this.wrapper);

        // Обновляем отображение времени
        this.updateTimeDisplay();

        // Обработчик клика на документе для закрытия dropdown
        this.documentClickListener = (e) => {
            if (this.dropdownVisible && this.dropdownElement) {
                // Проверяем, кликнули ли мы на сам dropdown или на элементы time-selector
                const clickedOnDropdown = this.dropdownElement.contains(e.target);
                const clickedOnTimeSelector = this.wrapper.contains(e.target);

                if (!clickedOnDropdown && !clickedOnTimeSelector) {
                    this.hideDropdown();
                }
            }
        };

        // Обработчик для ресайза - скрываем dropdown при изменении размера окна
        this.resizeHandler = () => {
            if (this.dropdownVisible) {
                this.hideDropdown();
            }
        };

        // Обработчик для скролла - скрываем dropdown при начале скролла
        this.scrollHandler = () => {
            if (this.dropdownVisible) {
                this.hideDropdown();
            }
        };

        // Добавляем обработчики
        document.addEventListener('click', this.documentClickListener);
        window.addEventListener('resize', this.resizeHandler);
        window.addEventListener('scroll', this.scrollHandler, { passive: true });
    }

    /**
     * Генерирует список временных интервалов
     * @private
     * @returns {Array} Массив объектов времени
     */
    generateTimeList() {
        const timeList = [];
        const intervals = this.options.intervals;

        let [startHour, startMinute] = this.options.startTime.split(':').map(Number);
        let [endHour, endMinute] = this.options.endTime.split(':').map(Number);

        // Начинаем с ближайшего интервала от начального времени
        const startMinutes = startHour * 60 + startMinute;
        const endMinutes = endHour * 60 + endMinute;

        for (let minutes = startMinutes; minutes <= endMinutes; minutes += intervals) {
            const hour = Math.floor(minutes / 60);
            const minute = minutes % 60;

            const timeString = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
            const displayTime = this.formatTimeForDisplay(hour, minute);
            const isBlocked = this.blockedTimes.has(timeString);

            timeList.push({
                value: timeString,
                display: displayTime,
                hour: hour,
                minute: minute,
                blocked: isBlocked
            });
        }

        return timeList;
    }

    /**
     * Форматирует время для отображения
     * @private
     * @param {number} hour - Часы
     * @param {number} minute - Минуты
     * @returns {string} Отформатированное время
     */
    formatTimeForDisplay(hour, minute) {
        if (this.options.format24h) {
            return `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
        } else {
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${minute.toString().padStart(2, '0')} ${period}`;
        }
    }

    /**
     * Обновляет отображение текущего времени в компоненте
     * @private
     * @fires TimeSelector#change
     */
    updateTimeDisplay() {
        if (this.currentTime) {
            const [hour, minute] = this.currentTime.split(':').map(Number);
            this.timeText.textContent = this.formatTimeForDisplay(hour, minute);
            this.timeText.classList.remove('time-text-placeholder');
        } else {
            this.timeText.textContent = 'Выберите время';
            this.timeText.classList.add('time-text-placeholder');
        }

        // Вызываем callback при изменении времени
        if (this.options.onChange && this.currentTime) {
            /**
             * Событие изменения времени
             * @event TimeSelector#change
             * @type {string}
             */
            this.options.onChange(this.currentTime);
        }
    }

    /**
     * Создает выпадающий список с вариантами времени
     * @private
     * @returns {HTMLElement} Элемент dropdown
     */
    createDropdown() {
        // Удаляем старый dropdown, если есть
        if (this.dropdownElement) {
            this.dropdownElement.remove();
        }

        // Создаем новый dropdown в body
        this.dropdownElement = document.createElement('div');
        this.dropdownElement.className = 'time-dropdown time-selector-dropdown';
        this.dropdownElement.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: transparent;
            z-index: 10050;
            pointer-events: none;
        `;
        this.dropdownElement.addEventListener('click', this.hideDropdown.bind(this));

        // Создаем внутренний контейнер для списка
        const dropdownInner = document.createElement('div');
        dropdownInner.className = 'time-dropdown-inner';
        dropdownInner.style.cssText = `
            position: absolute;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            max-height: ${this.options.dropdownOptions.maxHeight};
            pointer-events: auto;
            z-index: 10051;
        `;

        // Добавляем скроллбар если нужно
        if (!this.options.dropdownOptions.showScrollbar) {
            dropdownInner.style.scrollbarWidth = 'none';
            dropdownInner.style.msOverflowStyle = 'none';
        }

        // Создаем список времени
        const timeListContainer = document.createElement('ul');
        timeListContainer.className = 'time-list';

        // Добавляем опцию "Текущее время" если нужно
        if (this.options.showCurrentTime) {
            const currentTimeItem = document.createElement('li');
            currentTimeItem.className = 'time-list-item current-time';
            currentTimeItem.textContent = 'Текущее время';
            currentTimeItem.addEventListener('click', (e) => {
                e.stopPropagation();
                this.setCurrentTime();
                this.hideDropdown();
            });
            timeListContainer.appendChild(currentTimeItem);

            // Добавляем разделитель
            const separator = document.createElement('hr');
            separator.className = 'time-separator';
            timeListContainer.appendChild(separator);
        }

        // Добавляем все временные интервалы
        this.timeList.forEach((timeObj) => {
            const timeItem = document.createElement('li');
            timeItem.className = 'time-list-item';
            timeItem.textContent = timeObj.display;
            timeItem.dataset.value = timeObj.value;

            // Добавляем класс для заблокированных элементов
            if (timeObj.blocked) {
                timeItem.classList.add('blocked');
                timeItem.title = 'Время недоступно';
            }

            // Подсвечиваем выбранное время
            if (this.options.dropdownOptions.highlightSelected &&
                timeObj.value === this.currentTime) {
                timeItem.classList.add('selected');
            }

            timeItem.addEventListener('click', (e) => {
                e.stopPropagation();
                if (!timeObj.blocked) {
                    this.handleTimeSelect(timeObj.value);
                    this.hideDropdown();
                }
            });

            timeListContainer.appendChild(timeItem);
        });

        dropdownInner.appendChild(timeListContainer);
        this.dropdownElement.appendChild(dropdownInner);
        document.body.appendChild(this.dropdownElement);

        // Сохраняем ссылку на внутренний элемент для позиционирования
        this.dropdownInner = dropdownInner;

        return dropdownInner;
    }

    /**
     * Показывает выпадающий список
     * @private
     */
    showDropdown() {
        // Скрываем другие открытые dropdown
        document.querySelectorAll('.time-dropdown').forEach(dropdown => {
            if (dropdown !== this.dropdownElement) {
                dropdown.style.display = 'none';
            }
        });

        // Создаем dropdown
        this.createDropdown();
        this.dropdownElement.style.display = 'block';
        this.dropdownVisible = true;

        // Позиционируем dropdown относительно timeText
        this.positionDropdown();

        // Добавляем класс активного состояния
        this.timeDisplay.classList.add('active');
        this.arrowIcon.classList.add('active');
    }

    /**
     * Позиционирует dropdown относительно элемента отображения времени
     * @private
     */
    positionDropdown() {
        const timeTextRect = this.timeText.getBoundingClientRect();
        const dropdownRect = this.dropdownInner.getBoundingClientRect();

        // Рассчитываем позицию
        let top = timeTextRect.bottom + window.scrollY + 5;
        let left = timeTextRect.left + window.scrollX;

        // Проверяем, не выходит ли dropdown за правый край экрана
        if (left + dropdownRect.width > window.innerWidth) {
            left = window.innerWidth - dropdownRect.width - 10;
        }

        // Проверяем, не выходит ли dropdown за нижний край экрана
        if (top + dropdownRect.height > window.innerHeight + window.scrollY) {
            top = timeTextRect.top + window.scrollY - dropdownRect.height - 5;
        }

        // Проверяем, не выходит ли dropdown за левый край экрана
        if (left < 10) {
            left = 10;
        }

        // Применяем позицию
        this.dropdownInner.style.top = top + 'px';
        this.dropdownInner.style.left = left + 'px';
    }

    /**
     * Скрывает выпадающий список
     * @private
     */
    hideDropdown() {
        if (this.dropdownElement) {
            this.dropdownElement.style.display = 'none';
            this.dropdownElement.remove();
            this.dropdownElement = null;
            this.dropdownInner = null;
        }
        this.dropdownVisible = false;

        // Убираем класс активного состояния
        this.timeDisplay.classList.remove('active');
        this.arrowIcon.classList.remove('active');
    }

    /**
     * Переключает видимость выпадающего списка
     *
     * @example
     * timeSelector.toggleDropdown(); // Открыть/закрыть список
     */
    toggleDropdown() {
        if (this.dropdownVisible) {
            this.hideDropdown();
        } else {
            this.showDropdown();
        }
    }

    /**
     * Обрабатывает выбор времени из списка
     * @private
     * @param {string} time - Выбранное время в формате HH:MM
     */
    handleTimeSelect(time) {
        // Проверяем, не заблокировано ли время
        if (this.blockedTimes.has(time)) {
            console.warn(`Время ${time} заблокировано`);
            return;
        }

        this.currentTime = time;
        this.updateTimeDisplay();

        if (this.options.onChange) {
            this.options.onChange(this.currentTime);
        }
    }

    /**
     * Устанавливает текущее время
     * @private
     */
    setCurrentTime() {
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();

        // Находим ближайший доступный интервал
        let nearestTime = null;
        let minDiff = Infinity;

        this.timeList.forEach(timeObj => {
            // Пропускаем заблокированные времена
            if (timeObj.blocked) {
                return;
            }

            const timeInMinutes = timeObj.hour * 60 + timeObj.minute;
            const currentInMinutes = currentHour * 60 + currentMinute;
            const diff = Math.abs(timeInMinutes - currentInMinutes);

            if (diff < minDiff) {
                minDiff = diff;
                nearestTime = timeObj.value;
            }
        });

        if (nearestTime) {
            this.currentTime = nearestTime;
            this.updateTimeDisplay();

            if (this.options.onChange) {
                this.options.onChange(this.currentTime);
            }
        } else {
            console.warn('Нет доступного времени для установки текущего');
        }
    }

    /**
     * Устанавливает время программно
     *
     * @param {string} time - Новое время в формате HH:MM
     *
     * @example
     * timeSelector.setTime('14:30');
     * timeSelector.setTime('09:00');
     */
    setTime(time) {
        // Проверяем формат времени
        if (typeof time !== 'string' || !time.match(/^\d{2}:\d{2}$/)) {
            console.error('Неверный формат времени. Используйте формат HH:MM');
            return;
        }

        const [hour, minute] = time.split(':').map(Number);

        // Проверяем валидность времени
        if (hour < 0 || hour > 23 || minute < 0 || minute > 59) {
            console.error('Неверное время. Часы должны быть от 0 до 23, минуты от 0 до 59');
            return;
        }

        // Проверяем, что время находится в доступном списке
        const timeExists = this.timeList.some(timeObj => timeObj.value === time);

        if (!timeExists) {
            console.error('Время не доступно в текущих настройках интервалов');
            return;
        }

        // Проверяем, не заблокировано ли время
        if (this.blockedTimes.has(time)) {
            console.error(`Время ${time} заблокировано`);
            return;
        }

        this.currentTime = time;
        this.updateTimeDisplay();
    }

    /**
     * Возвращает текущее выбранное время
     *
     * @returns {string} Текущее время в формате HH:MM
     *
     * @example
     * const currentTime = timeSelector.getTime();
     * console.log('Текущее время:', currentTime);
     */
    getTime() {
        return this.currentTime;
    }

    /**
     * Устанавливает список заблокированных времен
     *
     * @param {string[]} times - Массив времен для блокировки в формате HH:MM
     *
     * @example
     * // Заблокировать несколько времен
     * timeSelector.setBlockedTimes(['10:00', '11:30', '15:00']);
     */
    setBlockedTimes(times) {
        this.blockedTimes = new Set(times);
        this.updateTimeList();

        // Проверяем, не заблокировано ли текущее время
        if (this.currentTime && this.blockedTimes.has(this.currentTime)) {
            this.currentTime = '';
            this.updateTimeDisplay();
        }

        // Обновляем dropdown если он открыт
        if (this.dropdownVisible) {
            this.showDropdown();
        }
    }

    /**
     * Добавляет одно время в список заблокированных
     *
     * @param {string} time - Время для блокировки в формате HH:MM
     *
     * @example
     * timeSelector.addBlockedTime('16:00');
     */
    addBlockedTime(time) {
        this.blockedTimes.add(time);
        this.updateTimeList();

        // Проверяем, не заблокировано ли текущее время
        if (this.currentTime === time) {
            this.currentTime = '';
            this.updateTimeDisplay();
        }

        // Обновляем dropdown если он открыт
        if (this.dropdownVisible) {
            this.showDropdown();
        }
    }

    /**
     * Удаляет время из списка заблокированных
     *
     * @param {string} time - Время для разблокировки в формате HH:MM
     *
     * @example
     * timeSelector.removeBlockedTime('11:30');
     */
    removeBlockedTime(time) {
        this.blockedTimes.delete(time);
        this.updateTimeList();

        // Обновляем dropdown если он открыт
        if (this.dropdownVisible) {
            this.showDropdown();
        }
    }

    /**
     * Очищает все заблокированные времена
     *
     * @example
     * timeSelector.clearBlockedTimes();
     */
    clearBlockedTimes() {
        this.blockedTimes.clear();
        this.updateTimeList();

        // Обновляем dropdown если он открыт
        if (this.dropdownVisible) {
            this.showDropdown();
        }
    }

    /**
     * Возвращает список заблокированных времен
     *
     * @returns {string[]} Массив заблокированных времен
     *
     * @example
     * const blocked = timeSelector.getBlockedTimes();
     * console.log('Заблокированные времена:', blocked);
     */
    getBlockedTimes() {
        return Array.from(this.blockedTimes);
    }

    /**
     * Проверяет, заблокировано ли время
     *
     * @param {string} time - Время для проверки в формате HH:MM
     * @returns {boolean} true если время заблокировано
     *
     * @example
     * const isBlocked = timeSelector.isTimeBlocked('10:00');
     */
    isTimeBlocked(time) {
        return this.blockedTimes.has(time);
    }

    /**
     * Обновляет список временных интервалов на основе текущих настроек
     *
     * @example
     * timeSelector.updateTimeList();
     */
    updateTimeList() {
        this.timeList = this.generateTimeList();

        // Если текущее время больше не доступно, сбрасываем его
        if (this.currentTime) {
            const timeExists = this.timeList.some(timeObj =>
                timeObj.value === this.currentTime && !timeObj.blocked
            );
            if (!timeExists) {
                this.currentTime = '';
                this.updateTimeDisplay();
            }
        }
    }

    /**
     * Обновляет опции компонента
     *
     * @param {Object} newOptions - Новые опции (частичное обновление)
     *
     * @example
     * timeSelector.updateOptions({
     *     intervals: 60,
     *     startTime: '07:00',
     *     format24h: false,
     *     blockedTimes: ['10:00', '14:00'],
     *     dropdownOptions: {
     *         maxHeight: '300px'
     *     }
     * });
     */
    updateOptions(newOptions) {
        const oldOptions = { ...this.options };
        this.options = { ...this.options, ...newOptions };

        if (newOptions.dropdownOptions) {
            this.options.dropdownOptions = {
                ...this.options.dropdownOptions,
                ...newOptions.dropdownOptions
            };
        }

        // Обновляем заблокированные времена если они изменились
        if (newOptions.blockedTimes !== undefined) {
            this.blockedTimes = new Set(newOptions.blockedTimes);
        }

        // Если изменились настройки интервалов или диапазона, обновляем список
        if (newOptions.intervals !== oldOptions.intervals ||
            newOptions.startTime !== oldOptions.startTime ||
            newOptions.endTime !== oldOptions.endTime ||
            newOptions.format24h !== oldOptions.format24h) {
            this.updateTimeList();
        }

        this.updateTimeDisplay();
    }

    /**
     * Уничтожает компонент, очищая все обработчики и DOM-элементы
     *
     * @example
     * timeSelector.destroy();
     */
    destroy() {
        this.hideDropdown();
        document.removeEventListener('click', this.documentClickListener);
        window.removeEventListener('resize', this.resizeHandler);
        window.removeEventListener('scroll', this.scrollHandler);

        // Очищаем контейнер
        if (this.container && this.wrapper) {
            this.container.removeChild(this.wrapper);
        }
    }
}

// Экспорт для использования в других файлах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TimeSelector;
}