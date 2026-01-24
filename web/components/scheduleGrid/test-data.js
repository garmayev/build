// test-data.js - временный файл для теста
if (!window.testScheduleData) {
    window.testScheduleData = {
        getTestData: function() {
            const startDate = new Date();
            const timeSlots = [];

            // Генерация временных слотов 8:00 - 22:00, каждый 30 минут
            for (let hour = 8; hour < 22; hour++) {
                for (let minute = 0; minute < 60; minute += 30) {
                    timeSlots.push(`${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`);
                }
            }

            // Генерация дней (30 дней)
            const days = [];
            for (let i = 0; i < 30; i++) {
                const date = new Date(startDate);
                date.setDate(startDate.getDate() + i);
                days.push({
                    id: i + 1,
                    name: `Ресурс ${i + 1}`,
                    date: date.toISOString().split('T')[0],
                    dayOfWeek: date.getDay(),
                    isWeekend: date.getDay() === 0 || date.getDay() === 6,
                    isToday: i === 0,
                    isPast: i < 0
                });
            }

            // Тестовые события
            const events = [
                {
                    id: 1,
                    title: 'Тестовая встреча',
                    date: startDate.toISOString().split('T')[0],
                    start_time: '10:00',
                    duration: 60,
                    category: 'meeting',
                    resource: 'Конференц-зал 1',
                    description: 'Обсуждение проекта'
                },
                {
                    id: 2,
                    title: 'Совещание отдела',
                    date: new Date(startDate.getTime() + 86400000).toISOString().split('T')[0],
                    start_time: '14:30',
                    duration: 90,
                    category: 'meeting',
                    resource: 'Переговорная 2',
                    description: 'Еженедельное совещание'
                },
                {
                    id: 3,
                    title: 'Обед',
                    date: startDate.toISOString().split('T')[0],
                    start_time: '13:00',
                    duration: 60,
                    category: 'break',
                    resource: 'Столовая'
                }
            ];

            return {
                timeSlots: timeSlots,
                days: days,
                events: events,
                resources: days.map(day => ({
                    id: day.id,
                    name: day.name,
                    type: 'resource'
                }))
            };
        }
    };
}