/**
 * Утилиты для работы с датами
 */
class DateUtils {
    /**
     * Возвращает массив полных названий месяцев
     * @param {string} locale - Локаль
     * @returns {string[]}
     */
    static getMonthsNames(locale = 'ru-RU') {
        const formatter = new Intl.DateTimeFormat(locale, { month: 'long' });
        return Array.from({ length: 12 }, (_, i) => {
            const date = new Date(2024, i, 1);
            return formatter.format(date);
        });
    }

    /**
     * Возвращает массив коротких названий месяцев (3 символа)
     * @param {string} locale - Локаль
     * @returns {string[]}
     */
    static getShortMonthsNames(locale = 'ru-RU') {
        const formatter = new Intl.DateTimeFormat(locale, { month: 'short' });
        return Array.from({ length: 12 }, (_, i) => {
            const date = new Date(2024, i, 1);
            return formatter.format(date);
        });
    }

    /**
     * Возвращает массив названий дней недели
     * @param {string} locale - Локаль
     * @param {boolean} startWeekOnMonday - Начинать ли неделю с понедельника
     * @returns {string[]}
     */
    static getWeekdaysNames(locale = 'ru-RU', startWeekOnMonday = true) {
        const formatter = new Intl.DateTimeFormat(locale, { weekday: 'short' });
        const weekdays = Array.from({ length: 7 }, (_, i) => {
            const date = new Date(2024, 0, i + 5); // 5 января 2024 - воскресенье
            return formatter.format(date);
        });

        if (startWeekOnMonday) {
            // Перемещаем воскресенье в конец
            return [...weekdays.slice(1), weekdays[0]];
        }
        return weekdays;
    }

    /**
     * Возвращает короткие названия годов (для отображения в кнопках)
     * @param {number} year - Год
     * @returns {string}
     */
    static getShortYearName(year) {
        return year.toString();
    }

    /**
     * Клонирует дату
     * @param {Date|string} date - Дата для клонирования
     * @returns {Date}
     */
    static cloneDate(date) {
        if (!date) return null;
        if (typeof date === 'string') {
            return new Date(date);
        }
        return new Date(date.getTime());
    }

    /**
     * Возвращает сегодняшнюю дату без времени
     * @returns {Date}
     */
    static getToday() {
        const today = new Date();
        // Правильное обнуление времени - создаём новую дату с тем же днём
        return new Date(today.getFullYear(), today.getMonth(), today.getDate());
    }

    /**
     * Устанавливает начало дня для даты
     * @param {Date} date - Дата
     * @returns {Date}
     */
    static setStartOfDay(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate());
    }

    /**
     * Проверяет, являются ли две даты одним и тем же днем
     * @param {Date} date1 - Первая дата
     * @param {Date} date2 - Вторая дата
     * @returns {boolean}
     */
    static isSameDay(date1, date2) {
        if (!date1 || !date2) return false;
        return date1.getDate() === date2.getDate() &&
            date1.getMonth() === date2.getMonth() &&
            date1.getFullYear() === date2.getFullYear();
    }

    /**
     * Возвращает первый день месяца
     * @param {Date} date - Дата
     * @returns {Date}
     */
    static getFirstDayOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth(), 1);
    }

    /**
     * Возвращает последний день месяца
     * @param {Date} date - Дата
     * @returns {Date}
     */
    static getLastDayOfMonth(date) {
        return new Date(date.getFullYear(), date.getMonth() + 1, 0);
    }

    /**
     * Возвращает номер дня недели (1-7, где 1 - понедельник)
     * @param {Date} date - Дата
     * @returns {number}
     */
    static getDayOfWeek(date) {
        const day = date.getDay();
        return day === 0 ? 7 : day;
    }

    /**
     * Возвращает количество дней в месяце
     * @param {number} year - Год
     * @param {number} month - Месяц (0-11)
     * @returns {number}
     */
    static getDaysInMonth(year, month) {
        return new Date(year, month + 1, 0).getDate();
    }

    /**
     * Форматирует дату в строку
     * @param {Date} date - Дата
     * @param {string} format - Формат (например, 'dd.MM.yyyy')
     * @param {string} locale - Локаль
     * @returns {string}
     */
    static formatDate(date, format = 'dd.MM.yyyy', locale = 'ru-RU') {
        if (!date) return '';

        const day = date.getDate();
        const month = date.getMonth() + 1;
        const year = date.getFullYear();

        // Поддержка различных вариантов форматов
        const replacements = {
            // Дни
            'dd': day.toString().padStart(2, '0'),
            'd': day.toString(),
            'DD': day.toString().padStart(2, '0'), // альтернатива
            'D': day.toString(),

            // Месяцы
            'MM': month.toString().padStart(2, '0'),
            'M': month.toString(),

            // Годы
            'yyyy': year.toString(),
            'yy': year.toString().slice(-2),
            'YYYY': year.toString(), // альтернатива
            'YY': year.toString().slice(-2),
        };

        let result = format;
        for (const [pattern, value] of Object.entries(replacements)) {
            result = result.replace(new RegExp(pattern, 'g'), value);
        }

        return result;
    }

    /**
     * Проверяет, является ли год високосным
     * @param {number} year - Год
     * @returns {boolean}
     */
    static isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || (year % 400 === 0);
    }

    /**
     * Добавляет дни к дате
     * @param {Date} date - Дата
     * @param {number} days - Количество дней для добавления
     * @returns {Date}
     */
    static addDays(date, days) {
        const result = new Date(date);
        result.setDate(result.getDate() + days);
        return result;
    }

    /**
     * Добавляет месяцы к дате
     * @param {Date} date - Дата
     * @param {number} months - Количество месяцев для добавления
     * @returns {Date}
     */
    static addMonths(date, months) {
        const result = new Date(date);
        result.setMonth(result.getMonth() + months);
        return result;
    }

    /**
     * Добавляет годы к дате
     * @param {Date} date - Дата
     * @param {number} years - Количество лет для добавления
     * @returns {Date}
     */
    static addYears(date, years) {
        const result = new Date(date);
        result.setFullYear(result.getFullYear() + years);
        return result;
    }

    /**
     * Возвращает разницу между датами в днях
     * @param {Date} date1 - Первая дата
     * @param {Date} date2 - Вторая дата
     * @returns {number}
     */
    static getDaysDifference(date1, date2) {
        // Устанавливаем оба времени на начало дня для точного сравнения дней
        const d1 = DateUtils.setStartOfDay(date1);
        const d2 = DateUtils.setStartOfDay(date2);
        const timeDiff = Math.abs(d2.getTime() - d1.getTime());
        return Math.ceil(timeDiff / (1000 * 3600 * 24));
    }

    /**
     * Проверяет, находится ли дата в диапазоне
     * @param {Date} date - Проверяемая дата
     * @param {Date} start - Начало диапазона
     * @param {Date} end - Конец диапазона
     * @param {boolean} inclusive - Включать границы
     * @returns {boolean}
     */
    static isDateInRange(date, start, end, inclusive = true) {
        if (!date || !start || !end) return false;

        const time = date.getTime();
        const startTime = start.getTime();
        const endTime = end.getTime();

        if (inclusive) {
            return time >= startTime && time <= endTime;
        }
        return time > startTime && time < endTime;
    }
}

// Экспорт для использования в других файлах
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DateUtils;
}