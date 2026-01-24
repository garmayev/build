class ElementFactory {
    /**
     * Реестр шаблонов
     */
    static templates = {};

    /**
     * Регистрация шаблона
     */
    static registerTemplate(name, templateFunction) {
        this.templates[name] = templateFunction;
    }

    /**
     * Создание элемента с возможностью использования шаблонов
     */
    static createElement(tag, options = {}) {
        const element = document.createElement(tag);

        // Использование шаблона, если указан
        if (options.template && this.templates[options.template]) {
            return this.templates[options.template](options.data, options.context);
        }

        // Стандартная логика создания элементов...
        if (options.className) {
            const classes = Array.isArray(options.className)
                ? options.className
                : options.className.split(' ');
            element.classList.add(...classes.filter(c => c));
        }

        if (options.attributes) {
            Object.entries(options.attributes).forEach(([key, value]) => {
                element.setAttribute(key, value);
            });
        }

        if (options.dataset) {
            Object.entries(options.dataset).forEach(([key, value]) => {
                element.dataset[key] = value;
            });
        }

        if (options.styles) {
            Object.assign(element.style, options.styles);
        }

        if (options.text !== undefined) {
            element.textContent = options.text;
        }

        if (options.html !== undefined) {
            element.innerHTML = options.html;
        }

        if (options.children) {
            options.children.forEach(child => {
                if (child instanceof HTMLElement) {
                    element.appendChild(child);
                } else if (typeof child === 'string') {
                    element.appendChild(document.createTextNode(child));
                }
            });
        }

        if (options.events) {
            Object.entries(options.events).forEach(([event, handler]) => {
                element.addEventListener(event, handler);
            });
        }

        // Поддержка drag & drop
        if (options.draggable !== undefined) {
            element.draggable = options.draggable;
            if (options.draggable) {
                element.addEventListener('dragstart', options.onDragStart || (() => {}));
                element.addEventListener('dragend', options.onDragEnd || (() => {}));
            }
        }

        if (options.dropZone) {
            element.addEventListener('dragover', options.onDragOver || ((e) => {
                e.preventDefault();
                element.classList.add('drop-zone-active');
            }));
            element.addEventListener('dragleave', options.onDragLeave || (() => {
                element.classList.remove('drop-zone-active');
            }));
            element.addEventListener('drop', options.onDrop || (() => {}));
        }

        return element;
    }

    /**
     * Создание элемента с помощью шаблона
     */
    static createFromTemplate(templateName, data = {}, context = {}) {
        if (!this.templates[templateName]) {
            console.error(`Шаблон "${templateName}" не найден`);
            return this.createElement('div', { text: 'Шаблон не найден' });
        }
        return this.templates[templateName](data, context);
    }

    /**
     * Регистрация стандартных шаблонов
     */
    static registerDefaultTemplates() {
        // Шаблон дня месяца
        this.registerTemplate('day-cell', (data, context) => {
            const { date, config } = data;
            const dayOfWeek = DateUtils.getDayOfWeek(date);
            const isWeekend = dayOfWeek >= 6;
            const isToday = DateUtils.isSameDay(date, new Date());
            const dayName = config.weekdayFormat === 'short'
                ? DateUtils.getWeekdaysNames()[dayOfWeek - 1]
                : DateUtils.formatDate(date, 'dddd');
            const dayNumber = DateUtils.formatDate(date, 'D');

            return this.createElement('div', {
                className: `day-cell ${isWeekend ? 'weekend' : ''} ${isToday ? 'today' : ''}`,
                dataset: {
                    date: date.toISOString().split('T')[0],
                    day: date.getDate()
                },
                children: [
                    this.createElement('div', {
                        className: 'day-name',
                        text: dayName
                    }),
                    this.createElement('div', {
                        className: 'day-number',
                        text: dayNumber
                    })
                ]
            });
        });

        // Шаблон сотрудника
        this.registerTemplate('employee-item', (data, context) => {
            const { employee, isActive, config } = data;
            const { onEmployeeClick } = context;

            const avatarText = config.avatarFormat === 'initials'
                ? `${employee.firstName[0]}${employee.lastName[0]}`
                : employee.firstName[0];

            const avatarColor = employee.avatarColor || '#0d6efd';

            return this.createElement('div', {
                className: `employee-item ${isActive ? 'active' : ''}`,
                dataset: {
                    employeeId: employee.id,
                    employeeName: `${employee.firstName} ${employee.lastName}`
                },
                attributes: {
                    'data-employee-id': employee.id
                },
                events: {
                    click: () => onEmployeeClick && onEmployeeClick(employee.id)
                },
                children: [
                    config.showAvatar && this.createElement('div', {
                        className: 'employee-avatar',
                        styles: { backgroundColor: avatarColor },
                        text: avatarText
                    }),

                    this.createElement('div', {
                        className: 'employee-info',
                        children: [
                            this.createElement('div', {
                                className: 'employee-name',
                                text: config.nameFormat === 'full'
                                    ? `${employee.firstName} ${employee.lastName}`
                                    : `${employee.firstName} ${employee.lastName[0]}.`
                            }),

                            config.showPosition && this.createElement('div', {
                                className: 'employee-position',
                                text: employee.position
                            }),

                            config.showStats && employee.stats && this.createElement('div', {
                                className: 'employee-stats',
                                children: [
                                    this.createElement('div', {
                                        className: 'stat',
                                        children: [
                                            this.createElement('i', {
                                                className: 'fas fa-tasks stat-icon'
                                            }),
                                            this.createElement('span', {
                                                text: employee.stats.orders || 0
                                            })
                                        ]
                                    }),
                                    this.createElement('div', {
                                        className: 'stat',
                                        children: [
                                            this.createElement('i', {
                                                className: 'fas fa-clock stat-icon'
                                            }),
                                            this.createElement('span', {
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
        });

        // Шаблон заказа с поддержкой drag & drop
        this.registerTemplate('order-item', (data, context) => {
            const { order, config, index = 0 } = data;
            const { onOrderClick, onDragStart, onDragEnd } = context;

            const priorityClass = `priority-${order.priority}`;
            const timeText = order.time
                ? order.time
                : config.timeFormat === 'range'
                    ? `${order.startTime || '9:00'} - ${order.endTime || '18:00'}`
                    : 'Весь день';

            const statusText = config.statusLabels?.[order.status] || order.status;

            const element = this.createElement('div', {
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
                onDragStart: onDragStart || ((e) => {
                    e.dataTransfer.setData('application/json', JSON.stringify({
                        id: order.id,
                        title: order.title,
                        employeeId: order.employeeId,
                        date: order.date
                    }));
                    e.dataTransfer.effectAllowed = 'move';
                    element.classList.add('dragging');
                }),
                onDragEnd: onDragEnd || (() => {
                    element.classList.remove('dragging');
                }),
                styles: {
                    top: `${5 + index * 75}px`,
                    height: '70px',
                    cursor: 'move'
                },
                events: {
                    click: (e) => {
                        e.stopPropagation();
                        onOrderClick && onOrderClick(order);
                    }
                },
                children: [
                    this.createElement('div', {
                        className: 'order-title',
                        text: order.title,
                        attributes: { title: order.title }
                    }),

                    config.showTime && this.createElement('div', {
                        className: 'order-time',
                        text: timeText
                    }),

                    config.showStatus && this.createElement('div', {
                        className: ['order-status', `status-${order.status.replace('-', '')}`],
                        text: statusText
                    }),

                    config.showClient && order.client && this.createElement('div', {
                        className: 'order-client',
                        text: order.client,
                        styles: {
                            fontSize: '0.75rem',
                            color: '#6c757d',
                            marginTop: '0.25rem'
                        }
                    }),

                    // Индикатор перетаскивания
                    this.createElement('div', {
                        className: 'drag-handle',
                        styles: {
                            position: 'absolute',
                            right: '5px',
                            top: '5px',
                            cursor: 'move',
                            opacity: '0.5'
                        },
                        children: [
                            this.createElement('i', {
                                className: 'fas fa-arrows-alt'
                            })
                        ]
                    })
                ].filter(Boolean)
            });

            return element;
        });

        // Шаблон ячейки для перетаскивания заказов
        this.registerTemplate('order-cell', (data, context) => {
            const { date, employeeId, config } = data;
            const { onDrop, onDragOver, onDragLeave } = context;

            const isToday = DateUtils.isSameDay(new Date(date), new Date());

            return this.createElement('div', {
                className: `order-cell ${isToday ? 'today' : ''} drop-zone`,
                dataset: {
                    date: date,
                    employeeId: employeeId
                },
                attributes: {
                    'data-date': date,
                    'data-employee-id': employeeId
                },
                dropZone: true,
                onDrop: onDrop || ((e) => {
                    e.preventDefault();
                    e.currentTarget.classList.remove('drop-zone-active');
                }),
                onDragOver: onDragOver || ((e) => {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    e.currentTarget.classList.add('drop-zone-active');
                }),
                onDragLeave: onDragLeave || ((e) => {
                    e.currentTarget.classList.remove('drop-zone-active');
                })
            });
        });
    }

    /**
     * Инициализация фабрики с шаблонами
     */
    static init() {
        this.registerDefaultTemplates();
    }

    /**
     * Создает кнопку с иконкой
     */
    static createIconButton(icon, className = '', onClick = null) {
        const button = this.createElement('button', {
            className: ['btn', ...className.split(' ')],
            children: [
                this.createElement('i', {
                    className: icon,
                    attributes: { 'aria-hidden': 'true' }
                })
            ]
        });

        if (onClick) {
            button.addEventListener('click', onClick);
        }

        return button;
    }

    /**
     * Создает элемент загрузки
     */
    static createLoadingState(message = 'Загрузка...') {
        return this.createElement('div', {
            className: 'loading-state',
            children: [
                this.createElement('div', { className: 'spinner' }),
                this.createElement('p', {
                    className: 'loading-text',
                    text: message
                })
            ]
        });
    }

    /**
     * Создает пустое состояние
     */
    static createEmptyState(icon = 'fas fa-inbox', message = 'Нет данных') {
        return this.createElement('div', {
            className: 'empty-state',
            children: [
                this.createElement('i', {
                    className: ['empty-icon', icon]
                }),
                this.createElement('p', {
                    className: 'empty-text',
                    text: message
                })
            ]
        });
    }

    /**
     * Создает элемент дня месяца
     */
    static createDayCell(date, config) {
        const dayOfWeek = DateUtils.getDayOfWeek(date);
        const isWeekend = dayOfWeek >= 6;
        const isToday = DateUtils.isSameDay(date, new Date());

        const dayName = config.weekdayFormat === 'short'
            ? DateUtils.getWeekdaysNames()[dayOfWeek - 1]
            : DateUtils.formatDate(date, 'dddd');

        const dayNumber = DateUtils.formatDate(date, 'D');

        return this.createElement('div', {
            className: `day-cell ${isWeekend ? 'weekend' : ''} ${isToday ? 'today' : ''}`,
            dataset: { date: date.toISOString().split('T')[0] },
            children: [
                this.createElement('div', {
                    className: 'day-name',
                    text: dayName
                }),
                this.createElement('div', {
                    className: 'day-number',
                    text: dayNumber
                })
            ]
        });
    }

    /**
     * Создает элемент сотрудника
     */
    static createEmployeeItem(employee, isActive = false, config) {
        const avatarText = config.avatarFormat === 'initials'
            ? `${employee.firstName[0]}${employee.lastName[0]}`
            : employee.firstName[0];

        const avatarColor = employee.avatarColor || '#0d6efd';

        return this.createElement('div', {
            className: `employee-item ${isActive ? 'active' : ''}`,
            dataset: { employeeId: employee.id },
            children: [
                // Аватар
                config.showAvatar && this.createElement('div', {
                    className: 'employee-avatar',
                    styles: { backgroundColor: avatarColor },
                    text: avatarText
                }),

                // Информация
                this.createElement('div', {
                    className: 'employee-info',
                    children: [
                        // Имя
                        this.createElement('div', {
                            className: 'employee-name',
                            text: config.nameFormat === 'full'
                                ? `${employee.firstName} ${employee.lastName}`
                                : `${employee.firstName} ${employee.lastName[0]}.`
                        }),

                        // Должность
                        config.showPosition && this.createElement('div', {
                            className: 'employee-position',
                            text: employee.position
                        }),

                        // Статистика
                        config.showStats && employee.stats && this.createElement('div', {
                            className: 'employee-stats',
                            children: [
                                this.createElement('div', {
                                    className: 'stat',
                                    children: [
                                        this.createElement('i', {
                                            className: 'fas fa-tasks stat-icon'
                                        }),
                                        this.createElement('span', {
                                            text: employee.stats.orders || 0
                                        })
                                    ]
                                }),
                                this.createElement('div', {
                                    className: 'stat',
                                    children: [
                                        this.createElement('i', {
                                            className: 'fas fa-clock stat-icon'
                                        }),
                                        this.createElement('span', {
                                            text: employee.stats.hours || '0h'
                                        })
                                    ]
                                })
                            ]
                        })
                    ]
                })
            ]
        });
    }

    /**
     * Создает элемент заказа
     */
    static createOrderItem(order, config) {
        const priorityClass = `priority-${order.priority}`;
        const statusClass = `status-${order.status}`;

        const timeText = order.time
            ? order.time
            : config.timeFormat === 'range'
                ? `${order.startTime || '9:00'} - ${order.endTime || '18:00'}`
                : 'Весь день';

        const statusText = config.statusLabels?.[order.status] || order.status;

        return this.createElement('div', {
            className: `order-item ${priorityClass}`,
            dataset: { orderId: order.id },
            children: [
                // Заголовок
                this.createElement('div', {
                    className: 'order-title',
                    text: order.title,
                    attributes: { title: order.title }
                }),

                // Время
                config.showTime && this.createElement('div', {
                    className: 'order-time',
                    text: timeText
                }),

                // Статус
                config.showStatus && this.createElement('div', {
                    className: ['order-status', statusClass],
                    text: statusText
                }),

                // Клиент
                config.showClient && order.client && this.createElement('div', {
                    className: 'order-client',
                    text: order.client,
                    styles: {
                        fontSize: '0.75rem',
                        color: '#6c757d',
                        marginTop: '0.25rem'
                    }
                })
            ]
        });
    }

    /**
     * Создает модальное окно
     */
    static createModal(title, content, footer = null) {
        return this.createElement('div', {
            className: 'modal fade schedule-modal',
            attributes: {
                tabindex: '-1',
                'aria-hidden': 'true'
            },
            children: [
                this.createElement('div', {
                    className: 'modal-dialog modal-dialog-centered',
                    children: [
                        this.createElement('div', {
                            className: 'modal-content',
                            children: [
                                // Заголовок
                                this.createElement('div', {
                                    className: 'modal-header',
                                    children: [
                                        this.createElement('h5', {
                                            className: 'modal-title',
                                            text: title
                                        }),
                                        this.createElement('button', {
                                            className: 'btn-close',
                                            attributes: {
                                                type: 'button',
                                                'data-bs-dismiss': 'modal',
                                                'aria-label': 'Close'
                                            }
                                        })
                                    ]
                                }),

                                // Содержимое
                                this.createElement('div', {
                                    className: 'modal-body',
                                    children: [content]
                                }),

                                // Подвал
                                footer && this.createElement('div', {
                                    className: 'modal-footer',
                                    children: [footer]
                                })
                            ]
                        })
                    ]
                })
            ]
        });
    }
}

// Автоматическая инициализация при загрузке
if (typeof window !== 'undefined') {
    ElementFactory.init();
}