markdown
# Calendar Component

Гибкий и настраиваемый компонент календаря для выбора дат с поддержкой навигации по годам и месяцам.

## Установка

### Подключение файлов

```html
<!-- Подключение стилей -->
<link rel="stylesheet" href="calendar.css">

<!-- Подключение скриптов -->
<script src="date-utils.js"></script> <!-- Предполагаемый файл с DateUtils -->
<script src="calendar.js"></script>
```
### Использование NPM
```bash
npm install calendar-component
```

## Быстрый старт
### Базовое использование
```javascript
const calendar = new Calendar('#calendar-container', {
    onDateSelect: (date) => {
        console.log('Выбрана дата:', date);
    }
});
```

### Расширенное использование
```javascript
const calendar = new Calendar('#calendar-container', {
    initialDate: new Date('2024-03-15'),
    minDate: new Date('2024-01-01'),
    maxDate: new Date('2024-12-31'),
    onDateSelect: (date) => {
        console.log('Выбрана дата:', date.toLocaleDateString());
    },
    showOtherMonthsDays: true,
    showNavigation: true,
    allowPastDates: false,
    locale: 'ru-RU',
    startWeekOnMonday: true,
    enableYearNavigation: true,
    monthsNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 
                  'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    weekdaysNames: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
});
```

## Опции конфигурации
|Опция	|Тип	|По умолчанию	| Описание                    |
|---|---|---|-----------------------------|
|initialDate	|Date/string	|new Date()	| Начальная отображаемая дата |
|minDate	|Date/string	|null	| Минимальная доступная дата  |
|maxDate	|Date/string	|null	| Максимальная доступная дата |
|onDateSelect	|Function	|null	| Callback при выборе даты    |
|onYearSelect	|Function	|null	| Callback при выборе года    |
|monthsNames	|Array	|Локализация	| Названия месяцев            |
|weekdaysNames	|Array	|Локализация	|Названия дней недели|
|showOtherMonthsDays	|Boolean	|true	|Показывать дни других месяцев|
|showNavigation	|Boolean	|true	|Показывать навигацию|
|locale	|String	|'ru-RU'	|Локаль для отображения|
|startWeekOnMonday	|Boolean	|true	|Начинать неделю с понедельника|
|enableYearNavigation	|Boolean	|false	|Включить навигацию по годам|
|allowPastDates	|Boolean	|false	|Разрешить выбор прошедших дат|
|allowFutureDates	|Boolean	|true	|Разрешить выбор будущих дат|
|yearNavigationRange	|Number	|100	|Диапазон отображаемых лет|

## Публичное API
### Основные методы
```javascript
// Установка даты
calendar.setDate(new Date('2024-05-20'));
calendar.setDate('2024-12-25');

// Получение текущей даты
const selectedDate = calendar.getDate();

// Установка ограничений
calendar.setMinDate(new Date('2024-01-01'));
calendar.setMaxDate(new Date('2024-12-31'));

// Управление видимостью
calendar.show();
calendar.hide();
calendar.toggle();

// Обновление опций
calendar.updateOptions({
    showOtherMonthsDays: false,
    locale: 'en-US'
});

// Управление годами
calendar.setAllowPastDates(true);
calendar.setAllowFutureDates(false);

// Уничтожение календаря
calendar.destroy();
```
### Навигация по годам

При включенной опции enableYearNavigation:

Клик на год в заголовке открывает селектор десятилетий

В селекторе доступен двухуровневый выбор: сначала декада, затем год

Поддерживается быстрый переход к сегодняшней дате

### События

`onDateSelect`
Вызывается при выборе даты пользователем.

```javascript
calendar.options.onDateSelect = function(date) {
    console.log('Выбрана дата:', date);
};
```

`onYearSelect`
Вызывается при выборе года через селектор.

```javascript
calendar.options.onYearSelect = function(newYear, oldYear) {
    console.log(`Год изменен с ${oldYear} на ${newYear}`);
};
```

### Стилизация
CSS-классы
```
.calendar-container - основной контейнер

.calendar-header - заголовок с навигацией

.calendar-weekdays - дни недели

.calendar-days - сетка дней

.calendar-day - день календаря

.calendar-day-selected - выбранный день

.calendar-day-today - сегодняшний день

.calendar-day-disabled - недоступный день

.calendar-day-other - день другого месяца
```

Кастомизация
```css
/* Пример кастомизации */
.calendar-container {
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.calendar-day-selected .calendar-day-inner {
    background-color: #4CAF50; /* Зеленый вместо синего */
}
```

## Локализация
```javascript
// Русская локализация (по умолчанию)
const calendarRU = new Calendar('#calendar-ru', {
    monthsNames: ['Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 
                  'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь'],
    weekdaysNames: ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс']
});

// Английская локализация
const calendarEN = new Calendar('#calendar-en', {
    locale: 'en-US',
    monthsNames: ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'],
    weekdaysNames: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
});
```

## Ограничения
Требует наличия класса DateUtils с вспомогательными методами для работы с датами

Поддерживает только григорианский календарь

Минимальная поддерживаемая версия браузеров: Chrome 60+, Firefox 55+, Safari 11+

## Примеры использования
Пример 1: Календарь для выбора даты рождения
```javascript
const birthCalendar = new Calendar('#birthday-calendar', {
    maxDate: new Date(),
    allowFutureDates: false,
    onDateSelect: (date) => {
        document.getElementById('birthdate').value = date.toISOString().split('T')[0];
    }
});
```

Пример 2: Календарь для бронирования
```javascript
const bookingCalendar = new Calendar('#booking-calendar', {
    minDate: new Date(),
    allowPastDates: false,
    onDateSelect: (date) => {
        checkAvailability(date);
    }
});
```

Лицензия
MIT License