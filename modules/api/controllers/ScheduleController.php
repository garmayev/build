<?php

namespace app\modules\api\controllers;

use yii\rest\Controller;

class ScheduleController extends Controller
{
    public function actionMonth()
    {
        $str = '{
  "success": true,
  "data": {
    "timeSlots": [
      "08:00", "08:30", "09:00", "09:30", "10:00", "10:30",
      "11:00", "11:30", "12:00", "12:30", "13:00", "13:30",
      "14:00", "14:30", "15:00", "15:30", "16:00", "16:30",
      "17:00", "17:30", "18:00", "18:30", "19:00", "19:30",
      "20:00", "20:30", "21:00", "21:30"
    ],
    "resources": [
      {
        "id": 1,
        "name": "Конференц-зал 1",
        "type": "room",
        "capacity": 20,
        "color": "#3B82F6"
      },
      {
        "id": 2,
        "name": "Конференц-зал 2",
        "type": "room",
        "capacity": 10,
        "color": "#10B981"
      },
      {
        "id": 3,
        "name": "Иван Иванов",
        "type": "employee",
        "position": "Менеджер",
        "avatar": "/avatars/ivan.jpg",
        "color": "#F59E0B"
      },
      {
        "id": 4,
        "name": "Мария Петрова",
        "type": "employee",
        "position": "Дизайнер",
        "avatar": "/avatars/maria.jpg",
        "color": "#8B5CF6"
      }
    ],
    "events": [
      {
        "id": 101,
        "title": "Презентация проекта",
        "description": "Презентация нового проекта клиенту",
        "date": "2024-01-20",
        "start_time": "10:00",
        "end_time": "11:30",
        "duration": 90,
        "category": "meeting",
        "status": "confirmed",
        "resource_id": 1,
        "resource": "Конференц-зал 1",
        "participants": [3, 4],
        "color": "#3B82F6",
        "priority": "high",
        "created_at": "2024-01-15 09:00:00",
        "updated_at": "2024-01-15 09:00:00"
      },
      {
        "id": 102,
        "title": "Планирование спринта",
        "description": "Еженедельное планирование задач",
        "date": "2024-01-20",
        "start_time": "14:00",
        "end_time": "15:30",
        "duration": 90,
        "category": "planning",
        "status": "scheduled",
        "resource_id": 2,
        "resource": "Конференц-зал 2",
        "participants": [3],
        "color": "#10B981",
        "priority": "medium",
        "created_at": "2024-01-16 11:30:00",
        "updated_at": "2024-01-16 11:30:00"
      },
      {
        "id": 103,
        "title": "Обед",
        "date": "2024-01-20",
        "start_time": "13:00",
        "end_time": "14:00",
        "duration": 60,
        "category": "break",
        "status": "confirmed",
        "resource_id": 3,
        "resource": "Иван Иванов",
        "color": "#F59E0B",
        "is_blocking": true
      },
      {
        "id": 104,
        "title": "Встреча с поставщиком",
        "description": "Обсуждение условий поставки",
        "date": "2024-01-21",
        "start_time": "11:00",
        "end_time": "12:00",
        "duration": 60,
        "category": "meeting",
        "status": "tentative",
        "resource_id": 1,
        "resource": "Конференц-зал 1",
        "participants": [3],
        "color": "#3B82F6",
        "priority": "medium"
      }
    ],
    "holidays": [
      {
        "date": "2024-01-01",
        "name": "Новый год",
        "type": "holiday",
        "is_day_off": true
      },
      {
        "date": "2024-01-07",
        "name": "Рождество",
        "type": "holiday",
        "is_day_off": true
      }
    ],
    "unavailable": [
      {
        "resource_id": 3,
        "date": "2024-01-22",
        "reason": "Отпуск",
        "type": "vacation"
      },
      {
        "resource_id": 1,
        "date": "2024-01-23",
        "time": "09:00-12:00",
        "reason": "Техническое обслуживание",
        "type": "maintenance"
      }
    ],
    "metadata": {
      "current_date": "2024-01-20",
      "days_count": 30,
      "time_start": 8,
      "time_end": 22,
      "timezone": "Europe/Moscow",
      "last_updated": "2024-01-20 10:30:00"
    }
  }
}';

        return json_decode($str, true);
    }
}