// calendar.js
class Calendar {
    constructor(options = {}) {
        this.config = {
            container: document.body,
            language: 'ru',
            authToken: '',
            apiUrl: '',
            monthNames: [
                'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь',
                'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'
            ],
            translations: {
                'calendar.back': 'Назад',
                'calendar.next': 'Вперед',
                'calendar.search_text': 'Поиск...',
                'calendar.total': 'Итого',
                'calendar.mode_finance': 'Финансовый режим',
                'calendar.order': 'Заказ',
                'calendar.is_paid': 'Оплачено',
                'calendar.is_not_paid': 'Не оплачено',
                'calendar.cost': 'Стоимость',
                'calendar.popup_count': 'Количество',
                'calendar.popup_status': 'Статус',
                'calendar.amount': 'Сумма',
                'calendar.start_time': 'Начало',
                'calendar.stop_time': 'Конец',
                'calendar.pay': 'Оплатить',
                'calendar.cancel': 'Отмена',
                'calendar.add': 'Добавить',
                'not-set': 'Не установлено',
                'calendar.days': 'дней',
                'calendar.order_duration': 'Длительность заказа',
                'calendar.hours': 'Часы работы',
                'calendar.see_details': 'Подробнее',
                'calendar.no_data': 'Нет данных'
            },
            events: {},
            templates: {},
            ajax: {
                enabled: true,
                endpoints: {
                    getData: '/api/calendar/data',
                    getPrice: '/api/price',
                    setHours: '/api/hours',
                    markPaid: '/api/hours/paid',
                    markOrderPaid: '/api/orders/paid'
                },
                request: null,
                customRequest: null
            },
            staticData: null,
            classes: {
                container: 'calendar-container',
                table: 'calendar-table',
                header: 'calendar-header',
                body: 'calendar-body',
                footer: 'calendar-footer',
                modeToggle: 'mode-toggle',
                searchInput: 'search-input'
            },
            modal: {
                external: false,
                container: null,
                template: null
            },
            display: {
                showOrders: true,
                showHours: true,
                mergeCells: true,
                colorByType: true
            }
        };

        this.mergeConfig(options);

        if (!this.config.ajax.request) {
            this.config.ajax.request = this.createRequestFunction();
        }

        this.currentDate = new Date();
        this.currentYear = this.currentDate.getFullYear();
        this.currentMonth = this.currentDate.getMonth();
        this.isFinanceMode = false;
        this.data = [];
        this.filteredData = [];
        this.modalData = [];
        this.searchText = '';
        this.hoverPopup = null;
        this.externalModal = null;
        this.currentModal = null;
        this.eventHandlers = new Map(); // Для хранения обработчиков событий

        this.init();
    }

    createRequestFunction() {
        return (url, options) => {
            const defaultOptions = {
                headers: {
                    'Authorization': `Bearer ${this.config.authToken}`,
                    'Content-Type': 'application/json'
                }
            };

            const mergedOptions = {
                ...defaultOptions,
                ...options,
                headers: {
                    ...defaultOptions.headers,
                    ...(options?.headers || {})
                }
            };

            return fetch(url, mergedOptions)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    throw error;
                });
        };
    }

    mergeConfig(options) {
        const deepKeys = ['events', 'templates', 'ajax', 'classes', 'modal', 'display', 'translations'];

        Object.keys(options).forEach(key => {
            if (deepKeys.includes(key)) {
                if (!this.config[key]) this.config[key] = {};
                Object.assign(this.config[key], options[key]);
            } else {
                this.config[key] = options[key];
            }
        });
    }

    init() {
        this.createContainer();
        this.render();
        this.loadData();
    }

    createContainer() {
        if (!this.config.container || !(this.config.container instanceof Element)) {
            console.error('Invalid container provided');
            return;
        }

        this.config.container.innerHTML = '';
        if (this.config.classes.container) {
            this.config.container.classList.add(this.config.classes.container);
        }

        this.container = document.createElement('div');
        this.container.id = 'my-calendar';
        this.config.container.appendChild(this.container);
    }

    async loadData() {
        if (this.config.ajax.enabled) {
            await this.loadDataFromAPI();
        } else if (this.config.staticData) {
            this.data = this.config.staticData;
            this.filteredData = [...this.data];
            this.triggerEvent('onDataLoad', this.data);
            this.renderTable();
        }
    }

    async loadDataFromAPI() {
        const endpoint = this.config.ajax.endpoints.getData;

        if (!endpoint) {
            console.error('API endpoint for getData is not configured');
            this.triggerEvent('onDataError', new Error('API endpoint for getData is not configured'));
            this.showErrorMessage('API endpoint not configured');
            return;
        }

        const url = endpoint.startsWith('http') ?
            `${endpoint}?year=${this.currentYear}&month=${this.currentMonth + 1}` :
            `${this.config.apiUrl}${endpoint}?year=${this.currentYear}&month=${this.currentMonth + 1}`;

        try {
            let response;
            if (this.config.ajax.customRequest) {
                response = await this.config.ajax.customRequest(url, { method: 'GET' });
            } else {
                response = await this.config.ajax.request(url, { method: 'GET' });
            }

            this.data = Array.isArray(response) ? response : [];
            this.processData();
            this.filteredData = [...this.data];
            this.triggerEvent('onDataLoad', this.data);
            this.renderTable();
        } catch (error) {
            console.error('Error loading data:', error);
            this.triggerEvent('onDataError', error);
            this.showErrorMessage('Ошибка загрузки данных. Проверьте авторизацию.');
        }
    }

    processData() {
        this.data.forEach(employee => {
            if (!employee) return;

            employee.days = {};

            // Обрабатываем заказы
            if (this.config.display.showOrders && employee.orders) {
                employee.orders.forEach(order => this.processOrder(employee, order));
            }

            // Обрабатываем часы работы
            if (this.config.display.showHours && employee.hours) {
                employee.hours.forEach(hour => this.processHour(employee, hour));
            }

            // Преобразуем дни в массив для удобства
            employee.processedDays = Object.values(employee.days);

            // Рассчитываем итоги
            this.calculateEmployeeTotals(employee);
        });
    }

    processOrder(employee, order) {
        if (!order.start_datetime || !order.finish_datetime) return;

        const startDate = new Date(order.start_datetime);
        const endDate = new Date(order.finish_datetime);
        const currentMonthStart = new Date(this.currentYear, this.currentMonth, 1);
        const currentMonthEnd = new Date(this.currentYear, this.currentMonth + 1, 0);

        // Проверяем, попадает ли заказ в текущий месяц
        if (endDate < currentMonthStart || startDate > currentMonthEnd) return;

        // Определяем даты для обработки
        const processStart = startDate < currentMonthStart ? currentMonthStart : startDate;
        const processEnd = endDate > currentMonthEnd ? currentMonthEnd : endDate;

        // Проходим по всем дням заказа
        const current = new Date(processStart);
        while (current <= processEnd) {
            const dateStr = this.formatDate(current.getDate());
            const dayIndex = current.getDate() - 1;

            if (!employee.days[dateStr]) {
                this.initializeDayData(employee.days, dateStr, dayIndex);
            }

            employee.days[dateStr].orders.push({
                ...order,
                type: 'order',
                isPayed: order.is_payed,
                duration_days: Math.ceil((endDate - startDate) / (1000 * 60 * 60 * 24)) + 1
            });

            employee.days[dateStr].isOrderDay = true;
            employee.days[dateStr].isPayed = employee.days[dateStr].isPayed || order.is_payed;

            current.setDate(current.getDate() + 1);
        }
    }

    processHour(employee, hour) {
        if (!hour.date) return;

        const hourDate = new Date(hour.date);
        const currentMonthStart = new Date(this.currentYear, this.currentMonth, 1);
        const currentMonthEnd = new Date(this.currentYear, this.currentMonth + 1, 0);

        // Проверяем, попадает ли час в текущий месяц
        if (hourDate < currentMonthStart || hourDate > currentMonthEnd) return;

        const dateStr = this.formatDate(hourDate.getDate());
        const dayIndex = hourDate.getDate() - 1;

        if (!employee.days[dateStr]) {
            this.initializeDayData(employee.days, dateStr, dayIndex);
        }

        employee.days[dateStr].hours.push({
            ...hour,
            type: 'hour',
            isPayed: hour.is_payed
        });

        employee.days[dateStr].isHourDay = true;
        employee.days[dateStr].isPayed = employee.days[dateStr].isPayed || hour.is_payed;
    }

    initializeDayData(days, dateStr, dayIndex) {
        days[dateStr] = {
            date: dateStr,
            dayIndex: dayIndex,
            orders: [],
            hours: [],
            totalValue: 0,
            isOrderDay: false,
            isHourDay: false,
            isPayed: false
        };
    }

    processOrderCellMerging(employee, dayCells) {
        if (!employee.days || !this.config.display.showOrders || !this.config.display.mergeCells) return;

        const daysInMonth = this.getDaysInMonth(this.currentYear, this.currentMonth);

        for (let i = 0; i < daysInMonth; i++) {
            const dateStr = this.formatDate(i + 1);
            const dayData = employee.days[dateStr];
            const cell = dayCells[i];

            if (dayData && dayData.orders.length === 1 && cell) {
                const order = dayData.orders[0];
                const orderId = order.id;

                // Находим последовательные дни с этим же заказом
                let spanLength = 1;
                for (let j = i + 1; j < daysInMonth; j++) {
                    const nextDateStr = this.formatDate(j + 1);
                    const nextDayData = employee.days[nextDateStr];

                    if (nextDayData?.orders.length === 1 && nextDayData.orders[0].id === orderId) {
                        spanLength++;
                    } else {
                        break;
                    }
                }

                // Объединяем ячейки, если заказ длится больше одного дня
                if (spanLength > 1) {
                    for (let j = i + 1; j < i + spanLength; j++) {
                        const nextCell = dayCells[j];
                        if (nextCell) nextCell.style.display = 'none';
                    }

                    cell.colSpan = spanLength;
                    cell.textContent = `${spanLength} дн.`;
                    cell.title = `Заказ ${order.id || ''}: ${spanLength} дней`;

                    i += spanLength - 1; // Пропускаем обработанные дни
                }
            }
        }
    }

    showErrorMessage(message) {
        this.tableContainer.innerHTML = '';

        const errorDiv = document.createElement('div');
        errorDiv.className = 'calendar-error';
        errorDiv.innerHTML = `
            <div class="error-message">
                <p>${message}</p>
                <button class="retry-button">Повторить попытку</button>
            </div>
        `;

        this.tableContainer.appendChild(errorDiv);

        // Сохраняем обработчик для последующего удаления
        const retryHandler = () => this.loadData();
        this.eventHandlers.set(retryButton, retryHandler);

        const retryButton = errorDiv.querySelector('.retry-button');
        if (retryButton) {
            retryButton.addEventListener('click', retryHandler);
        }
    }

    calculateEmployeeTotals(employee) {
        let debit_hours = 0, credit_hours = 0, debit_amount = 0, credit_amount = 0;

        Object.values(employee.days).forEach(day => {
            // Суммируем часы
            day.hours.forEach(hour => {
                const hours = hour.count || 0;
                const amount = hours * (hour.price || 0);

                if (hour.isPayed) {
                    debit_hours += hours;
                    debit_amount += amount;
                } else {
                    credit_hours += hours;
                    credit_amount += amount;
                }
            });

            // Суммируем заказы
            day.orders.forEach(order => {
                const daysInOrder = order.duration_days || 1;
                const dailyPrice = (order.price || 0) / daysInOrder;

                if (order.isPayed) {
                    debit_hours += 8; // 8 часов в день для заказа
                    debit_amount += dailyPrice;
                } else {
                    credit_hours += 8;
                    credit_amount += dailyPrice;
                }
            });
        });

        employee.debit_hours = debit_hours;
        employee.credit_hours = credit_hours;
        employee.debit_amount = debit_amount;
        employee.credit_amount = credit_amount;
    }

    render() {
        this.container.innerHTML = '';

        // Кнопки управления отображением
        const controlsDiv = document.createElement('div');
        controlsDiv.className = 'calendar-controls';

        // Чекбокс для отображения заказов
        const ordersCheckbox = this.createCheckboxControl(
            'show-orders',
            this.config.display.showOrders,
            'Показывать заказы',
            (checked) => {
                this.config.display.showOrders = checked;
                this.renderTable();
            }
        );
        controlsDiv.appendChild(ordersCheckbox);

        // Чекбокс для отображения часов
        const hoursCheckbox = this.createCheckboxControl(
            'show-hours',
            this.config.display.showHours,
            'Показывать часы',
            (checked) => {
                this.config.display.showHours = checked;
                this.renderTable();
            }
        );
        controlsDiv.appendChild(hoursCheckbox);

        this.container.appendChild(controlsDiv);

        // Финансовый режим
        const modeDiv = document.createElement('div');
        modeDiv.className = this.config.classes.modeToggle;

        const financeCheckbox = this.createCheckboxControl(
            'finance-mode',
            this.isFinanceMode,
            this.t('calendar.mode_finance'),
            (checked) => {
                this.isFinanceMode = checked;
                this.triggerEvent('onModeToggle', this.isFinanceMode);
                this.renderTable();
            }
        );
        modeDiv.appendChild(financeCheckbox);

        this.container.appendChild(modeDiv);

        this.tableContainer = document.createElement('div');
        this.tableContainer.className = 'table-container';
        this.container.appendChild(this.tableContainer);

        this.hoverPopup = document.createElement('div');
        this.hoverPopup.className = 'calendar-popup-container';
        this.container.appendChild(this.hoverPopup);
    }

    createCheckboxControl(id, checked, labelText, onChange) {
        const container = document.createElement('span');
        container.className = 'checkbox-control';

        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.id = id;
        checkbox.checked = checked;

        const handler = (e) => onChange(e.target.checked);
        checkbox.addEventListener('change', handler);
        this.eventHandlers.set(checkbox, handler);

        const label = document.createElement('label');
        label.htmlFor = id;
        label.textContent = labelText;

        container.appendChild(checkbox);
        container.appendChild(label);

        return container;
    }

    renderTable() {
        if (!this.tableContainer) return;

        this.tableContainer.innerHTML = '';

        if (!Array.isArray(this.filteredData)) {
            this.filteredData = [];
        }

        const daysInMonth = this.getDaysInMonth(this.currentYear, this.currentMonth);
        const table = document.createElement('table');
        table.className = this.config.classes.table;

        table.appendChild(this.createDefaultHeader(daysInMonth));

        const tbody = document.createElement('tbody');
        tbody.className = this.config.classes.body;

        // Строка поиска
        const searchRow = document.createElement('tr');
        const searchCell = document.createElement('td');
        searchCell.colSpan = daysInMonth + 2;

        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.className = this.config.classes.searchInput;
        searchInput.placeholder = this.t('calendar.search_text');
        searchInput.value = this.searchText;

        const searchHandler = (e) => {
            this.searchText = e.target.value.toLowerCase();
            this.triggerEvent('onSearch', this.searchText);
            this.filterTable();
        };
        searchInput.addEventListener('input', searchHandler);
        this.eventHandlers.set(searchInput, searchHandler);

        searchCell.appendChild(searchInput);
        searchRow.appendChild(searchCell);
        tbody.appendChild(searchRow);

        // Строки сотрудников
        this.filteredData.forEach((employee, index) => {
            const row = this.createEmployeeRow(employee, index, daysInMonth);
            tbody.appendChild(row);
        });

        if (this.filteredData.length === 0) {
            const emptyRow = document.createElement('tr');
            const emptyCell = document.createElement('td');
            emptyCell.colSpan = daysInMonth + 2;
            emptyCell.className = 'empty-data';
            emptyCell.textContent = this.t('calendar.no_data');
            emptyRow.appendChild(emptyCell);
            tbody.appendChild(emptyRow);
        }

        table.appendChild(tbody);
        table.appendChild(this.createFooter(daysInMonth));
        this.tableContainer.appendChild(table);

        this.bindNavigationEvents(table);
    }

    createEmployeeRow(employee, index, daysInMonth) {
        const row = document.createElement('tr');
        row.className = `employee-row ${index % 2 ? 'even-row' : 'odd-row'}`;
        if (employee.user?.id) {
            row.dataset.userId = employee.user.id;
        }

        const nameCell = document.createElement('td');
        nameCell.className = 'employee-name';
        const family = employee.user?.profile?.family || '';
        const name = employee.user?.profile?.name || '';
        nameCell.textContent = `${family} ${name}`.trim();
        row.appendChild(nameCell);

        const dayCells = [];
        for (let i = 0; i < daysInMonth; i++) {
            const cell = this.createEmployeeCell(employee, i);
            dayCells.push(cell);
            row.appendChild(cell);
        }

        // Обрабатываем объединение ячеек для заказов
        this.processOrderCellMerging(employee, dayCells);

        const totalCell = document.createElement('td');
        totalCell.className = 'employee-total';

        const summaryDiv = document.createElement('div');
        summaryDiv.className = 'summary-cell';

        const paidDiv = document.createElement('div');
        paidDiv.className = 'paid-amount';
        paidDiv.textContent = this.isFinanceMode ?
            (employee.debit_amount || 0).toFixed(2) :
            Math.round(employee.debit_hours || 0);

        const unpaidDiv = document.createElement('div');
        unpaidDiv.className = 'unpaid-amount';
        unpaidDiv.textContent = this.isFinanceMode ?
            (employee.credit_amount || 0).toFixed(2) :
            Math.round(employee.credit_hours || 0);

        const totalDiv = document.createElement('div');
        totalDiv.className = 'total-amount';
        totalDiv.textContent = this.isFinanceMode ?
            ((employee.credit_amount || 0) + (employee.debit_amount || 0)).toFixed(2) :
            Math.round((employee.credit_hours || 0) + (employee.debit_hours || 0));

        summaryDiv.appendChild(paidDiv);
        summaryDiv.appendChild(unpaidDiv);
        summaryDiv.appendChild(totalDiv);
        totalCell.appendChild(summaryDiv);
        row.appendChild(totalCell);

        return row;
    }

    createEmployeeCell(employee, dayIndex) {
        const dateStr = this.formatDate(dayIndex + 1);
        const cellDate = new Date(this.currentYear, this.currentMonth, dayIndex + 1);
        const isPast = cellDate < this.currentDate;

        // Получаем данные за день
        const dayData = employee.days?.[dateStr];
        const hasData = dayData && (
            (this.config.display.showOrders && dayData.orders?.length > 0) ||
            (this.config.display.showHours && dayData.hours?.length > 0)
        );

        let value = 0;
        let tooltip = '';
        let cellType = 'empty';
        let overlappingOrders = false;
        let orderCount = 0;

        if (hasData) {
            let ordersValue = 0;
            let hoursValue = 0;
            orderCount = dayData.orders?.length || 0;
            overlappingOrders = orderCount > 1;

            // Считаем заказы
            if (this.config.display.showOrders && orderCount > 0) {
                if (this.isFinanceMode) {
                    dayData.orders.forEach(order => {
                        const daysInOrder = order.duration_days || 1;
                        ordersValue += (order.price || 0) / daysInOrder;
                    });
                } else {
                    ordersValue = orderCount * 8; // 8 часов в день для заказа
                }
            }

            // Считаем часы
            if (this.config.display.showHours && dayData.hours?.length > 0) {
                dayData.hours.forEach(hour => {
                    if (this.isFinanceMode) {
                        hoursValue += (hour.count || 0) * (hour.price || 0);
                    } else {
                        hoursValue += hour.count || 0;
                    }
                });
            }

            value = ordersValue + hoursValue;

            // Определяем тип ячейки для стилей
            if (orderCount > 0 && dayData.hours?.length > 0) {
                cellType = 'mixed';
                tooltip = `Заказы: ${orderCount}, Часы: ${dayData.hours.length}`;
            } else if (orderCount > 0) {
                cellType = 'order';
                tooltip = `Заказов: ${orderCount}`;
                if (overlappingOrders) tooltip += ' (наложение)';
            } else if (dayData.hours?.length > 0) {
                cellType = 'hour';
                tooltip = `Часов: ${dayData.hours.length}`;
            }
        }

        const cell = document.createElement('td');
        let className = 'employee-cell';
        if (!isPast) className += ' disabled';
        if (!hasData && isPast) className += ' free';
        if (hasData) className += ' has-data';
        if (cellType !== 'empty') className += ` type-${cellType}`;
        if (dayData?.isPayed) className += ' payed';
        if (overlappingOrders) className += ' overlapping';

        cell.className = className;
        cell.dataset.date = dateStr;
        cell.dataset.dayIndex = dayIndex;
        cell.dataset.cellType = cellType;
        cell.dataset.orderCount = orderCount;
        if (employee.user?.id) {
            cell.dataset.user = employee.user.id;
        }

        if (hasData) {
            const cellContent = document.createElement('div');
            cellContent.className = 'cell-content';

            // Добавляем индикатор количества заказов, если их несколько
            if (overlappingOrders) {
                const orderCounter = document.createElement('div');
                orderCounter.className = 'order-counter';
                orderCounter.textContent = `${orderCount}`;
                cellContent.appendChild(orderCounter);
            }

            // Добавляем основное значение
            const valueDisplay = document.createElement('div');
            valueDisplay.className = 'cell-value';
            valueDisplay.textContent = this.isFinanceMode ?
                value.toFixed(2) : Math.round(value);
            cellContent.appendChild(valueDisplay);

            cell.appendChild(cellContent);
            cell.title = tooltip;
        } else {
            cell.title = isPast ? 'Кликните для добавления' : 'Недоступно';
        }

        if (isPast) {
            const clickHandler = (e) => this.handleCellClick(e, {
                employee,
                dateStr,
                cellDate,
                hasData,
                dayData,
                value,
                isPast,
                isFinance: this.isFinanceMode,
                cellType,
                overlappingOrders,
                orderCount
            });
            cell.addEventListener('click', clickHandler);
            this.eventHandlers.set(cell, clickHandler);
        }

        return cell;
    }

    handleCellClick(event, cellData) {
        event.stopPropagation();

        // Закрываем предыдущее модальное окно
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }

        this.triggerEvent('onDayClick', cellData);

        // Раскомментировать этот блок кода
        if (cellData.hasData) {
            this.openDayDetailsModal(cellData);
        } else {
            this.addNewRecord(cellData.employee, cellData.dateStr);
        }
    }

    openDayDetailsModal(cellData) {
        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }

        const modal = document.createElement('div');
        modal.className = 'calendar-modal';

        let content = `<h3>${new Date(cellData.dateStr).toLocaleDateString(this.config.language)}</h3>`;

        // Показываем предупреждение о наложении заказов
        if (cellData.overlappingOrders && cellData.orderCount > 1) {
            content += `
        <div class="overlap-warning">
            <strong>⚠️ Наложение заказов!</strong><br>
            В этот день выполняется ${cellData.orderCount} заказов одновременно
        </div>
        `;
        }

        if (cellData.dayData) {
            // Заказы
            if (cellData.dayData.orders && cellData.dayData.orders.length > 0) {
                content += `<h4>${this.t('calendar.order')} (${cellData.dayData.orders.length})</h4>`;

                // Группируем заказы по времени
                const groupedOrders = this.groupOverlappingOrders(cellData.dayData.orders);

                Object.entries(groupedOrders).forEach(([groupKey, orders], groupIndex) => {
                    const isOverlap = orders.length > 1;

                    if (isOverlap) {
                        content += `<div class="overlap-details">`;
                        content += `<h5>Группа наложения #${groupIndex + 1} (${orders.length} заказов)</h5>`;
                    }

                    orders.forEach((order, index) => {
                        const startDate = new Date(order.start_datetime).toLocaleDateString();
                        const endDate = new Date(order.finish_datetime).toLocaleDateString();
                        const startTime = new Date(order.start_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        const endTime = new Date(order.finish_datetime).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

                        content += `
                    <div class="order-item ${isOverlap ? 'overlap-item' : ''}">
                        <strong>Заказ #${order.id || index + 1} ${isOverlap ? `(группа ${groupIndex + 1})` : ''}</strong><br>
                        Период: ${startDate} ${startTime} - ${endDate} ${endTime}<br>
                        ${isOverlap ? `<span style="color: #dc3545;">⚠️ Наложение с другими заказами</span><br>` : ''}
                        Цена: ${order.price || 0} руб.<br>
                        Статус: ${order.is_payed ? this.t('calendar.is_paid') : this.t('calendar.is_not_paid')}<br>
                        ${!order.is_payed ? `<button class="pay-order-btn" data-order-id="${order.id}">${this.t('calendar.pay')}</button>` : ''}
                    </div>
                    ${isOverlap && index < orders.length - 1 ? '<hr style="margin: 5px 0; border-color: #ddd;">' : ''}
                `;
                    });

                    if (isOverlap) {
                        content += `</div>`;
                    }
                });
            }

            // Часы работы
            if (cellData.dayData.hours && cellData.dayData.hours.length > 0) {
                content += `<h4>${this.t('calendar.hours')} (${cellData.dayData.hours.length})</h4>`;
                cellData.dayData.hours.forEach((hour, index) => {
                    content += `
                <div class="hour-item">
                    <strong>Заказ #${hour.order_id || index + 1}</strong><br>
                    Часы: ${hour.count || 0}<br>
                    Цена: ${hour.price || 0} руб./ч<br>
                    Сумма: ${(hour.count || 0) * (hour.price || 0)} руб.<br>
                    Время: ${hour.start_time} ${hour.stop_time}<br>
                    Статус: ${hour.is_payed ? this.t('calendar.is_paid') : this.t('calendar.is_not_paid')}<br>
                    ${!hour.is_payed ? `<button class="pay-hour-btn" data-user-id="${hour.user_id}" data-date="${hour.date}" data-order-id="${hour.order_id}">${this.t('calendar.pay')}</button>` : ''}
                </div>
            `;
                });
            }
        }

        content += `
    <div class="modal-actions">
        <button class="close-btn">${this.t('calendar.cancel')}</button>
        <button class="add-btn">${this.t('calendar.add')}</button>
    </div>
    `;

        modal.innerHTML = `<div class="modal-content">${content}</div>`;
        document.body.appendChild(modal);

        // Добавляем обработчик для удаления модального окна
        const closeHandler = () => {
            modal.remove();
            this.currentModal = null;
        };

        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeHandler();
        });

        modal.querySelector('.close-btn').addEventListener('click', closeHandler);
        this.eventHandlers.set(modal.querySelector('.close-btn'), closeHandler);

        modal.querySelector('.add-btn').addEventListener('click', () => {
            modal.remove();
            this.currentModal = null;
            this.addNewRecord(cellData.employee, cellData.dateStr);
        });

        // Обработчики оплаты
        modal.querySelectorAll('.pay-order-btn').forEach(btn => {
            const handler = (e) => {
                e.stopPropagation();
                const orderId = e.target.dataset.orderId;
                const employeeId = cellData.employee.id || cellData.employee.user?.id;
                this.markOrderAsPaid(orderId, employeeId);
                modal.remove();
                this.currentModal = null;
            };
            btn.addEventListener('click', handler);
            this.eventHandlers.set(btn, handler);
        });

        modal.querySelectorAll('.pay-hour-btn').forEach(btn => {
            const handler = (e) => {
                e.stopPropagation();
                const dataset = e.target.dataset;
                this.markHourAsPaid(dataset.userId, dataset.date, dataset.orderId);
                modal.remove();
                this.currentModal = null;
            };
            btn.addEventListener('click', handler);
            this.eventHandlers.set(btn, handler);
        });

        this.currentModal = modal;
    }

    groupOverlappingOrders(orders) {
        if (!orders || orders.length === 0) return {};

        const sortedOrders = [...orders].sort((a, b) => {
            return new Date(a.start_datetime) - new Date(b.start_datetime);
        });

        const groups = {};
        let currentGroup = [];
        let groupIndex = 0;

        sortedOrders.forEach((order) => {
            if (currentGroup.length === 0) {
                currentGroup.push(order);
            } else {
                const lastOrder = currentGroup[currentGroup.length - 1];
                const lastOrderEnd = new Date(lastOrder.finish_datetime);
                const currentOrderStart = new Date(order.start_datetime);

                if (currentOrderStart <= lastOrderEnd) {
                    currentGroup.push(order);
                } else {
                    groups[`group_${groupIndex}`] = [...currentGroup];
                    groupIndex++;
                    currentGroup = [order];
                }
            }
        });

        if (currentGroup.length > 0) {
            groups[`group_${groupIndex}`] = [...currentGroup];
        }

        return groups;
    }

    createDefaultHeader(daysInMonth) {
        const thead = document.createElement('thead');

        const headerRow1 = document.createElement('tr');
        headerRow1.className = 'calendar-header';

        const backCell = document.createElement('td');
        backCell.rowSpan = 2;
        backCell.className = 'nav-cell prev-month';
        const backButton = document.createElement('button');
        backButton.className = 'nav-button prev-button';
        backButton.textContent = '←';
        backCell.appendChild(backButton);
        headerRow1.appendChild(backCell);

        const monthCell = document.createElement('td');
        monthCell.colSpan = daysInMonth;
        monthCell.className = 'month-title';
        monthCell.textContent = `${this.config.monthNames[this.currentMonth]} ${this.currentYear}`;
        headerRow1.appendChild(monthCell);

        const nextCell = document.createElement('td');
        nextCell.rowSpan = 2;
        nextCell.className = 'nav-cell next-month';
        const nextButton = document.createElement('button');
        nextButton.className = 'nav-button next-button';
        nextButton.textContent = '→';
        nextCell.appendChild(nextButton);
        headerRow1.appendChild(nextCell);

        thead.appendChild(headerRow1);

        const headerRow2 = document.createElement('tr');
        headerRow2.className = 'days-header';
        for (let i = 1; i <= daysInMonth; i++) {
            const dayCell = document.createElement('td');
            dayCell.className = 'day-header';
            dayCell.textContent = i;
            headerRow2.appendChild(dayCell);
        }
        thead.appendChild(headerRow2);

        return thead;
    }

    calculateTotals() {
        const dataToCalculate = this.filteredData.length > 0 ? this.filteredData : this.data;

        if (!Array.isArray(dataToCalculate) || dataToCalculate.length === 0) {
            return {
                debit_hours: 0,
                credit_hours: 0,
                debit_amount: 0,
                credit_amount: 0
            };
        }

        return dataToCalculate.reduce((acc, curr) => ({
            debit_hours: acc.debit_hours + Number(curr.debit_hours || 0),
            credit_hours: acc.credit_hours + Number(curr.credit_hours || 0),
            debit_amount: acc.debit_amount + Number(curr.debit_amount || 0),
            credit_amount: acc.credit_amount + Number(curr.credit_amount || 0)
        }), {
            debit_hours: 0,
            credit_hours: 0,
            debit_amount: 0,
            credit_amount: 0
        });
    }

    createFooter(daysInMonth) {
        const tfoot = document.createElement('tfoot');
        tfoot.className = this.config.classes.footer;

        const footerRow = document.createElement('tr');

        const totalLabelCell = document.createElement('td');
        totalLabelCell.colSpan = daysInMonth + 1;
        totalLabelCell.textContent = this.t('calendar.total');
        footerRow.appendChild(totalLabelCell);

        const totalCell = document.createElement('td');
        totalCell.className = 'total-cell';

        const totals = this.calculateTotals();
        const summaryDiv = document.createElement('div');
        summaryDiv.className = 'summary-cell';

        const paidDiv = document.createElement('div');
        paidDiv.className = 'paid-amount';
        paidDiv.textContent = this.isFinanceMode ?
            totals.debit_amount.toFixed(2) :
            Math.round(totals.debit_hours);

        const unpaidDiv = document.createElement('div');
        unpaidDiv.className = 'unpaid-amount';
        unpaidDiv.textContent = this.isFinanceMode ?
            totals.credit_amount.toFixed(2) :
            Math.round(totals.credit_hours);

        const totalDiv = document.createElement('div');
        totalDiv.className = 'total-amount';
        totalDiv.textContent = this.isFinanceMode ?
            (totals.debit_amount + totals.credit_amount).toFixed(2) :
            Math.round(totals.debit_hours + totals.credit_hours);

        summaryDiv.appendChild(paidDiv);
        summaryDiv.appendChild(unpaidDiv);
        summaryDiv.appendChild(totalDiv);
        totalCell.appendChild(summaryDiv);
        footerRow.appendChild(totalCell);

        tfoot.appendChild(footerRow);
        return tfoot;
    }

    bindNavigationEvents(table) {
        const prevButton = table.querySelector('.prev-button');
        const nextButton = table.querySelector('.next-button');

        const prevHandler = () => this.prevMonth();
        const nextHandler = () => this.nextMonth();

        if (prevButton) {
            prevButton.addEventListener('click', prevHandler);
            this.eventHandlers.set(prevButton, prevHandler);
        }

        if (nextButton) {
            nextButton.addEventListener('click', nextHandler);
            this.eventHandlers.set(nextButton, nextHandler);
        }
    }

    prevMonth() {
        this.currentMonth--;
        if (this.currentMonth < 0) {
            this.currentMonth = 11;
            this.currentYear--;
        }
        this.triggerEvent('onMonthChange', { year: this.currentYear, month: this.currentMonth });
        this.loadData();
    }

    nextMonth() {
        this.currentMonth++;
        if (this.currentMonth > 11) {
            this.currentMonth = 0;
            this.currentYear++;
        }
        this.triggerEvent('onMonthChange', { year: this.currentYear, month: this.currentMonth });
        this.loadData();
    }

    filterTable() {
        const rows = this.tableContainer.querySelectorAll('.employee-row');
        let hasVisibleRows = false;

        rows.forEach(row => {
            const nameCell = row.querySelector('.employee-name');
            if (!nameCell) return;

            const userName = nameCell.textContent.toLowerCase();
            const isVisible = !this.searchText || userName.includes(this.searchText);
            row.style.display = isVisible ? '' : 'none';

            if (isVisible) hasVisibleRows = true;
        });

        // Пересчитываем и обновляем подвал таблицы
        const table = this.tableContainer.querySelector('.calendar-table');
        if (table) {
            const oldTfoot = table.querySelector('tfoot');
            if (oldTfoot) oldTfoot.remove();

            const daysInMonth = this.getDaysInMonth(this.currentYear, this.currentMonth);
            const newTfoot = this.createFooter(daysInMonth);
            table.appendChild(newTfoot);
        }
    }

    // Удалены неиспользуемые методы:
    // - handleCellHover
    // - handleCellHoverLeave
    // - createDefaultEmployeeCell

    // Вспомогательные методы
    t(key) {
        return this.config.translations[key] || key;
    }

    async markHourAsPaid(userId, date, orderId) {
        if (!userId || !this.config.ajax.enabled) return;

        try {
            const endpoint = this.config.ajax.endpoints.markPaid;
            if (!endpoint) {
                console.error('markPaid endpoint not configured');
                return;
            }

            const result = await this.makeRequest('markPaid', { user_id: userId, date: date, order_id: orderId }, 'POST');
            if (result) {
                this.loadData(); // Обновляем данные
                this.triggerEvent('onHourPaid', { hourId: userId, result });
            }
        } catch (error) {
            console.error('Error marking hour as paid:', error);
            this.triggerEvent('onHourPayError', { hourId: userId, error });
        }
    }

    async markOrderAsPaid(orderId, employeeId) {
        if (!orderId || !this.config.ajax.enabled) return;

        try {
            const endpoint = this.config.ajax.endpoints.markOrderPaid;
            if (!endpoint) {
                console.error('markOrderPaid endpoint not configured');
                return;
            }

            const result = await this.makeRequest('markOrderPaid', {
                order_id: orderId,
                user_id: employeeId
            }, 'POST');

            if (result) {
                this.loadData(); // Обновляем данные
                this.triggerEvent('onOrderPaid', { orderId, employeeId, result });
            }
        } catch (error) {
            console.error('Error marking order as paid:', error);
            this.triggerEvent('onOrderPayError', { orderId, employeeId, error });
        }
    }

    // Также добавьте вспомогательный метод makeRequest, если его нет:
    async makeRequest(endpointKey, data = null, method = 'GET') {
        if (!this.config.ajax.enabled) {
            console.warn('AJAX is disabled');
            return null;
        }

        const endpoint = this.config.ajax.endpoints[endpointKey];
        if (!endpoint) {
            console.error(`Endpoint ${endpointKey} not configured`);
            return null;
        }

        const url = endpoint.startsWith('http') ? endpoint : `${this.config.apiUrl}${endpoint}`;
        const options = {
            method: method,
            headers: {
                'Authorization': `Bearer ${this.config.authToken}`,
                'Content-Type': 'application/json'
            }
        };

        options.method = method;
        if (data && (method === 'POST' || method === 'PUT')) {
            options.body = JSON.stringify(data);
        } else if (data && method === 'GET') {
            const params = new URLSearchParams(data).toString();
            return this.makeRequestWithUrl(`${url}?${params}`, { method: 'GET' });
        }

        try {
            if (this.config.ajax.customRequest) {
                return await this.config.ajax.customRequest(url, options);
            } else {
                return await this.config.ajax.request(url, options);
            }
        } catch (error) {
            console.error(`Request to ${endpointKey} failed:`, error);
            this.triggerEvent('onDataError', error);
            return null;
        }
    }

    async makeRequestWithUrl(url, options = {}) {
        try {
            if (this.config.ajax.customRequest) {
                return await this.config.ajax.customRequest(url, options);
            } else {
                return await this.config.ajax.request(url, options);
            }
        } catch (error) {
            console.error(`Request failed:`, error);
            this.triggerEvent('onDataError', error);
            throw error;
        }
    }

    zeroPad(num, length = 2) {
        return num.toString().padStart(length, '0');
    }

    getDaysInMonth(year, month) {
        return new Date(year, month + 1, 0).getDate();
    }

    formatDate(day) {
        return `${this.currentYear}-${this.zeroPad(this.currentMonth + 1, 2)}-${this.zeroPad(day, 2)}`;
    }

    addNewRecord(employee, dateStr) {
        if (this.config.display.showHours) {
            this.addNewHours(employee, dateStr);
        } else if (this.config.display.showOrders) {
            // Здесь можно добавить логику для создания нового заказа
            console.log('Создание нового заказа для', employee.user?.id, 'на дату', dateStr);
            // this.addNewOrder(employee, dateStr); // Если будет реализовано
        }
    }

    async addNewHours(employee, dateStr) {
        const cellData = {
            employee: employee,
            dateStr: dateStr,
            date: new Date(dateStr)
        };

        this.triggerEvent('onAddHours', cellData);

        if (this.config.ajax.enabled) {
            try {
                const endpoint = this.config.ajax.endpoints.getPrice;
                const url = endpoint.startsWith('http') ?
                    `${endpoint}?user_id=${employee.user.id}&date=${dateStr}` :
                    `${this.config.apiUrl}${endpoint}?user_id=${employee.user.id}&date=${dateStr}`;

                const priceData = await this.makeRequestWithUrl(url, { method: 'GET' });

                if (priceData) {
                    this.modalData = [{
                        user_id: employee.user.id,
                        date: dateStr,
                        price: priceData.price || 0,
                        is_payed: false,
                        is_new: true
                    }];
                    this.openModal(this.modalData);
                }
            } catch (error) {
                console.error('Error getting price:', error);
                this.modalData = [{
                    user_id: employee.user.id,
                    date: dateStr,
                    price: 0,
                    is_payed: false,
                    is_new: true
                }];
                this.openModal(this.modalData);
            }
        } else {
            this.modalData = [{
                user_id: employee.user.id,
                date: dateStr,
                price: 0,
                is_payed: false,
                is_new: true
            }];
            this.openModal(this.modalData);
        }
    }

    triggerEvent(eventName, data) {
        if (this.config.events[eventName]) {
            try {
                this.config.events[eventName](data, this);
            } catch (error) {
                console.error(`Error in event handler ${eventName}:`, error);
            }
        }
    }

    // Публичные методы API
    setLanguage(lang) {
        this.config.language = lang;
        this.renderTable();
    }

    setAuthToken(token) {
        this.config.authToken = token;
        if (this.config.ajax.enabled) {
            this.loadData();
        }
    }

    setData(data) {
        this.data = Array.isArray(data) ? data : [];
        this.filteredData = [...this.data];
        this.renderTable();
    }

    updateConfig(newConfig) {
        // Используем существующий mergeConfig вместо отсутствующего safeMerge
        this.mergeConfig(newConfig);
        this.render();
        this.loadData();
    }

    getCurrentDate() {
        return {
            year: this.currentYear,
            month: this.currentMonth,
            monthName: this.config.monthNames[this.currentMonth]
        };
    }

    refresh() {
        this.loadData();
    }

    destroy() {
        // Удаляем все обработчики событий
        this.eventHandlers.forEach((handler, element) => {
            element.removeEventListener('click', handler);
            element.removeEventListener('change', handler);
            element.removeEventListener('input', handler);
        });
        this.eventHandlers.clear();

        // Удаляем DOM элементы
        this.container.remove();

        if (this.currentModal) {
            this.currentModal.remove();
            this.currentModal = null;
        }

        if (this.hoverPopup) {
            this.hoverPopup.remove();
            this.hoverPopup = null;
        }
    }
}

// Экспорт для использования
if (typeof module !== 'undefined' && module.exports) {
    module.exports = Calendar;
} else {
    window.Calendar = Calendar;
}