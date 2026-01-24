class ScheduleComponent {
    constructor(containerId, config = {}) {
        // Конфигурация по умолчанию
        this.defaultConfig = {
            // Общие настройки
            title: 'Расписание заказов',
            locale: 'ru-RU',

            // Настройки отображения сотрудников
            employees: {
                showAvatar: true,
                avatarFormat: 'initials', // 'initials' | 'firstLetter'
                nameFormat: 'full', // 'full' | 'short'
                showPosition: true,
                showStats: true,
                sortBy: 'name', // 'name' | 'position' | 'orders'
                filter: null
            },

            // Настройки отображения заказов
            orders: {
                showTime: true,
                timeFormat: 'range', // 'range' | 'duration'
                showStatus: true,
                showClient: true,
                showPriority: true,
                statusLabels: {
                    'new': 'Новый',
                    'in-progress': 'В работе',
                    'completed': 'Завершен',
                    'cancelled': 'Отменен'
                },
                priorityColors: {
                    'high': '#dc3545',
                    'medium': '#ffc107',
                    'low': '#198754'
                }
            },

            // Настройки календаря
            calendar: {
                weekdayFormat: 'short', // 'short' | 'full'
                showWeekends: true,
                highlightToday: true,
                firstDayOfWeek: 1, // 1 - Понедельник, 0 - Воскресенье
                dayCellWidth: 100
            },

            // Настройки API
            api: {
                employeesUrl: '/api/employees',
                ordersUrl: '/api/orders',
                methods: {
                    get: 'GET',
                    post: 'POST',
                    put: 'PUT',
                    delete: 'DELETE'
                }
            },

            // Настройки взаимодействия
            interaction: {
                selectable: true,
                editable: true,
                draggable: true,
                resizable: true,
                onOrderClick: null,
                onEmployeeSelect: null,
                onDateChange: null
            },

            // Новые настройки drag & drop
            dragDrop: {
                enabled: true,
                ghostOpacity: 0.5,
                ghostClass: 'order-ghost',
                dragHandle: '.drag-handle',
                dropEffect: 'move',
                onOrderMove: null,  // Callback при перемещении заказа
                validateDrop: null   // Функция валидации перемещения
            },

            // Настройки прокрутки
            scrolling: {
                dragToScroll: true,
                scrollSensitivity: 2,
                scrollSpeed: 5,
                scrollBoundary: 50
            },

            // Настройки шаблонов
            templates: {
                dayCell: 'day-cell',
                employeeItem: 'employee-item',
                orderItem: 'order-item',
                orderCell: 'order-cell',
                customTemplates: {}  // Пользовательские шаблоны
            }
        };

        // Слияние конфигураций
        this.config = this.mergeConfigs(this.defaultConfig, config);

        // Регистрация пользовательских шаблонов
        this.registerCustomTemplates();

        // Ссылки на DOM
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Контейнер с id "${containerId}" не найден`);
            return;
        }

        // Состояние компонента
        this.state = {
            currentDate: new Date(),
            employees: [],
            orders: [],
            selectedEmployee: null,
            loading: true,
            error: null,

            // Состояние для drag & drop
            dragging: {
                isDragging: false,
                orderId: null,
                sourceElement: null,
                ghostElement: null,
                dragData: null
            },

            // Состояние для прокрутки мышью
            scrolling: {
                isScrolling: false,
                startX: 0,
                startY: 0,
                scrollLeft: 0,
                scrollTop: 0,
                dragElement: null
            },

            // Состояние для синхронной прокрутки
            syncScroll: {
                isSyncing: false,
                employeesScrollTop: 0,
                ordersScrollTop: 0
            }
        };

        // Инициализация
        this.init();
    }

    /**
     * Слияние конфигураций
     */
    mergeConfigs(defaultConfig, userConfig) {
        const merged = { ...defaultConfig };

        const mergeDeep = (target, source) => {
            for (const key in source) {
                if (source[key] && typeof source[key] === 'object' && !Array.isArray(source[key])) {
                    if (!target[key]) target[key] = {};
                    mergeDeep(target[key], source[key]);
                } else {
                    target[key] = source[key];
                }
            }
            return target;
        };

        return mergeDeep(merged, userConfig);
    }

    /**
     * Регистрация пользовательских шаблонов
     */
    registerCustomTemplates() {
        if (this.config.templates.customTemplates) {
            Object.entries(this.config.templates.customTemplates).forEach(([name, template]) => {
                ElementFactory.registerTemplate(name, template);
            });
        }
    }

    /**
     * Инициализация компонента
     */
    init() {
        this.render();
        this.loadData();
        this.bindEvents();
        this.setupMouseScrolling();
        // Drag & drop настраивается в renderOrderItem и renderCalendarColumn
    }

    /**
     * Синхронизация высот прокрутки
     */
    syncScrollHeights() {
        if (!this.employeesList || !this.ordersGrid) return;

        const employeesScrollHeight = this.employeesList.scrollHeight - this.employeesList.clientHeight;
        const ordersScrollHeight = this.ordersGrid.scrollHeight - this.ordersGrid.clientHeight;

        if (employeesScrollHeight > 0 && ordersScrollHeight > 0) {
            this.scrollRatio = ordersScrollHeight / employeesScrollHeight;
        }
    }

    /**
     * Обработка прокрутки списка сотрудников
     */
    handleEmployeesScroll(e) {
        if (this.state.syncScroll.isSyncing) return;

        this.state.syncScroll.isSyncing = true;
        const scrollTop = e.target.scrollTop;

        if (this.ordersGrid && this.scrollRatio) {
            this.ordersGrid.scrollTop = scrollTop * this.scrollRatio;
        }

        setTimeout(() => {
            this.state.syncScroll.isSyncing = false;
        }, 10);
    }

    /**
     * Обработка прокрутки сетки заказов
     */
    handleOrdersGridScroll(e) {
        if (this.state.syncScroll.isSyncing) return;

        this.state.syncScroll.isSyncing = true;
        const scrollTop = e.target.scrollTop;

        if (this.employeesList && this.scrollRatio) {
            this.employeesList.scrollTop = scrollTop / this.scrollRatio;
        }

        setTimeout(() => {
            this.state.syncScroll.isSyncing = false;
        }, 10);
    }

    /**
     * Рендеринг всего компонента
     */
    render() {
        // Очистка контейнера
        this.container.innerHTML = '';

        // Создание основного контейнера
        this.componentElement = ElementFactory.createElement('div', {
            className: 'schedule-component'
        });

        // Добавление заголовка
        this.componentElement.appendChild(this.renderHeader());

        // Добавление основного содержимого
        this.componentElement.appendChild(this.renderContent());

        // Добавление в контейнер
        this.container.appendChild(this.componentElement);
    }

    /**
     * Рендеринг заголовка
     */
    renderHeader() {
        const monthYear = DateUtils.formatDate(this.state.currentDate, 'MMMM YYYY');

        return ElementFactory.createElement('div', {
            className: 'schedule-header',
            children: [
                ElementFactory.createElement('div', {
                    className: 'header-content',
                    children: [
                        // Заголовок
                        ElementFactory.createElement('div', {
                            className: 'header-title',
                            children: [
                                ElementFactory.createElement('i', {
                                    className: 'fas fa-calendar-alt title-icon'
                                }),
                                ElementFactory.createElement('h1', {
                                    className: 'title-text',
                                    text: this.config.title
                                })
                            ]
                        }),

                        // Навигация по месяцам
                        ElementFactory.createElement('div', {
                            className: 'month-navigation',
                            children: [
                                ElementFactory.createIconButton(
                                    'fas fa-chevron-left',
                                    'nav-btn',
                                    () => this.previousMonth()
                                ),
                                ElementFactory.createElement('h2', {
                                    className: 'current-month',
                                    text: monthYear
                                }),
                                ElementFactory.createIconButton(
                                    'fas fa-chevron-right',
                                    'nav-btn',
                                    () => this.nextMonth()
                                )
                            ]
                        }),

                        // Кнопки действий
                        ElementFactory.createElement('div', {
                            className: 'action-buttons',
                            children: [
                                ElementFactory.createElement('button', {
                                    className: ['btn', 'btn-light', 'action-btn'],
                                    children: [
                                        ElementFactory.createElement('i', {
                                            className: 'fas fa-plus btn-icon'
                                        }),
                                        ElementFactory.createElement('span', {
                                            text: 'Добавить'
                                        })
                                    ],
                                    events: {
                                        click: () => this.showAddOrderModal()
                                    }
                                }),
                                ElementFactory.createElement('button', {
                                    className: ['btn', 'btn-light', 'action-btn'],
                                    children: [
                                        ElementFactory.createElement('i', {
                                            className: 'fas fa-sync-alt btn-icon'
                                        }),
                                        ElementFactory.createElement('span', {
                                            text: 'Обновить'
                                        })
                                    ],
                                    events: {
                                        click: () => this.refreshData()
                                    }
                                }),
                                ElementFactory.createElement('button', {
                                    className: ['btn', 'btn-light', 'action-btn'],
                                    children: [
                                        ElementFactory.createElement('i', {
                                            className: 'fas fa-calendar-day btn-icon'
                                        }),
                                        ElementFactory.createElement('span', {
                                            text: 'Сегодня'
                                        })
                                    ],
                                    events: {
                                        click: () => this.goToToday()
                                    }
                                })
                            ]
                        })
                    ]
                })
            ]
        });
    }

    /**
     * Рендеринг основного содержимого
     */
    renderContent() {
        return ElementFactory.createElement('div', {
            className: 'schedule-content',
            children: [
                // Колонка сотрудников
                this.renderEmployeesColumn(),
                // Календарная часть
                this.renderCalendarColumn()
            ]
        });
    }

    /**
     * Рендеринг колонки сотрудников
     */
    renderEmployeesColumn() {
        const column = ElementFactory.createElement('div', {
            className: 'employees-column'
        });

        // Заголовок колонки
        column.appendChild(ElementFactory.createElement('div', {
            className: 'employees-header',
            children: [
                ElementFactory.createElement('div', {
                    className: 'header-title',
                    children: [
                        ElementFactory.createElement('i', {
                            className: 'fas fa-users header-icon'
                        }),
                        ElementFactory.createElement('h3', {
                            text: 'Сотрудники'
                        })
                    ]
                })
            ]
        }));

        // Список сотрудников
        const employeesList = ElementFactory.createElement('div', {
            className: 'employees-list'
        });

        if (this.state.loading) {
            employeesList.appendChild(ElementFactory.createLoadingState('Загрузка сотрудников...'));
        } else if (this.state.error) {
            employeesList.appendChild(ElementFactory.createEmptyState('fas fa-exclamation-triangle', this.state.error));
        } else if (this.state.employees.length === 0) {
            employeesList.appendChild(ElementFactory.createEmptyState('fas fa-user-friends', 'Нет сотрудников'));
        } else {
            const sortedEmployees = this.sortEmployees(this.state.employees);

            sortedEmployees.forEach(employee => {
                const isActive = this.state.selectedEmployee === employee.id;
                const employeeElement = this.renderEmployeeItem(employee, isActive);
                employeesList.appendChild(employeeElement);
            });
        }

        column.appendChild(employeesList);
        return column;
    }

    /**
     * Рендеринг элемента сотрудника
     */
    renderEmployeeItem(employee, isActive) {
        const avatarText = this.config.employees.avatarFormat === 'initials'
            ? `${employee.firstName[0]}${employee.lastName[0]}`
            : employee.firstName[0];

        const avatarColor = employee.avatarColor || '#0d6efd';

        return ElementFactory.createElement('div', {
            className: `employee-item ${isActive ? 'active' : ''}`,
            dataset: {
                employeeId: employee.id
            },
            events: {
                click: () => this.selectEmployee(employee.id)
            },
            children: [
                this.config.employees.showAvatar && ElementFactory.createElement('div', {
                    className: 'employee-avatar',
                    styles: { backgroundColor: avatarColor },
                    text: avatarText
                }),

                ElementFactory.createElement('div', {
                    className: 'employee-info',
                    children: [
                        ElementFactory.createElement('div', {
                            className: 'employee-name',
                            text: this.config.employees.nameFormat === 'full'
                                ? `${employee.firstName} ${employee.lastName}`
                                : `${employee.firstName} ${employee.lastName[0]}.`
                        }),

                        this.config.employees.showPosition && ElementFactory.createElement('div', {
                            className: 'employee-position',
                            text: employee.position
                        }),

                        this.config.employees.showStats && employee.stats && ElementFactory.createElement('div', {
                            className: 'employee-stats',
                            children: [
                                ElementFactory.createElement('div', {
                                    className: 'stat',
                                    children: [
                                        ElementFactory.createElement('i', {
                                            className: 'fas fa-tasks stat-icon'
                                        }),
                                        ElementFactory.createElement('span', {
                                            text: employee.stats.orders || 0
                                        })
                                    ]
                                }),
                                ElementFactory.createElement('div', {
                                    className: 'stat',
                                    children: [
                                        ElementFactory.createElement('i', {
                                            className: 'fas fa-clock stat-icon'
                                        }),
                                        ElementFactory.createElement('span', {
                                            text: employee.stats.hours || '0h'
                                        })
                                    ]
                                })
                            ]
                        })
                    ].filter(Boolean)
                })
            ].filter(Boolean)
        });
    }

    /**
     * Сортировка сотрудников
     */
    sortEmployees(employees) {
        const { sortBy } = this.config.employees;

        return [...employees].sort((a, b) => {
            switch (sortBy) {
                case 'position':
                    return a.position.localeCompare(b.position);
                case 'orders':
                    return (b.stats?.orders || 0) - (a.stats?.orders || 0);
                case 'name':
                default:
                    return `${a.lastName} ${a.firstName}`.localeCompare(`${b.lastName} ${b.firstName}`);
            }
        });
    }

    /**
     * Рендеринг заголовка с днями месяца
     */
    renderDaysHeader() {
        const daysInMonth = DateUtils.getLastDayOfMonth(this.state.currentDate).getDate();
        const daysHeader = ElementFactory.createElement('div', {
            className: 'days-header'
        });

        // Создание ячеек дней
        for (let day = 1; day <= daysInMonth; day++) {
            const date = new Date(
                this.state.currentDate.getFullYear(),
                this.state.currentDate.getMonth(),
                day
            );

            const dayCell = ElementFactory.createDayCell(date, this.config.calendar);
            daysHeader.appendChild(dayCell);
        }

        return daysHeader;
    }

    /**
     * Рендеринг элемента заказа с поддержкой drag & drop (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    renderOrderItem(order, index) {
        const priorityClass = `priority-${order.priority}`;
        const timeText = order.time
            ? order.time
            : this.config.orders.timeFormat === 'range'
                ? `${order.startTime || '9:00'} - ${order.endTime || '18:00'}`
                : 'Весь день';

        const statusText = this.config.orders.statusLabels?.[order.status] || order.status;

        return ElementFactory.createElement('div', {
            className: `order-item ${priorityClass} draggable-order`,
            dataset: {
                orderId: order.id,
                orderTitle: order.title,
                employeeId: order.employeeId,
                date: order.date,
                priority: order.priority,
                status: order.status
            },
            draggable: true,
            events: {
                // Исправленные обработчики drag & drop
                dragstart: (e) => this.handleDragStart(e, order),
                dragend: (e) => this.handleDragEnd(e),
                // Отключаем стандартное поведение
                dragover: (e) => e.preventDefault(),
                click: (e) => {
                    e.stopPropagation();
                    const employee = this.state.employees.find(e => e.id === order.employeeId);
                    this.showOrderDetails(order, employee);
                }
            },
            styles: {
                top: `${5 + index * 75}px`,
                height: '70px',
                cursor: 'grab'
            },
            children: [
                ElementFactory.createElement('div', {
                    className: 'order-title',
                    text: order.title,
                    attributes: {title: order.title}
                }),

                this.config.orders.showTime && ElementFactory.createElement('div', {
                    className: 'order-time',
                    text: timeText
                }),

                this.config.orders.showStatus && ElementFactory.createElement('div', {
                    className: ['order-status', `status-${order.status.replace('-', '')}`],
                    text: statusText
                }),

                this.config.orders.showClient && order.client && ElementFactory.createElement('div', {
                    className: 'order-client',
                    text: order.client,
                    styles: {
                        fontSize: '0.75rem',
                        color: '#6c757d',
                        marginTop: '0.25rem'
                    }
                }),

                ElementFactory.createElement('div', {
                    className: 'drag-handle',
                    styles: {
                        position: 'absolute',
                        right: '5px',
                        top: '5px',
                        cursor: 'grab',
                        opacity: '0.5'
                    },
                    children: [
                        ElementFactory.createElement('i', {
                            className: 'fas fa-arrows-alt'
                        })
                    ]
                })
            ].filter(Boolean)
        });
    }

    /**
     * Рендеринг ячейки заказа (drop zone) (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    renderOrderCell(date, employeeId, dayOrders) {
        const isToday = DateUtils.isSameDay(date, new Date());
        const orderCell = ElementFactory.createElement('div', {
            className: `order-cell ${isToday ? 'today' : ''} drop-zone`,
            dataset: {
                date: date.toISOString().split('T')[0],
                employeeId: employeeId
            },
            events: {
                // Исправленные обработчики для drop zone
                dragover: (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    e.dataTransfer.dropEffect = 'move';
                    e.currentTarget.classList.add('drop-zone-active');
                },
                dragenter: (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    e.currentTarget.classList.add('drop-zone-highlight');
                },
                dragleave: (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    e.currentTarget.classList.remove('drop-zone-active', 'drop-zone-highlight');
                },
                drop: (e) => this.handleDrop(e, date, employeeId)
            }
        });

        // Отображение заказов
        dayOrders.forEach((order, index) => {
            const orderElement = this.renderOrderItem(order, index);
            orderCell.appendChild(orderElement);
        });

        return orderCell;
    }

    /**
     * Получение заказов для даты и сотрудника
     */
    getOrdersForDateAndEmployee(date, employeeId) {
        return this.state.orders.filter(order => {
            const orderDate = new Date(order.date);
            return DateUtils.isSameDay(orderDate, date) && order.employeeId === employeeId;
        });
    }

    /**
     * Загрузка данных
     */
    async loadData() {
        this.state.loading = true;
        this.state.error = null;
        this.updateEmployeesList();

        try {
            // Загрузка сотрудников
            await this.loadEmployees();

            // Загрузка заказов
            await this.loadOrders();

            this.state.loading = false;
            this.updateEmployeesList();
            this.updateOrdersGrid();
        } catch (error) {
            this.state.error = 'Ошибка загрузки данных';
            this.state.loading = false;
            console.error('Ошибка загрузки данных:', error);
            this.updateEmployeesList();
            this.updateOrdersGrid();
        }
    }

    /**
     * Загрузка сотрудников
     */
    async loadEmployees() {
        // Имитация API запроса
        await new Promise(resolve => setTimeout(resolve, 500));

        // Тестовые данные
        this.state.employees = [
            {
                id: 1,
                firstName: 'Иван',
                lastName: 'Петров',
                position: 'Менеджер проектов',
                avatarColor: '#0d6efd',
                stats: { orders: 12, hours: 160 }
            },
            {
                id: 2,
                firstName: 'Мария',
                lastName: 'Сидорова',
                position: 'Разработчик',
                avatarColor: '#198754',
                stats: { orders: 8, hours: 140 }
            },
            {
                id: 3,
                firstName: 'Алексей',
                lastName: 'Иванов',
                position: 'Дизайнер',
                avatarColor: '#ffc107',
                stats: { orders: 15, hours: 180 }
            },
            {
                id: 4,
                firstName: 'Елена',
                lastName: 'Смирнова',
                position: 'Аналитик',
                avatarColor: '#dc3545',
                stats: { orders: 6, hours: 120 }
            }
        ];
    }

    /**
     * Загрузка заказов
     */
    async loadOrders() {
        // Имитация API запроса
        await new Promise(resolve => setTimeout(resolve, 800));

        // Тестовые данные
        const orders = [];
        const titles = [
            'Разработка API',
            'Дизайн интерфейса',
            'Тестирование системы',
            'Оптимизация БД',
            'Code Review',
            'Документация'
        ];

        const clients = ['ООО "Рога и копыта"', 'ИП Сидоров', 'ЗАО "Вектор"', 'ОАО "Технологии"'];
        const priorities = ['high', 'medium', 'low'];
        const statuses = ['new', 'in-progress', 'completed'];

        // Генерация заказов для текущего месяца
        const daysInMonth = DateUtils.getLastDayOfMonth(this.state.currentDate).getDate();

        this.state.employees.forEach(employee => {
            // 2-4 заказа на сотрудника
            const numOrders = Math.floor(Math.random() * 3) + 2;

            for (let i = 0; i < numOrders; i++) {
                const day = Math.floor(Math.random() * daysInMonth) + 1;
                const date = new Date(
                    this.state.currentDate.getFullYear(),
                    this.state.currentDate.getMonth(),
                    day
                );

                orders.push({
                    id: Date.now() + i,
                    title: titles[Math.floor(Math.random() * titles.length)],
                    employeeId: employee.id,
                    date: date.toISOString().split('T')[0],
                    priority: priorities[Math.floor(Math.random() * priorities.length)],
                    status: statuses[Math.floor(Math.random() * statuses.length)],
                    client: clients[Math.floor(Math.random() * clients.length)],
                    startTime: '09:00',
                    endTime: '18:00'
                });
            }
        });

        this.state.orders = orders;
    }

    /**
     * Выбор сотрудника
     */
    selectEmployee(employeeId) {
        this.state.selectedEmployee = this.state.selectedEmployee === employeeId ? null : employeeId;

        // Обновление UI
        const employeeItems = this.componentElement?.querySelectorAll('.employee-item');
        if (employeeItems) {
            employeeItems.forEach(item => {
                const itemEmployeeId = parseInt(item.dataset.employeeId);
                if (itemEmployeeId === this.state.selectedEmployee) {
                    item.classList.add('active');
                } else {
                    item.classList.remove('active');
                }
            });
        }

        // Обновление сетки заказов
        this.updateOrdersGrid();

        // Вызов callback
        if (this.config.interaction.onEmployeeSelect) {
            const employee = this.state.employees.find(e => e.id === employeeId);
            this.config.interaction.onEmployeeSelect(employee);
        }
    }

    /**
     * Переход к предыдущему месяцу
     */
    previousMonth() {
        this.state.currentDate = DateUtils.addMonths(this.state.currentDate, -1);
        this.updateMonth();
        this.loadOrders();
    }

    /**
     * Переход к следующему месяцу
     */
    nextMonth() {
        this.state.currentDate = DateUtils.addMonths(this.state.currentDate, 1);
        this.updateMonth();
        this.loadOrders();
    }

    /**
     * Переход к сегодняшней дате
     */
    goToToday() {
        this.state.currentDate = new Date();
        this.updateMonth();
        this.loadOrders();
    }

    /**
     * Обновление отображения месяца
     */
    updateMonth() {
        const monthYear = DateUtils.formatDate(this.state.currentDate, 'MMMM YYYY');
        const monthElement = this.componentElement?.querySelector('.current-month');
        if (monthElement) {
            monthElement.textContent = monthYear;
        }

        // Обновление заголовка дней
        const daysHeader = this.componentElement?.querySelector('.days-header');
        if (daysHeader) {
            const newDaysHeader = this.renderDaysHeader();
            daysHeader.parentNode.replaceChild(newDaysHeader, daysHeader);
        }

        // Обновление сетки заказов
        this.updateOrdersGrid();

        // Вызов callback
        if (this.config.interaction.onDateChange) {
            this.config.interaction.onDateChange(this.state.currentDate);
        }
    }

    /**
     * Обновление данных
     */
    refreshData() {
        this.loadData();
    }

    /**
     * Показ модального окна добавления заказа
     */
    showAddOrderModal() {
        // Создание формы
        const form = ElementFactory.createElement('form', {
            children: [
                ElementFactory.createElement('div', {
                    className: 'mb-3',
                    children: [
                        ElementFactory.createElement('label', {
                            className: 'form-label',
                            htmlFor: 'order-title',
                            text: 'Название заказа'
                        }),
                        ElementFactory.createElement('input', {
                            className: 'form-control',
                            type: 'text',
                            id: 'order-title',
                            required: true
                        })
                    ]
                }),
                ElementFactory.createElement('div', {
                    className: 'mb-3',
                    children: [
                        ElementFactory.createElement('label', {
                            className: 'form-label',
                            htmlFor: 'order-employee',
                            text: 'Сотрудник'
                        }),
                        ElementFactory.createElement('select', {
                            className: 'form-select',
                            id: 'order-employee',
                            required: true,
                            children: this.state.employees.map(employee =>
                                ElementFactory.createElement('option', {
                                    value: employee.id,
                                    text: `${employee.firstName} ${employee.lastName}`
                                })
                            )
                        })
                    ]
                })
            ]
        });

        const modal = ElementFactory.createModal('Добавить заказ', form);
        document.body.appendChild(modal);

        // Инициализация модального окна Bootstrap
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        // Очистка после закрытия
        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * Показ деталей заказа
     */
    showOrderDetails(order, employee) {
        const details = ElementFactory.createElement('div', {
            children: [
                ElementFactory.createElement('p', {
                    children: [
                        ElementFactory.createElement('strong', { text: 'Заказ: ' }),
                        ElementFactory.createElement('span', { text: order.title })
                    ]
                }),
                ElementFactory.createElement('p', {
                    children: [
                        ElementFactory.createElement('strong', { text: 'Сотрудник: ' }),
                        ElementFactory.createElement('span', { text: `${employee.firstName} ${employee.lastName}` })
                    ]
                }),
                ElementFactory.createElement('p', {
                    children: [
                        ElementFactory.createElement('strong', { text: 'Дата: ' }),
                        ElementFactory.createElement('span', {
                            text: DateUtils.formatDate(new Date(order.date), 'DD.MM.YYYY')
                        })
                    ]
                }),
                ElementFactory.createElement('p', {
                    children: [
                        ElementFactory.createElement('strong', { text: 'Статус: ' }),
                        ElementFactory.createElement('span', {
                            text: this.config.orders.statusLabels[order.status] || order.status
                        })
                    ]
                })
            ]
        });

        const modal = ElementFactory.createModal('Детали заказа', details);
        document.body.appendChild(modal);

        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', () => {
            modal.remove();
        });
    }

    /**
     * Настройка drag & drop
     */
    setupDragAndDrop() {
        if (!this.config.dragDrop.enabled) return undefined;

        // Уже настроили в renderOrderItem и renderCalendarColumn
    }

    /**
     * Обработка начала перетаскивания заказа
     */
    handleOrderDragStart(e, order) {
        if (this.config.dragDrop.dragHandle && !e.target.closest(this.config.dragDrop.dragHandle)) {
            e.preventDefault();
            return;
        }

        this.state.dragging = {
            isDragging: true,
            orderId: order.id,
            sourceElement: e.target,
            ghostElement: null
        };

        // Устанавливаем данные для переноса
        e.dataTransfer.setData('application/json', JSON.stringify({
            id: order.id,
            title: order.title,
            employeeId: order.employeeId,
            date: order.date
        }));
        e.dataTransfer.effectAllowed = 'move';

        // Добавляем класс для визуальной обратной связи
        e.target.classList.add('dragging');
        document.body.classList.add('dragging-active');
    }

    /**
     * Обработка окончания перетаскивания
     */
    handleOrderDragEnd(e) {
        if (this.state.dragging.isDragging) {
            e.target.classList.remove('dragging');
            document.body.classList.remove('dragging-active');

            // Очищаем подсветку всех зон сброса
            document.querySelectorAll('.drop-zone').forEach(zone => {
                zone.classList.remove('drop-zone-active', 'drop-zone-highlight');
            });

            this.state.dragging = {
                isDragging: false,
                orderId: null,
                sourceElement: null,
                ghostElement: null
            };
        }
    }

    /**
     * Обработка сброса заказа в ячейку
     */
    handleOrderDrop(e, date, employeeId) {
        e.preventDefault();
        e.currentTarget.classList.remove('drop-zone-active');

        try {
            const dragData = JSON.parse(e.dataTransfer.getData('application/json'));

            if (!dragData || !dragData.id) {
                console.error('Нет данных для перемещения');
                return;
            }

            const orderId = parseInt(dragData.id);
            const orderIndex = this.state.orders.findIndex(o => o.id === orderId);

            if (orderIndex === -1) {
                console.error('Заказ не найден:', orderId);
                return;
            }

            const order = this.state.orders[orderIndex];
            const oldEmployeeId = order.employeeId;
            const oldDate = order.date;
            const newDate = date.toISOString().split('T')[0];

            // Проверяем валидацию
            if (this.config.dragDrop.validateDrop) {
                const isValid = this.config.dragDrop.validateDrop({
                    order,
                    oldEmployeeId,
                    newEmployeeId: employeeId,
                    oldDate,
                    newDate
                });

                if (!isValid) {
                    console.log('Перемещение отменено валидатором');
                    return;
                }
            }

            // Обновляем заказ
            order.employeeId = employeeId;
            order.date = newDate;

            // Обновляем UI
            this.updateOrdersGrid();

            // Вызываем callback
            if (this.config.dragDrop.onOrderMove) {
                this.config.dragDrop.onOrderMove({
                    order,
                    oldEmployeeId,
                    newEmployeeId: employeeId,
                    oldDate,
                    newDate
                });
            }

            console.log(`Заказ ${orderId} перемещен:`, {
                fromEmployee: oldEmployeeId,
                toEmployee: employeeId,
                fromDate: oldDate,
                toDate: newDate
            });

        } catch (error) {
            console.error('Ошибка при обработке сброса:', error);
        }
    }

    /**
     * Начало прокрутки мышью
     */
    startMouseScroll(e) {
        // Прокручиваем только при зажатой ЛКМ и не на перетаскиваемом элементе
        if (e.button !== 0 || e.target.closest('.draggable-order')) return;

        const ordersGrid = e.currentTarget;
        this.state.scrolling = {
            isScrolling: true,
            startX: e.pageX,
            startY: e.pageY,
            scrollLeft: ordersGrid.scrollLeft,
            scrollTop: ordersGrid.scrollTop
        };

        ordersGrid.style.cursor = 'grabbing';
        ordersGrid.style.userSelect = 'none';

        e.preventDefault();
    }

    /**
     * Прокрутка мышью
     */
    doMouseScroll(e) {
        if (!this.state.scrolling.isScrolling) return;

        const ordersGrid = e.currentTarget;
        const sensitivity = this.config.scrolling.scrollSensitivity;

        // Вычисляем смещение
        const deltaX = (this.state.scrolling.startX - e.pageX) * sensitivity;
        const deltaY = (this.state.scrolling.startY - e.pageY) * sensitivity;

        // Применяем прокрутку
        ordersGrid.scrollLeft = this.state.scrolling.scrollLeft + deltaX;
        ordersGrid.scrollTop = this.state.scrolling.scrollTop + deltaY;

        // Автопрокрутка при приближении к границам
        this.handleAutoScroll(e, ordersGrid);
    }

    /**
     * Автоматическая прокрутка при перетаскивании к границам
     */
    handleAutoScroll(e, element) {
        if (!this.state.dragging.isDragging) return;

        const rect = element.getBoundingClientRect();
        const scrollSpeed = this.config.scrolling.scrollSpeed;
        const boundary = this.config.scrolling.scrollBoundary;

        // Проверка границ по горизонтали
        if (e.clientX < rect.left + boundary) {
            element.scrollLeft -= scrollSpeed;
        } else if (e.clientX > rect.right - boundary) {
            element.scrollLeft += scrollSpeed;
        }

        // Проверка границ по вертикали
        if (e.clientY < rect.top + boundary) {
            element.scrollTop -= scrollSpeed;
        } else if (e.clientY > rect.bottom - boundary) {
            element.scrollTop += scrollSpeed;
        }
    }

    /**
     * Остановка прокрутки мышью
     */
    stopMouseScroll(e) {
        if (!this.state.scrolling.isScrolling) return;

        this.state.scrolling.isScrolling = false;

        const ordersGrid = e.currentTarget;
        ordersGrid.style.cursor = '';
        ordersGrid.style.userSelect = '';
    }

    /**
     * Обновление конфигурации
     */
    updateConfig(newConfig) {
        this.config = this.mergeConfigs(this.config, newConfig);
        this.render();
        this.loadData();
    }

    /**
     * Получение текущего состояния
     */
    getState() {
        return { ...this.state };
    }

    /**
     * Установка состояния
     */
    setState(newState) {
        Object.assign(this.state, newState);
        this.updateEmployeesList();
        this.updateOrdersGrid();
    }

    /**
     * Обработчики событий для шаблонов
     */
    handleOrderClick(order) {
        if (this.config.interaction.onOrderClick) {
            this.config.interaction.onOrderClick(order);
        } else {
            const employee = this.state.employees.find(e => e.id === order.employeeId);
            this.showOrderDetails(order, employee);
        }
    }

    /**
     * Рендеринг сотрудников с использованием шаблонов
     */
    renderEmployeesList() {
        const employeesList = ElementFactory.createElement('div', {
            className: 'employees-list'
        });

        if (this.state.loading) {
            employeesList.appendChild(ElementFactory.createLoadingState('Загрузка сотрудников...'));
            return employeesList;
        }

        // Сортировка сотрудников
        const sortedEmployees = this.sortEmployees(this.state.employees);

        // Контекст для шаблона
        const templateContext = {
            onEmployeeClick: this.selectEmployee.bind(this)
        };

        // Отображение сотрудников через шаблон
        sortedEmployees.forEach(employee => {
            const isActive = this.state.selectedEmployee === employee.id;
            const employeeElement = ElementFactory.createFromTemplate(
                this.config.templates.employeeItem,
                {
                    employee: employee,
                    isActive: isActive,
                    config: this.config.employees
                },
                templateContext
            );

            employeesList.appendChild(employeeElement);
        });

        return employeesList;
    }

    /**
     * Добавление пользовательского шаблона
     */
    addTemplate(name, templateFunction) {
        ElementFactory.registerTemplate(name, templateFunction);
        this.config.templates.customTemplates[name] = templateFunction;
    }

    /**
     * Изменение используемого шаблона для элемента
     */
    setTemplateFor(elementType, templateName) {
        if (this.config.templates[elementType]) {
            this.config.templates[elementType] = templateName;
            this.render();
        }
    }

    /**
     * Обработка начала перетаскивания (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    handleDragStart(e, order) {
        if (this.config.dragDrop.dragHandle && !e.target.closest(this.config.dragDrop.dragHandle)) {
            e.preventDefault();
            return;
        }

        // Создаем данные для переноса
        const dragData = {
            id: order.id,
            title: order.title,
            employeeId: order.employeeId,
            date: order.date,
            priority: order.priority,
            status: order.status
        };

        // Устанавливаем данные в формате JSON
        e.dataTransfer.setData('application/json', JSON.stringify(dragData));
        e.dataTransfer.effectAllowed = 'move';

        // Создаем призрачное изображение
        const ghost = e.target.cloneNode(true);
        ghost.classList.add('order-ghost');
        ghost.style.position = 'fixed';
        ghost.style.width = `${e.target.offsetWidth}px`;
        ghost.style.height = `${e.target.offsetHeight}px`;
        ghost.style.left = '-1000px';
        ghost.style.top = '-1000px';
        ghost.style.opacity = '0.7';
        ghost.style.pointerEvents = 'none';
        ghost.style.zIndex = '9999';

        document.body.appendChild(ghost);
        e.dataTransfer.setDragImage(ghost, e.target.offsetWidth / 2, e.target.offsetHeight / 2);

        setTimeout(() => {
            if (ghost.parentNode) {
                ghost.parentNode.removeChild(ghost);
            }
        }, 0);

        // Обновляем состояние
        this.state.dragging = {
            isDragging: true,
            orderId: order.id,
            sourceElement: e.target,
            ghostElement: ghost,
            dragData: dragData
        };

        // Визуальная обратная связь
        e.target.classList.add('dragging');
        document.body.classList.add('dragging-active');
    }

    /**
     * Обработка окончания перетаскивания (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    handleDragEnd(e) {
        // Очищаем подсветку всех зон сброса
        document.querySelectorAll('.drop-zone').forEach(zone => {
            zone.classList.remove('drop-zone-active', 'drop-zone-highlight');
        });

        // Удаляем призрачный элемент если существует
        if (this.state.dragging.ghostElement && this.state.dragging.ghostElement.parentNode) {
            this.state.dragging.ghostElement.parentNode.removeChild(this.state.dragging.ghostElement);
        }

        // Убираем класс с исходного элемента
        if (this.state.dragging.sourceElement) {
            this.state.dragging.sourceElement.classList.remove('dragging');
        }

        // Очищаем состояние
        this.state.dragging = {
            isDragging: false,
            orderId: null,
            sourceElement: null,
            ghostElement: null,
            dragData: null
        };

        document.body.classList.remove('dragging-active');
        e.preventDefault();
    }

    /**
     * Обработка сброса заказа (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    async handleDrop(e, date, employeeId) {
        e.preventDefault();
        e.stopPropagation();

        try {
            // Получаем данные из dataTransfer
            const dragData = JSON.parse(e.dataTransfer.getData('application/json'));

            if (!dragData || !dragData.id) {
                console.error('Нет данных для перемещения');
                return;
            }

            const orderId = dragData.id;
            const orderIndex = this.state.orders.findIndex(o => o.id === orderId);

            if (orderIndex === -1) {
                console.error('Заказ не найден:', orderId);
                return;
            }

            const order = this.state.orders[orderIndex];
            const oldEmployeeId = order.employeeId;
            const oldDate = order.date;
            const newDate = date.toISOString().split('T')[0];

            // Проверяем, не пытаемся ли переместить в ту же ячейку
            if (oldEmployeeId === employeeId && oldDate === newDate) {
                console.log('Перемещение в ту же ячейку, игнорируем');
                return;
            }

            // Проверяем валидацию
            if (this.config.dragDrop.validateDrop) {
                const isValid = await this.config.dragDrop.validateDrop({
                    order,
                    oldEmployeeId,
                    newEmployeeId: employeeId,
                    oldDate,
                    newDate
                });

                if (!isValid) {
                    console.log('Перемещение отменено валидатором');
                    return;
                }
            }

            // Обновляем заказ
            order.employeeId = employeeId;
            order.date = newDate;

            // Обновляем UI
            this.updateOrdersGrid();

            // Вызываем callback
            if (this.config.dragDrop.onOrderMove) {
                this.config.dragDrop.onOrderMove({
                    order,
                    oldEmployeeId,
                    newEmployeeId: employeeId,
                    oldDate,
                    newDate
                });
            }

            console.log(`Заказ ${orderId} перемещен:`, {
                fromEmployee: oldEmployeeId,
                toEmployee: employeeId,
                fromDate: oldDate,
                toDate: newDate
            });

        } catch (error) {
            console.error('Ошибка при обработке сброса:', error);
        } finally {
            // Очищаем подсветку
            e.currentTarget.classList.remove('drop-zone-active', 'drop-zone-highlight');
        }
    }

    /**
     * Настройка горизонтальной прокрутки мышью (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    setupMouseScrolling() {
        if (!this.config.scrolling.dragToScroll) return;

        // Настраиваем после рендеринга
        setTimeout(() => {
            const ordersGrid = this.componentElement?.querySelector('.orders-grid');
            const daysHeader = this.componentElement?.querySelector('.days-header');

            if (ordersGrid) {
                // Обработчики для сетки заказов
                ordersGrid.addEventListener('mousedown', (e) => this.startHorizontalScroll(e, ordersGrid));
                ordersGrid.addEventListener('mousemove', (e) => this.doHorizontalScroll(e, ordersGrid));
                ordersGrid.addEventListener('mouseup', () => this.stopHorizontalScroll(ordersGrid));
                ordersGrid.addEventListener('mouseleave', () => this.stopHorizontalScroll(ordersGrid));

                // Добавляем стиль для курсора
                ordersGrid.style.cursor = 'grab';
            }

            if (daysHeader) {
                // Обработчики для заголовка дней
                daysHeader.addEventListener('mousedown', (e) => this.startHorizontalScroll(e, daysHeader));
                daysHeader.addEventListener('mousemove', (e) => this.doHorizontalScroll(e, daysHeader));
                daysHeader.addEventListener('mouseup', () => this.stopHorizontalScroll(daysHeader));
                daysHeader.addEventListener('mouseleave', () => this.stopHorizontalScroll(daysHeader));

                // Синхронизация прокрутки заголовка с сеткой
                if (ordersGrid) {
                    ordersGrid.addEventListener('scroll', () => {
                        daysHeader.scrollLeft = ordersGrid.scrollLeft;
                    });
                }
            }
        }, 100);
    }

    /**
     * Начало горизонтальной прокрутки (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    startHorizontalScroll(e, element) {
        // Прокручиваем только при зажатой ЛКМ и не на перетаскиваемом элементе
        if (e.button !== 0 || e.target.closest('.draggable-order') || e.target.closest('.drag-handle')) {
            return;
        }

        this.state.scrolling = {
            isScrolling: true,
            startX: e.clientX,
            startY: e.clientY,
            scrollLeft: element.scrollLeft,
            scrollTop: element.scrollTop,
            dragElement: element
        };

        element.style.cursor = 'grabbing';
        element.style.userSelect = 'none';

        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Выполнение горизонтальной прокрутки (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    doHorizontalScroll(e, element) {
        if (!this.state.scrolling.isScrolling || this.state.scrolling.dragElement !== element) {
            return;
        }

        const sensitivity = this.config.scrolling.scrollSensitivity || 2;
        const deltaX = (this.state.scrolling.startX - e.clientX) * sensitivity;

        // Применяем прокрутку
        element.scrollLeft = this.state.scrolling.scrollLeft + deltaX;

        // Синхронизируем с другим элементом если нужно
        if (element.classList.contains('orders-grid')) {
            const daysHeader = this.componentElement?.querySelector('.days-header');
            if (daysHeader) {
                daysHeader.scrollLeft = element.scrollLeft;
            }
        } else if (element.classList.contains('days-header')) {
            const ordersGrid = this.componentElement?.querySelector('.orders-grid');
            if (ordersGrid) {
                ordersGrid.scrollLeft = element.scrollLeft;
            }
        }
    }

    /**
     * Остановка горизонтальной прокрутки (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    stopHorizontalScroll(element) {
        if (!this.state.scrolling.isScrolling || this.state.scrolling.dragElement !== element) {
            return;
        }

        this.state.scrolling.isScrolling = false;
        this.state.scrolling.dragElement = null;

        element.style.cursor = 'grab';
        element.style.userSelect = '';
    }

    /**
     * Настройка синхронной вертикальной прокрутки (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    setupSyncScroll() {
        // Настраиваем после рендеринга
        setTimeout(() => {
            const employeesList = this.componentElement?.querySelector('.employees-list');
            const ordersGrid = this.componentElement?.querySelector('.orders-grid');

            if (employeesList && ordersGrid) {
                // Рассчитываем соотношение высот
                const calculateScrollRatio = () => {
                    const employeesHeight = employeesList.scrollHeight - employeesList.clientHeight;
                    const ordersHeight = ordersGrid.scrollHeight - ordersGrid.clientHeight;

                    if (employeesHeight > 0 && ordersHeight > 0) {
                        this.scrollRatio = ordersHeight / employeesHeight;
                    } else {
                        this.scrollRatio = 1;
                    }
                };

                // Рассчитываем при инициализации
                calculateScrollRatio();

                // Обработчик прокрутки списка сотрудников
                employeesList.addEventListener('scroll', (e) => {
                    if (this.state.syncScroll.isSyncing) return;

                    this.state.syncScroll.isSyncing = true;
                    const scrollTop = e.target.scrollTop;

                    // Синхронизируем прокрутку сетки заказов
                    if (this.scrollRatio) {
                        ordersGrid.scrollTop = scrollTop * this.scrollRatio;
                    }

                    setTimeout(() => {
                        this.state.syncScroll.isSyncing = false;
                    }, 10);
                });

                // Обработчик прокрутки сетки заказов
                ordersGrid.addEventListener('scroll', (e) => {
                    if (this.state.syncScroll.isSyncing) return;

                    this.state.syncScroll.isSyncing = true;
                    const scrollTop = e.target.scrollTop;

                    // Синхронизируем прокрутку списка сотрудников
                    if (this.scrollRatio && this.scrollRatio > 0) {
                        employeesList.scrollTop = scrollTop / this.scrollRatio;
                    }

                    setTimeout(() => {
                        this.state.syncScroll.isSyncing = false;
                    }, 10);
                });

                // Пересчитываем соотношение при изменении размера
                const resizeObserver = new ResizeObserver(() => {
                    calculateScrollRatio();
                });

                resizeObserver.observe(employeesList);
                resizeObserver.observe(ordersGrid);

                // Сохраняем observer для очистки
                this.resizeObserver = resizeObserver;
            }
        }, 100);
    }

    /**
     * Рендеринг календарной части (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    renderCalendarColumn() {
        const column = ElementFactory.createElement('div', {
            className: 'calendar-column'
        });

        // Заголовок с днями месяца
        const daysHeader = this.renderDaysHeader();
        column.appendChild(daysHeader);

        // Сетка с заказами
        const ordersGrid = ElementFactory.createElement('div', {
            className: 'orders-grid'
        });

        if (this.state.loading) {
            ordersGrid.appendChild(ElementFactory.createLoadingState('Загрузка заказов...'));
        } else if (this.state.error) {
            ordersGrid.appendChild(ElementFactory.createEmptyState('fas fa-exclamation-triangle', this.state.error));
        } else {
            // Фильтрация сотрудников
            let employeesToShow = this.state.employees;
            if (this.state.selectedEmployee) {
                employeesToShow = employeesToShow.filter(e => e.id === this.state.selectedEmployee);
            }

            // Создание строк для каждого сотрудника
            employeesToShow.forEach(employee => {
                const employeeRow = ElementFactory.createElement('div', {
                    className: 'employee-row'
                });

                const daysInMonth = DateUtils.getLastDayOfMonth(this.state.currentDate).getDate();

                // Создание ячеек для каждого дня
                for (let day = 1; day <= daysInMonth; day++) {
                    const date = new Date(
                        this.state.currentDate.getFullYear(),
                        this.state.currentDate.getMonth(),
                        day
                    );

                    // Поиск заказов для этого сотрудника и даты
                    const dayOrders = this.getOrdersForDateAndEmployee(date, employee.id);

                    // Создание ячейки с drop zone
                    const orderCell = this.renderOrderCell(date, employee.id, dayOrders);
                    employeeRow.appendChild(orderCell);
                }

                ordersGrid.appendChild(employeeRow);
            });
        }

        column.appendChild(ordersGrid);
        return column;
    }

    /**
     * Обновление сетки заказов (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    updateOrdersGrid() {
        const ordersGrid = this.componentElement?.querySelector('.orders-grid');
        if (!ordersGrid) return;

        const calendarColumn = ordersGrid.parentElement;
        const newCalendarColumn = this.renderCalendarColumn();
        const newOrdersGrid = newCalendarColumn.querySelector('.orders-grid');

        if (newOrdersGrid && calendarColumn) {
            // Сохраняем позицию прокрутки
            const scrollLeft = ordersGrid.scrollLeft;
            const scrollTop = ordersGrid.scrollTop;

            // Заменяем сетку
            calendarColumn.replaceChild(newOrdersGrid, ordersGrid);

            // Восстанавливаем позицию прокрутки
            newOrdersGrid.scrollLeft = scrollLeft;
            newOrdersGrid.scrollTop = scrollTop;

            // Обновляем синхронную прокрутку
            this.setupSyncScroll();
            this.setupMouseScrolling();
        }
    }

    /**
     * Обновление списка сотрудников (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    updateEmployeesList() {
        const employeesList = this.componentElement?.querySelector('.employees-list');
        if (!employeesList) return;

        // Сохраняем позицию прокрутки
        const scrollTop = employeesList.scrollTop;

        employeesList.innerHTML = '';

        if (this.state.loading) {
            employeesList.appendChild(ElementFactory.createLoadingState('Загрузка сотрудников...'));
        } else if (this.state.error) {
            employeesList.appendChild(ElementFactory.createEmptyState('fas fa-exclamation-triangle', this.state.error));
        } else if (this.state.employees.length === 0) {
            employeesList.appendChild(ElementFactory.createEmptyState('fas fa-user-friends', 'Нет сотрудников'));
        } else {
            const sortedEmployees = this.sortEmployees(this.state.employees);

            sortedEmployees.forEach(employee => {
                const isActive = this.state.selectedEmployee === employee.id;
                const employeeElement = this.renderEmployeeItem(employee, isActive);
                employeesList.appendChild(employeeElement);
            });
        }

        // Восстанавливаем позицию прокрутки
        employeesList.scrollTop = scrollTop;

        // Обновляем синхронную прокрутку
        this.setupSyncScroll();
    }

    /**
     * Привязка глобальных событий (ИСПРАВЛЕННАЯ ВЕРСИЯ)
     */
    bindEvents() {
        // Предотвращаем стандартное поведение dragover на всем документе
        document.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        // Предотвращаем стандартное поведение drop на всем документе
        document.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
        });

        // Обработка кликов по ячейкам календаря
        document.addEventListener('click', (e) => {
            const orderCell = e.target.closest('.order-cell');
            if (orderCell && !e.target.closest('.order-item')) {
                const date = orderCell.dataset.date;
                const employeeId = orderCell.dataset.employeeId;
                console.log('Клик по ячейке:', { date, employeeId });
            }
        });
    }

    /**
     * Очистка при уничтожении компонента
     */
    destroy() {
        // Удаляем resize observer
        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }

        // Удаляем обработчики событий
        const ordersGrid = this.componentElement?.querySelector('.orders-grid');
        const daysHeader = this.componentElement?.querySelector('.days-header');
        const employeesList = this.componentElement?.querySelector('.employees-list');

        if (ordersGrid) {
            ordersGrid.removeEventListener('mousedown', this.startHorizontalScroll);
            ordersGrid.removeEventListener('mousemove', this.doHorizontalScroll);
            ordersGrid.removeEventListener('mouseup', this.stopHorizontalScroll);
            ordersGrid.removeEventListener('mouseleave', this.stopHorizontalScroll);
        }

        if (daysHeader) {
            daysHeader.removeEventListener('mousedown', this.startHorizontalScroll);
            daysHeader.removeEventListener('mousemove', this.doHorizontalScroll);
            daysHeader.removeEventListener('mouseup', this.stopHorizontalScroll);
            daysHeader.removeEventListener('mouseleave', this.stopHorizontalScroll);
        }

        // Удаляем глобальные обработчики
        document.removeEventListener('dragover', this.handleGlobalDragOver);
        document.removeEventListener('drop', this.handleGlobalDrop);

        // Очищаем контейнер
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}