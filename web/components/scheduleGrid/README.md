# Документация компонента Calendar
## Обзор

Компонент Calendar - это JavaScript-библиотека для отображения календаря рабочих часов сотрудников с поддержкой финансового учета. Компонент полностью нативный, не требует зависимостей от React, Vue или других фреймворков.

## Установка
```html
<!-- Подключение CSS -->
<link rel="stylesheet" href="scheduleGrid.css">

<!-- Подключение JS -->
<script src="scheduleGrid.js"></script>

<!-- Контейнер для календаря -->
<div id="calendar-container"></div>

<!-- Инициализация -->
<script>
    const calendar = new Calendar({
        container: document.getElementById('calendar-container'),
        // ... настройки
    });
</script>
```

## Конфигурация
### Основные настройки
```javascript
const calendar = new Calendar({
    // Обязательные параметры
    container: document.getElementById('calendar-container'), // DOM-элемент для рендеринга

    // Основные настройки
    language: 'ru', // Язык интерфейса
    authToken: 'your_token_here', // Токен авторизации для API
    apiUrl: 'https://api.example.com', // Базовый URL API

    // Настройки AJAX
    ajax: {
        enabled: true, // Включить AJAX-запросы
        endpoints: {
            getHours: '/api/hours/calendar', // Получение данных о часах
            getPrice: '/api/price', // Получение цены
            setHours: '/api/hours', // Сохранение часов
            markPaid: '/api/hours/paid' // Отметка как оплачено
        },
        request: null, // Кастомная функция запроса
        customRequest: null // Альтернативная кастомная функция
    },

    // Статические данные (если ajax.enabled = false)
    staticData: null,

    // Настройки модальных окон
    modal: {
        external: false, // Использовать внешнее модальное окно
        container: 'my-modal-id', // ID внешнего модального окна
        template: null // Шаблон модального окна
    },

    // Переводы
    translations: {
        'calendar.back': 'Назад',
        'calendar.next': 'Вперед',
        'calendar.search_text': 'Поиск...',
        // ... другие переводы
    },

    // Названия месяцев
    monthNames: [
        'Январь', 'Февраль', 'Март', // ... все месяцы
    ],

    // Классы CSS
    classes: {
        container: 'calendar-container',
        table: 'calendar-table',
        header: 'calendar-header',
        // ... другие классы
    },

    // Обработчики событий
    events: {
        onDayClick: null,
        onMonthChange: null,
        // ... другие события
    },

    // Шаблоны
    templates: {
        dayCell: null,
        employeeCell: null,
        // ... другие шаблоны
    }
});
```

### Детальное описание настроек
#### Настройки AJAX
| Параметр	                 | Тип	       | По умолчанию	        | Описание                        |
|---------------------------|------------|----------------------|---------------------------------|
| enabled	                  | boolean	   | true	                | Включить/выключить AJAX-запросы |
| endpoints.getHours	       | string	    | /api/hours/calendar	 | Эндпоинт для получения данных   |
| endpoints.getPrice	       | string	    | /api/price	          | Эндпоинт для получения цены     |
| endpoints.setHours	       | string	    | /api/hours	          | Эндпоинт для сохранения часов   |
| endpoints.markPaid	       | string	    | /api/hours/paid	     | Эндпоинт для отметки оплаты     |
| request	                  | function	  | null	                | Функция для выполнения запросов |
| customRequest	            | function	  | null	                | Альтернативная функция запросов |

#### Обработчики событий (events)
| Событие	      | Параметры	                 | Описание                 |
|---------------|----------------------------|--------------------------|
| onDayClick    | (cellData, calendar)	      | Клик по ячейке дня       |
| onMonthChange | ({year, month}, calendar)	 | Смена месяца             |
| onSearch	     | (searchText, calendar)	    | Поиск по сотрудникам     |
| onModeToggle	 | (isFinanceMode, calendar)	 | Переключение режима      |
| onHover	     | ({event, data}, calendar)	 | Наведение на ячейку      |
| onHoverLeave	 | ({event, data}, calendar)	 | Уход курсора с ячейки    |
| onModalOpen	 | (data, calendar)	          | Открытие модального окна |
| onModalClose	 | (calendar)	                | Закрытие модального окна |
| onAddHours	 | (cellData, calendar)	      | Добавление новых часов   |
| onPay	     | (item, calendar)	          | Оплата записи            |
| onDataLoad	 | (data, calendar)	          | Загрузка данных          |
| onDataError	 | (error, calendar)	         | Ошибка загрузки данных   |

#### Шаблоны (templates)
```
templates: {
    // Шаблон ячейки дня
    dayCell: function(data) {
        // data = { day, value, isPast, hasData, isFinance }
        return `<div class="custom-day-cell">${data.day}</div>`;
    },

    // Шаблон ячейки сотрудника
    employeeCell: function(data) {
        // data = { value, isPast, hasData, isFree, dateStr, userId, ... }
        return `<td class="custom-cell">${data.value}</td>`;
    },

    // Шаблон строки сотрудника
    employeeRow: function(data) {
        // data = { employee, cells, index, isFinance }
        return `<tr>${data.cells}</tr>`;
    },

    // Шаблон заголовка
    header: function(data) {
        // data = { monthName, year, daysInMonth }
        return `<thead>...</thead>`;
    },

    // Шаблон всплывающего окна
    hoverPopup: function(data) {
        // data = { period, count, sum, is_payed, position }
        return `<div class="popup">...</div>`;
    }
}
```

### API методов
#### Основные методы

`setAuthToken(token)`
Устанавливает токен авторизации и перезагружает данные.

```javascript
calendar.setAuthToken('new_token_here');
```

`setLanguage(lang)`
Изменяет язык интерфейса.

```javascript
calendar.setLanguage('en');
```

`setData(data)`
Устанавливает статические данные (обходит AJAX).

```
calendar.setData([
    {
        user: { id: 1, profile: { name: "Иван", family: "Иванов" } },
        hours: [...],
        debit_hours: 40,
        credit_hours: 10,
        debit_amount: 40000,
        credit_amount: 10000
    }
]);
```

`refresh()`
Перезагружает данные.

```javascript
calendar.refresh();
```

`getCurrentDate()`
Возвращает текущую дату календаря.

```javascript
const date = calendar.getCurrentDate();
// { year: 2024, month: 0, monthName: 'Январь' }
```

`updateConfig(newConfig)`
Обновляет конфигурацию.

```
calendar.updateConfig({
    ajax: {
        enabled: false
    },
    staticData: [...]
});
```

`destroy()`
Уничтожает компонент и очищает DOM.

```javascript
calendar.destroy();
```

### Методы навигации
`prevMonth()`
Переход к предыдущему месяцу.

```javascript
calendar.prevMonth();
```

`nextMonth()`
Переход к следующему месяцу.

```javascript
calendar.nextMonth();
```

### Вспомогательные методы

`t(key)`
Получение перевода по ключу.

```javascript
const text = calendar.t('calendar.total'); // "Итого"
```

`zeroPad(num, length)`
Добавление ведущих нулей.

```javascript
const padded = calendar.zeroPad(5, 2); // "05"
```

`formatDate(day)`
Форматирование даты.

```javascript
const dateStr = calendar.formatDate(15); // "2024-01-15"
```

`isBefore(date1, date2)`
Сравнение дат.

```javascript
const isPast = calendar.isBefore(someDate, new Date());
```

## Структура данных
Формат данных сотрудника
```json
{
    "user": {
        "id": 1,
        "profile": {
            "name": "Иван",
            "family": "Иванов",
            "surname": "Иванович"
        },
        "username": "ivanov"
    },
    "hours": [
        {
            "id": 123,
            "date": "2024-01-15",
            "count": 8,
            "price": 100,
            "is_payed": true,
            "order_id": 456,
            "start_time": "2024-01-15 09:00:00",
            "stop_time": "2024-01-15 18:00:00"
        }
    ],
    "debit_hours": 40,
    "credit_hours": 10,
    "debit_amount": 4000,
    "credit_amount": 1000
}
```

Формат данных ячейки (cellData)
```
{
    employee: {...},      // Данные сотрудника
    dateStr: "2024-01-15", // Дата в формате YYYY-MM-DD
    cellDate: Date,       // Объект Date
    hasData: true,        // Есть ли данные за этот день
    dayData: [...],       // Массив записей за день
    value: 8,            // Значение для отображения
    isPast: true,         // Прошедший ли день
    isFinance: false      // Финансовый режим
}
```

### События
Пример использования событий
```javascript
const calendar = new Calendar({
    // ... настройки

    events: {
        onDayClick: function(cellData, calendar) {
            console.log('Клик по ячейке:', cellData);

            if (cellData.hasData) {
                // Открыть детали
                this.openDetailsModal(cellData.dayData);
            } else {
                // Добавить новую запись
                this.openAddHoursModal(cellData);
            }
        },

        onMonthChange: function(dateInfo, calendar) {
            console.log('Месяц изменен:', dateInfo.year, dateInfo.month);
            // Можно обновить другие компоненты страницы
        },

        onDataError: function(error, calendar) {
            console.error('Ошибка данных:', error);

            if (error.message.includes('401')) {
                // Перенаправление на логин
                window.location.href = '/login';
            } else {
                // Показать уведомление
                this.showNotification('Ошибка загрузки данных', 'error');
            }
        }
    }
});
```

### Кастомные AJAX-запросы
Пример кастомной функции запроса
```javascript
const calendar = new Calendar({
    // ... другие настройки

    ajax: {
        enabled: true,
        endpoints: {
            getHours: '/api/hours/calendar'
        },

        // Кастомная функция запроса
        request: function(url, options) {
            // Добавляем дополнительные заголовки
            const headers = new Headers({
                'Authorization': `Bearer ${this.config.authToken}`,
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            });

            // Добавляем CSRF-токен
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
            if (csrfToken) {
                headers.append('X-CSRF-Token', csrfToken);
            }

            return fetch(url, {
                ...options,
                headers: headers,
                credentials: 'include' // Для отправки кук
            })
            .then(response => {
                // Обработка различных статусов
                if (response.status === 401) {
                    // Авторизация истекла
                    window.dispatchEvent(new CustomEvent('auth-expired'));
                    throw new Error('Authentication required');
                }

                if (response.status === 403) {
                    // Нет доступа
                    throw new Error('Access denied');
                }

                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}`);
                }

                return response.json();
            })
            .catch(error => {
                console.error('API Error:', error);
                throw error;
            });
        }
    }
});
```

Пример с использованием jQuery.ajax
```
ajax: {
    enabled: true,
    customRequest: function(url, options) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: url,
                type: options.method || 'GET',
                data: options.body ? JSON.parse(options.body) : null,
                headers: {
                    'Authorization': `Bearer ${token}`,
                    ...options.headers
                },
                success: resolve,
                error: (xhr, status, error) => {
                    reject(new Error(error || 'Request failed'));
                }
            });
        });
    }
}
```

### Модальные окна
Встроенное модальное окно
По умолчанию компонент использует встроенное простое модальное окно.

Внешнее модальное окно
```html
<!-- Внешнее модальное окно -->
<div id="external-modal" style="display: none;">
    <div class="modal-content">
        <h3 id="modal-title"></h3>
        <div id="modal-body"></div>
        <div class="modal-actions">
            <button onclick="handlePay()">Оплатить</button>
            <button onclick="handleClose()">Закрыть</button>
        </div>
    </div>
</div>

<script>
// Глобальные функции для управления модальным окном
window.showCalendarModal = function(data, callbacks) {
    const modal = document.getElementById('external-modal');
    // ... заполнение модального окна данными
    modal.style.display = 'block';
    window.calendarCallbacks = callbacks;
};

window.hideCalendarModal = function() {
    document.getElementById('external-modal').style.display = 'none';
};

const calendar = new Calendar({
    modal: {
        external: true,
        container: 'external-modal'
    }
});
</script>
```

### Кастомизация стилей
CSS классы
Компонент использует следующие классы по умолчанию:

```
/* Основные классы */
.calendar-container { ... }
.calendar-table { ... }

/* Ячейки */
.employee-cell { ... }
.employee-cell.disabled { ... }
.employee-cell.free { ... }
.employee-cell.has-data { ... }

/* Заголовки */
.calendar-header { ... }
.nav-button { ... }
.month-title { ... }

/* Режимы */
.paid-amount { ... }
.unpaid-amount { ... }
.total-amount { ... }

/* Состояния */
.calendar-error { ... }
.empty-data { ... }
Пример кастомизации стилей
css
/* Переопределение стилей */
#calendar-container .calendar-table {
    border: 2px solid #333;
    font-family: 'Arial', sans-serif;
}

#calendar-container .employee-cell {
    min-width: 60px;
    height: 60px;
    font-size: 16px;
}

#calendar-container .employee-cell.free {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

#calendar-container .employee-cell.has-data {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    font-weight: bold;
}

#calendar-container .nav-button {
    background: #4CAF50;
    color: white;
    border-radius: 20px;
    padding: 10px 20px;
}
```

### Полный пример использования
```html
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Календарь рабочих часов</title>
    <link rel="stylesheet" href="scheduleGrid.css">
    <style>
        .custom-styles {
            /* Дополнительные стили */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Календарь рабочих часов</h1>

        <div class="controls">
            <label>
                <input type="checkbox" id="finance-mode">
                Финансовый режим
            </label>
            <button id="refresh-btn">Обновить</button>
            <button id="today-btn">Сегодня</button>
        </div>

        <div id="calendar-container"></div>

        <!-- Внешнее модальное окно (опционально) -->
        <div id="details-modal" class="modal" style="display: none;">
            <div class="modal-content">
                <span class="close">&times;</span>
                <div id="modal-content"></div>
            </div>
        </div>
    </div>

    <script src="scheduleGrid.js"></script>
    <script>
        // Глобальная функция для показа модального окна
        window.showDetailsModal = function(content) {
            const modal = document.getElementById('details-modal');
            document.getElementById('modal-content').innerHTML = content;
            modal.style.display = 'block';

            // Закрытие по клику на X
            modal.querySelector('.close').onclick = function() {
                modal.style.display = 'none';
            };

            // Закрытие по клику вне модального окна
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            };
        };

        // Инициализация календаря
        document.addEventListener('DOMContentLoaded', function() {
            const calendar = new Calendar({
                container: document.getElementById('calendar-container'),
                language: 'ru',
                authToken: '<?= $authToken ?>', // Токен из PHP
                apiUrl: 'https://api.example.com',

                ajax: {
                    enabled: true,
                    endpoints: {
                        getHours: '/api/hours/calendar'
                    }
                },

                events: {
                    onDayClick: function(cellData, calendar) {
                        if (cellData.hasData) {
                            const content = `
                                <h3>Детали за ${cellData.dateStr}</h3>
                                <pre>${JSON.stringify(cellData.dayData, null, 2)}</pre>
                            `;
                            window.showDetailsModal(content);
                        } else {
                            const hours = prompt(
                                `Введите количество часов для ${cellData.employee.user.profile.name}:`,
                                '8'
                            );
                            if (hours) {
                                // Логика сохранения...
                            }
                        }
                    },

                    onDataLoad: function(data, calendar) {
                        console.log(`Загружено ${data.length} записей`);
                    },

                    onDataError: function(error, calendar) {
                        alert('Ошибка загрузки данных: ' + error.message);
                    }
                }
            });

            // Устанавливаем кастомную функцию запроса
            calendar.config.ajax.request = function(url, options) {
                const headers = new Headers({
                    'Authorization': `Bearer ${calendar.config.authToken}`,
                    'Content-Type': 'application/json'
                });

                return fetch(url, {
                    ...options,
                    headers: headers
                })
                .then(response => response.json())
                .catch(error => {
                    console.error('Request failed:', error);
                    throw error;
                });
            };

            // Привязываем элементы управления
            document.getElementById('finance-mode').addEventListener('change', function(e) {
                calendar.isFinanceMode = e.target.checked;
                calendar.renderTable();
            });

            document.getElementById('refresh-btn').addEventListener('click', function() {
                calendar.refresh();
            });

            document.getElementById('today-btn').addEventListener('click', function() {
                const today = new Date();
                calendar.currentYear = today.getFullYear();
                calendar.currentMonth = today.getMonth();
                calendar.refresh();
            });

            // Сохраняем ссылку для отладки
            window.calendar = calendar;
        });
    </script>
</body>
</html>
```

## Отладка и решение проблем
Частые ошибки
"Cannot read properties of undefined (reading 'authToken')"

Причина: Неправильный контекст в функции запроса

Решение: Использовать стрелочную функцию или сохранить контекст

"401 Unauthorized"

Причина: Неверный или отсутствующий токен

Решение: Проверить токен авторизации

Ошибки рендеринга

Причина: Неверный формат данных

Решение: Проверить структуру данных, убедиться что это массив

Отладка в консоли
```javascript
// Проверка конфигурации
console.log(calendar.config);

// Проверка данных
console.log(calendar.data);

// Проверка текущего состояния
console.log(calendar.getCurrentDate());

// Принудительный рендеринг
calendar.renderTable();
```

# Совместимость
Браузеры: Chrome 60+, Firefox 55+, Safari 11+, Edge 79+

Требования: ES6+ поддержка, Fetch API

Размер: ~20KB минифицированный

# Лицензия
MIT License - свободное использование и модификация.