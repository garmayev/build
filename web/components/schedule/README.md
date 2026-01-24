🚀 Обзор
ScheduleComponent - это современный, полностью настраиваемый JavaScript-компонент для отображения расписания заказов сотрудников в формате временной шкалы. Компонент сочетает в себе лучшие черты DayPilot и jQuery Timeline, но реализован с использованием чистого JavaScript, Bootstrap 5 и поддерживает полную кастомизацию через шаблоны.

🎯 Ключевые возможности:
📅 Табличный вывод с навигацией по месяцам

👥 Динамическая загрузка сотрудников через AJAX

📦 Отображение заказов по датам и сотрудникам

🎨 Полная кастомизация через шаблоны

🖱️ Прокрутка мышью при зажатой ЛКМ

🔄 Перетаскивание заказов между сотрудниками

📱 Полная адаптивность под мобильные устройства

📁 Структура проекта
text
project/
├── index.html                 # Основной HTML-файл
├── schedule.less              # Стили компонента (LESS)
├── schedule.css               # Скомпилированные стили (CSS)
├── DateUtils.js               # Утилиты для работы с датами
├── ElementFactory.js          # Фабрика DOM-элементов с шаблонами
├── ScheduleComponent.js       # Основной класс компонента
└── app.js                     # Инициализация приложения
📦 Быстрый старт
1. Подключение зависимостей
   html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расписание заказов</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Стили компонента -->
    <link rel="stylesheet" href="schedule.css">
</head>
<body>
    <div id="schedule-container"></div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Компонент ScheduleComponent -->
    <script src="DateUtils.js"></script>
    <script src="ElementFactory.js"></script>
    <script src="ScheduleComponent.js"></script>
    <script src="app.js"></script>
</body>
</html>
2. Базовая инициализация
javascript
// app.js
document.addEventListener('DOMContentLoaded', function() {
    const schedule = new ScheduleComponent('schedule-container');
});
⚙️ Конфигурация
Полная структура конфигурации:
javascript
const config = {
    // === ОБЩИЕ НАСТРОЙКИ ===
    title: 'Расписание заказов',
    locale: 'ru-RU',

    // === ОТОБРАЖЕНИЕ СОТРУДНИКОВ ===
    employees: {
        showAvatar: true,             // Показывать аватарки
        avatarFormat: 'initials',     // 'initials' | 'firstLetter'
        nameFormat: 'full',           // 'full' | 'short'
        showPosition: true,           // Показывать должность
        showStats: true,              // Показывать статистику
        sortBy: 'name',               // 'name' | 'position' | 'orders'
        filter: null                  // Функция фильтрации
    },
    
    // === ОТОБРАЖЕНИЕ ЗАКАЗОВ ===
    orders: {
        showTime: true,               // Показывать время
        timeFormat: 'range',          // 'range' | 'duration'
        showStatus: true,             // Показывать статус
        showClient: true,             // Показывать клиента
        showPriority: true,           // Показывать приоритет
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
    
    // === НАСТРОЙКИ КАЛЕНДАРЯ ===
    calendar: {
        weekdayFormat: 'short',       // 'short' | 'full'
        showWeekends: true,           // Выделять выходные дни
        highlightToday: true,         // Выделять текущий день
        firstDayOfWeek: 1,            // 1 (понедельник) | 0 (воскресенье)
        dayCellWidth: 120             // Ширина ячейки дня в пикселях
    },
    
    // === НАСТРОЙКИ API ===
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
    
    // === ВЗАИМОДЕЙСТВИЕ ===
    interaction: {
        selectable: true,             // Разрешить выбор элементов
        editable: true,               // Разрешить редактирование
        draggable: true,              // Разрешить перетаскивание
        resizable: true,              // Разрешить изменение размера
        onOrderClick: null,           // Callback при клике на заказ
        onEmployeeSelect: null,       // Callback при выборе сотрудника
        onDateChange: null            // Callback при смене даты
    },
    
    // === DRAG & DROP ===
    dragDrop: {
        enabled: true,                // Включить перетаскивание
        ghostOpacity: 0.5,            // Прозрачность призрачного элемента
        ghostClass: 'order-ghost',    // CSS-класс призрачного элемента
        dragHandle: '.drag-handle',   // Селектор для ручки перетаскивания
        dropEffect: 'move',           // Эффект перетаскивания
        onOrderMove: null,            // Callback при перемещении заказа
        validateDrop: null            // Функция валидации перемещения
    },
    
    // === ПРОКРУТКА ===
    scrolling: {
        dragToScroll: true,           // Включить прокрутку мышью
        scrollSensitivity: 2,         // Чувствительность прокрутки
        scrollSpeed: 5,               // Скорость автопрокрутки
        scrollBoundary: 50            // Граница автопрокрутки (px)
    },
    
    // === ШАБЛОНЫ ===
    templates: {
        dayCell: 'day-cell',          // Шаблон ячейки дня
        employeeItem: 'employee-item', // Шаблон элемента сотрудника
        orderItem: 'order-item',      // Шаблон элемента заказа
        orderCell: 'order-cell',      // Шаблон ячейки заказа
        customTemplates: {}           // Пользовательские шаблоны
    }
};

// Инициализация с конфигурацией
const schedule = new ScheduleComponent('container-id', config);
🎨 Шаблоны (Templates)
📋 Использование встроенных шаблонов
javascript
// Использование шаблона для конкретного элемента
const employeeElement = ElementFactory.createFromTemplate('employee-item', {
employee: employeeData,
isActive: true,
config: config.employees
}, {
onEmployeeClick: (employeeId) => console.log('Выбран сотрудник:', employeeId)
});
🛠️ Создание собственных шаблонов
javascript
// 1. Регистрация нового шаблона
ElementFactory.registerTemplate('my-custom-order', (data, context) => {
const { order } = data;

    return ElementFactory.createElement('div', {
        className: 'custom-order',
        children: [
            ElementFactory.createElement('div', {
                className: 'custom-order-header',
                children: [
                    ElementFactory.createElement('i', {
                        className: 'fas fa-star'
                    }),
                    ElementFactory.createElement('span', {
                        text: order.title
                    })
                ]
            }),
            ElementFactory.createElement('div', {
                className: 'custom-order-details',
                children: [
                    ElementFactory.createElement('span', {
                        text: `Статус: ${order.status}`
                    })
                ]
            })
        ]
    });
});

// 2. Использование в компоненте
const schedule = new ScheduleComponent('container', {
templates: {
orderItem: 'my-custom-order',
customTemplates: {
// Дополнительные шаблоны
'my-day': (data) => { /* ... */ }
}
}
});

// 3. Динамическое изменение шаблона
schedule.setTemplateFor('orderItem', 'my-custom-order');
🏗️ Встроенные шаблоны
Шаблон	Назначение	Доступные параметры
day-cell	Ячейка дня в календаре	date, config
employee-item	Элемент сотрудника	employee, isActive, config
order-item	Элемент заказа	order, config, index
order-cell	Ячейка для заказа (drop zone)	date, employeeId, config
🖱️ Прокрутка мышью
Включение/отключение прокрутки:
javascript
// В конфигурации
const config = {
scrolling: {
dragToScroll: true,  // Включить прокрутку мышью
scrollSensitivity: 2 // Чувствительность (чем больше, тем быстрее)
}
};

// Динамически
schedule.setMouseScrollingEnabled(true);
Особенности:
Зажатие ЛКМ на свободной области календаря активирует режим прокрутки

Перетаскивание мышью прокручивает содержимое

Автопрокрутка при перетаскивании заказов к границам

Курсор изменяется на grabbing в режиме прокрутки

🔄 Drag & Drop (Перетаскивание заказов)
Базовая настройка:
javascript
const config = {
dragDrop: {
enabled: true,
onOrderMove: function(data) {
console.log('Заказ перемещен:', {
order: data.order,
fromEmployee: data.oldEmployeeId,
toEmployee: data.newEmployeeId,
fromDate: data.oldDate,
toDate: data.newDate
});

            // Отправка на сервер
            fetch('/api/orders/move', {
                method: 'POST',
                body: JSON.stringify(data)
            });
        }
    }
};
Валидация перемещений:
javascript
const config = {
dragDrop: {
enabled: true,
validateDrop: async function(data) {
// Проверка доступности сотрудника
const response = await fetch(`/api/employees/${data.newEmployeeId}/availability`);
const availability = await response.json();

            // Проверка квалификации
            const canHandle = await checkEmployeeQualification(
                data.newEmployeeId, 
                data.order.type
            );
            
            return availability.available && canHandle;
        }
    }
};
Программное управление:
javascript
// Перемещение заказа программно
schedule.moveOrder(123, 456, '2024-01-15');

// Включение/отключение drag & drop
schedule.setDragDropEnabled(true);

// Визуальная обратная связь
schedule.highlightOrderMove(123, 1000); // Подсветка на 1 секунду
Визуальные эффекты:
Призрачный элемент при перетаскивании

Подсветка зон сброса

Анимация перемещения

Автопрокрутка при приближении к границам

🔧 API компонента
Основные методы:
Метод	Описание	Пример
constructor(id, config)	Создание компонента	new ScheduleComponent('id', config)
updateConfig(config)	Обновление конфигурации	schedule.updateConfig({title: 'Новый заголовок'})
getState()	Получение текущего состояния	const state = schedule.getState()
setState(state)	Установка состояния	schedule.setState({loading: false})
refreshData()	Обновление данных	schedule.refreshData()
previousMonth()	Предыдущий месяц	schedule.previousMonth()
nextMonth()	Следующий месяц	schedule.nextMonth()
goToToday()	Текущий месяц	schedule.goToToday()
addTemplate(name, fn)	Добавление шаблона	schedule.addTemplate('my-template', fn)
setTemplateFor(type, name)	Установка шаблона	schedule.setTemplateFor('orderItem', 'custom')
moveOrder(orderId, employeeId, date)	Перемещение заказа	schedule.moveOrder(123, 456, '2024-01-15')
setDragDropEnabled(enabled)	Включение/отключение DnD	schedule.setDragDropEnabled(true)
setMouseScrollingEnabled(enabled)	Включение/отключение прокрутки	schedule.setMouseScrollingEnabled(true)
highlightOrderMove(orderId, duration)	Подсветка перемещения	schedule.highlightOrderMove(123, 1000)
getConfig()	Получение конфигурации	const config = schedule.getConfig()
События (Callbacks):
javascript
const config = {
interaction: {
// Клик по заказу
onOrderClick: function(order, employee) {
console.log('Клик по заказу:', order);
console.log('Сотрудник:', employee);
showOrderModal(order);
},

        // Выбор сотрудника
        onEmployeeSelect: function(employee) {
            console.log('Выбран сотрудник:', employee);
            updateEmployeeDetails(employee);
        },
        
        // Смена даты
        onDateChange: function(date) {
            console.log('Смена месяца:', date);
            updateStatistics(date);
        }
    },
    
    dragDrop: {
        // Перемещение заказа
        onOrderMove: function(data) {
            console.log('Перемещение:', data);
            saveOrderPosition(data);
        }
    }
};
📊 Форматы данных
Сотрудник (Employee):
javascript
{
id: 1,                              // Уникальный идентификатор (обязательно)
firstName: 'Иван',                  // Имя (обязательно)
lastName: 'Петров',                 // Фамилия (обязательно)
position: 'Менеджер проектов',      // Должность
avatarColor: '#0d6efd',             // Цвет аватарки (HEX)
stats: {                            // Статистика (опционально)
orders: 12,                     // Количество заказов
hours: 160,                     // Количество часов
efficiency: 95                  // Эффективность (%)
},
// Дополнительные пользовательские поля
department: 'Разработка',
email: 'ivan.petrov@example.com'
}
Заказ (Order):
javascript
{
id: 1,                              // Уникальный идентификатор (обязательно)
title: 'Разработка API',           // Название (обязательно)
employeeId: 1,                      // ID сотрудника (обязательно)
date: '2024-01-15',                 // Дата (обязательно, YYYY-MM-DD)
priority: 'high',                   // Приоритет: 'high' | 'medium' | 'low'
status: 'in-progress',              // Статус
client: 'ООО "Рога и копыта"',      // Клиент
description: 'Разработка REST API', // Описание
startTime: '09:00',                 // Время начала (HH:mm)
endTime: '18:00',                   // Время окончания (HH:mm)
estimatedHours: 8,                  // Оценка часов
actualHours: 7.5,                   // Фактические часы
// Дополнительные пользовательские поля
projectCode: 'API-2024',
budget: 50000
}
🔌 Интеграция с API
Пример реальной загрузки данных:
javascript
class RealDataScheduleComponent extends ScheduleComponent {
async loadEmployees() {
try {
this.setState({ loading: true, error: null });

            const response = await fetch(this.config.api.employeesUrl, {
                method: this.config.api.methods.get,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.setState({ 
                employees: data,
                loading: false 
            });
            
        } catch (error) {
            console.error('Ошибка загрузки сотрудников:', error);
            this.setState({ 
                error: 'Не удалось загрузить сотрудников',
                loading: false 
            });
        }
    }
    
    async loadOrders() {
        try {
            const month = this.state.currentDate.getMonth() + 1;
            const year = this.state.currentDate.getFullYear();
            
            const url = new URL(this.config.api.ordersUrl);
            url.searchParams.append('month', month);
            url.searchParams.append('year', year);
            
            if (this.state.selectedEmployee) {
                url.searchParams.append('employeeId', this.state.selectedEmployee);
            }
            
            const response = await fetch(url, {
                method: this.config.api.methods.get,
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.getAuthToken()}`
                }
            });
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.setState({ orders: data });
            
        } catch (error) {
            console.error('Ошибка загрузки заказов:', error);
            this.setState({ error: 'Не удалось загрузить заказы' });
        }
    }
    
    getAuthToken() {
        // Получение токена аутентификации
        return localStorage.getItem('authToken');
    }
}

// Использование
const schedule = new RealDataScheduleComponent('container', {
api: {
employeesUrl: 'https://api.example.com/employees',
ordersUrl: 'https://api.example.com/orders'
}
});
🎯 Примеры использования
Пример 1: Полное расписание с кастомными шаблонами
javascript
// Кастомный шаблон заказа
ElementFactory.registerTemplate('fancy-order', (data, context) => {
const { order } = data;
const priorityColors = {
high: '#dc3545',
medium: '#ffc107',
low: '#198754'
};

    return ElementFactory.createElement('div', {
        className: 'fancy-order',
        styles: {
            borderLeft: `4px solid ${priorityColors[order.priority]}`,
            background: `linear-gradient(135deg, ${priorityColors[order.priority]}20 0%, ${priorityColors[order.priority]}40 100%)`
        },
        children: [
            ElementFactory.createElement('div', {
                className: 'fancy-order-header',
                children: [
                    ElementFactory.createElement('i', {
                        className: 'fas fa-project-diagram'
                    }),
                    ElementFactory.createElement('span', {
                        text: order.title,
                        styles: { fontWeight: 'bold' }
                    })
                ]
            }),
            ElementFactory.createElement('div', {
                className: 'fancy-order-details',
                children: [
                    ElementFactory.createElement('span', {
                        text: `🕒 ${order.startTime} - ${order.endTime}`
                    }),
                    ElementFactory.createElement('span', {
                        text: `👤 ${order.client}`,
                        styles: { marginLeft: '10px' }
                    })
                ]
            })
        ]
    });
});

// Инициализация компонента
const schedule = new ScheduleComponent('schedule-container', {
title: 'Производственный календарь',

    employees: {
        showAvatar: true,
        avatarFormat: 'initials',
        nameFormat: 'full',
        showPosition: true,
        showStats: true,
        sortBy: 'position'
    },
    
    orders: {
        showTime: true,
        timeFormat: 'range',
        showStatus: true,
        showClient: true,
        showPriority: true
    },
    
    dragDrop: {
        enabled: true,
        onOrderMove: function(data) {
            // Сохранение на сервере
            fetch('/api/schedule/move-order', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    orderId: data.order.id,
                    newEmployeeId: data.newEmployeeId,
                    newDate: data.newDate,
                    oldEmployeeId: data.oldEmployeeId,
                    oldDate: data.oldDate
                })
            }).then(response => {
                if (!response.ok) {
                    // Откат при ошибке
                    schedule.moveOrder(
                        data.order.id, 
                        data.oldEmployeeId, 
                        data.oldDate
                    );
                    alert('Не удалось сохранить изменения');
                }
            });
        }
    },
    
    templates: {
        orderItem: 'fancy-order'
    },
    
    interaction: {
        onOrderClick: function(order) {
            openOrderEditor(order);
        },
        onEmployeeSelect: function(employee) {
            updateEmployeeCard(employee);
        }
    }
});
Пример 2: Фильтрация и поиск
javascript
const schedule = new ScheduleComponent('schedule-container', {
employees: {
filter: function(employee) {
// Фильтрация только активных сотрудников
return employee.active === true;
}
}
});

// Динамическая фильтрация
function filterByDepartment(department) {
schedule.updateConfig({
employees: {
filter: (employee) => employee.department === department
}
});
}

// Поиск сотрудников
function searchEmployees(query) {
schedule.updateConfig({
employees: {
filter: (employee) =>
employee.firstName.toLowerCase().includes(query.toLowerCase()) ||
employee.lastName.toLowerCase().includes(query.toLowerCase()) ||
employee.position.toLowerCase().includes(query.toLowerCase())
}
});
}
Пример 3: Мобильная оптимизация
javascript
// Определение мобильного устройства
const isMobile = window.matchMedia('(max-width: 768px)').matches;

const schedule = new ScheduleComponent('schedule-container', {
calendar: {
dayCellWidth: isMobile ? 80 : 120,
weekdayFormat: isMobile ? 'short' : 'full'
},

    employees: {
        showStats: !isMobile,
        showAvatar: !isMobile
    },
    
    orders: {
        showClient: !isMobile,
        showTime: !isMobile
    },
    
    scrolling: {
        dragToScroll: true,
        scrollSensitivity: isMobile ? 3 : 2
    }
});

// Адаптация при изменении размера окна
window.addEventListener('resize', () => {
const isNowMobile = window.matchMedia('(max-width: 768px)').matches;
if (isMobile !== isNowMobile) {
schedule.updateConfig({
calendar: { dayCellWidth: isNowMobile ? 80 : 120 }
});
}
});
🎨 Кастомизация стилей
Переменные LESS для кастомизации:
less
// Основные цвета
@primary-color: #0d6efd;
@secondary-color: #6c757d;
@success-color: #198754;
@danger-color: #dc3545;
@warning-color: #ffc107;
@info-color: #0dcaf0;

// Фоновые цвета
@light-color: #f8f9fa;
@dark-color: #212529;

// Границы и тени
@border-color: #dee2e6;
@shadow-color: rgba(0, 0, 0, 0.1);

// Drag & Drop
@drop-zone-color: fade(@primary-color, 10%);
@drop-zone-border: @primary-color;
@ghost-opacity: 0.5;

// Адаптивные точки останова
@breakpoint-mobile: 768px;
@breakpoint-tablet: 992px;
@breakpoint-desktop: 1200px;
CSS-классы для кастомизации:
Класс	Назначение
.schedule-component	Корневой элемент компонента
.schedule-header	Заголовок компонента
.schedule-content	Основное содержимое
.employees-column	Колонка со списком сотрудников
.calendar-column	Календарная часть
.days-header	Заголовок дней месяца
.orders-grid	Сетка с заказами
.employee-item	Элемент сотрудника
.order-item	Элемент заказа
.day-cell	Ячейка дня
.order-cell	Ячейка для заказов
.priority-high	Высокий приоритет
.priority-medium	Средний приоритет
.priority-low	Низкий приоритет
.draggable-order	Перетаскиваемый заказ
.order-ghost	Призрачный элемент при перетаскивании
.drop-zone	Зона сброса
.drop-zone-active	Активная зона сброса
.drop-zone-highlight	Подсвеченная зона сброса
📱 Адаптивность
Компонент автоматически адаптируется под разные размеры экрана:

Десктоп (≥1200px)
Две колонки: сотрудники (280px) + календарь

Полное отображение информации

Все интерактивные элементы доступны

Планшет (768px-1199px)
Вертикальная компоновка

Автоматическая прокрутка

Упрощенные элементы

Мобильный (<768px)
Горизонтальная прокрутка календаря

Компактное отображение

Touch-оптимизированные элементы

⚡ Производительность
Оптимизации:
Виртуализация для большого количества строк

Мемоизация вычислений

Отложенная загрузка изображений

Минимальные обновления DOM

Кэширование данных

Рекомендации:
До 50 сотрудников - полная загрузка

50-200 сотрудников - пагинация или виртуализация

200+ сотрудников - обязательная виртуализация

1000+ заказов - серверная пагинация

Частые обновления - кэширование на клиенте

🔧 Расширение функциональности
Добавление новых методов:
javascript
// Расширение класса компонента
class EnhancedScheduleComponent extends ScheduleComponent {
// Новый метод для экспорта данных
exportToPDF() {
const data = {
date: this.state.currentDate,
employees: this.state.employees,
orders: this.state.orders
};

        // Генерация PDF
        return generatePDF(data);
    }
    
    // Новый метод для импорта данных
    importFromJSON(jsonData) {
        this.setState({
            employees: jsonData.employees,
            orders: jsonData.orders
        });
    }
    
    // Переопределение существующего метода
    renderHeader() {
        const header = super.renderHeader();
        
        // Добавление дополнительных кнопок
        const exportButton = ElementFactory.createElement('button', {
            className: 'btn btn-outline-light',
            text: 'Экспорт',
            events: {
                click: () => this.exportToPDF()
            }
        });
        
        header.querySelector('.action-buttons').appendChild(exportButton);
        return header;
    }
}

// Использование расширенного компонента
const enhancedSchedule = new EnhancedScheduleComponent('container', config);
const pdf = enhancedSchedule.exportToPDF();
🐛 Отладка и устранение неполадок
Консольные команды для отладки:
javascript
// Доступ к компоненту через глобальную переменную
window.scheduleComponent.getState();    // Текущее состояние
window.scheduleComponent.getConfig();   // Текущая конфигурация

// Тестирование методов
window.scheduleComponent.moveOrder(123, 456, '2024-01-15');
window.scheduleComponent.refreshData();

// Проверка данных
console.log('Сотрудники:', window.scheduleComponent.state.employees);
console.log('Заказы:', window.scheduleComponent.state.orders);
Типичные проблемы и решения:
Компонент не отображается

Проверьте ID контейнера

Проверьте подключение скриптов

Проверьте консоль на ошибки

Данные не загружаются

Проверьте API endpoints

Проверьте CORS настройки

Проверьте формат данных

Drag & Drop не работает

Проверьте dragDrop.enabled

Проверьте обработчики событий

Проверьте CSS-классы

Прокрутка мышью не работает

Проверьте scrolling.dragToScroll

Проверьте наличие calendar-column

Проверьте обработчики событий

📄 Лицензия
MIT License

Copyright (c) 2024 ScheduleComponent

Разрешается бесплатное использование, копирование, изменение, объединение, публикация, распространение, сублицензирование и/или продажа копий программного обеспечения при условии, что вышеуказанное уведомление об авторском праве и данное уведомление о разрешении включаются во все копии или существенные части программного обеспечения.

🔄 Обновления
Версия 2.0 (Текущая)
Поддержка шаблонов

Drag & Drop между сотрудниками

Прокрутка мышью

Улучшенная производительность

Планируемые обновления:
Поддержка временных интервалов в течение дня

Группировка заказов по проектам

Офлайн-режим с IndexedDB

Экспорт в Excel/PDF

Интеграция с календарями (Google, Outlook)

📞 Поддержка
При возникновении проблем:

Проверьте консоль браузера на наличие ошибок

Убедитесь, что все зависимости подключены

Проверьте формат передаваемых данных

Используйте window.scheduleComponent для отладки

Создайте issue в репозитории проекта

🚀 Рекомендации по использованию
Для больших наборов данных используйте виртуализацию

Для мобильных устройств оптимизируйте отображение

Для частых обновлений реализуйте кэширование

Для сложной логики создавайте кастомные шаблоны

Для интеграции с API используйте наследование классов

📚 Дополнительные ресурсы
Примеры использования

Демо приложение

Видео-руководство

Сообщество разработчиков

*Документация обновлена: 2024-01-15*
Версия компонента: 2.0.0
Автор: Команда разработки ScheduleComponent