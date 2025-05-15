-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 15 2025 г., 02:16
-- Версия сервера: 9.2.0
-- Версия PHP: 8.3.16

SET
SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET
time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `build_test`
--

-- --------------------------------------------------------

--
-- Структура таблицы `attachment`
--

CREATE TABLE `attachment`
(
    `id`           int NOT NULL,
    `url`          varchar(255) DEFAULT NULL,
    `target_class` varchar(255) DEFAULT NULL,
    `target_id`    int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `building_coworker`
--

CREATE TABLE `building_coworker`
(
    `building_id` int NOT NULL,
    `coworker_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `category`
--

CREATE TABLE `category`
(
    `id`        int          NOT NULL,
    `title`     varchar(255) NOT NULL,
    `type`      int          NOT NULL,
    `parent_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;
INSERT INTO `category` (`id`, `title`, `type`, `parent_id`) VALUES (1, 'Штукатурщик', 1, NULL);
INSERT INTO `category` (`id`, `title`, `type`, `parent_id`) VALUES (2, 'Разнорабочий', 1, NULL);

-- --------------------------------------------------------
--
-- Структура таблицы `category_coworker`
--

CREATE TABLE `category_coworker`
(
    `category_id` int NOT NULL,
    `coworker_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `category_material`
--

CREATE TABLE `category_material`
(
    `category_id` int NOT NULL,
    `material_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `category_property`
--

CREATE TABLE `category_property`
(
    `category_id` int NOT NULL,
    `property_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `category_technique`
--

CREATE TABLE `category_technique`
(
    `category_id`  int NOT NULL,
    `technique_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `coworker`
--

CREATE TABLE `coworker`
(
    `id`          int          NOT NULL,
    `firstname`   varchar(255) NOT NULL,
    `lastname`    varchar(255) NOT NULL,
    `email`       varchar(255) NOT NULL,
    `phone`       varchar(255) NOT NULL,
    `priority`    int          NOT NULL,
    `user_id`     int          NOT NULL,
    `category_id` int          DEFAULT NULL,
    `type`        int          DEFAULT NULL,
    `chat_id`     varchar(255) DEFAULT NULL,
    `device_id`   varchar(255) DEFAULT NULL,
    `created_by`  int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `coworker_property`
--

CREATE TABLE `coworker_property`
(
    `coworker_id`  int NOT NULL,
    `property_id`  int NOT NULL,
    `dimension_id` int NOT NULL,
    `value`        int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `dimension`
--

CREATE TABLE `dimension`
(
    `id`    int          NOT NULL,
    `title` varchar(255) NOT NULL,
    `multiplier` double NOT NULL,
    `short` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Дамп данных таблицы `dimension`
--

INSERT INTO `dimension` (`id`, `title`, `multiplier`, `short`)
VALUES (1, 'Лет', 1, 'л');

-- --------------------------------------------------------

--
-- Структура таблицы `filter`
--

CREATE TABLE `filter`
(
    `id`          int NOT NULL,
    `category_id` int DEFAULT NULL,
    `count`       int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `hours`
--

CREATE TABLE `hours`
(
    `coworker_id` int  NOT NULL,
    `date`        date NOT NULL,
    `count`       int DEFAULT NULL,
    `is_payed`    tinyint(1) DEFAULT '0',
    `order_id`    int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `location`
--

CREATE TABLE `location`
(
    `id`      int NOT NULL,
    `address` varchar(255) DEFAULT NULL,
    `latitude` double DEFAULT NULL,
    `longitude` double DEFAULT NULL,
    `user_id` int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Дамп данных таблицы `location`
--

INSERT INTO `location` (`id`, `address`, `latitude`, `longitude`, `user_id`)
VALUES (2, 'Воровского улица, 22', 107.605717, 51.819447, 4);

-- --------------------------------------------------------

--
-- Структура таблицы `material`
--

CREATE TABLE `material`
(
    `id`          int          NOT NULL,
    `title`       varchar(255) NOT NULL,
    `price` double DEFAULT NULL,
    `image`       varchar(255) DEFAULT NULL,
    `category_id` int          NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `material_property`
--

CREATE TABLE `material_property`
(
    `material_id` int NOT NULL,
    `property_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `migration`
--

CREATE TABLE `migration`
(
    `version`    varchar(180) NOT NULL,
    `apply_time` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Дамп данных таблицы `migration`
--

INSERT INTO `migration` (`version`, `apply_time`)
VALUES ('m000000_000000_base', 1747270477),
       ('m241114_091601_create_user_table', 1747270480),
       ('m241114_092018_create_category_table', 1747270480),
       ('m241114_092105_create_material_table', 1747270480),
       ('m241114_092607_create_coworker_table', 1747270480),
       ('m241114_093106_create_technique_table', 1747270480),
       ('m241114_093538_create_dimension_table', 1747270480),
       ('m241114_093708_create_property_table', 1747270481),
       ('m241114_093856_create_junction_table_for_property_and_dimension_tables', 1747270482),
       ('m241114_093921_create_junction_table_for_material_and_property_tables', 1747270482),
       ('m241114_093937_create_junction_table_for_coworker_and_property_tables', 1747270482),
       ('m241114_093947_create_junction_table_for_technique_and_property_tables', 1747270482),
       ('m241115_012031_create_location_table', 1747270482),
       ('m241115_012129_create_building_table', 1747270483),
       ('m241115_012420_create_order_table', 1747270483),
       ('m241115_012747_create_filter_table', 1747270483),
       ('m241115_013338_create_junction_table_for_order_and_filter_tables', 1747270484),
       ('m241115_013353_create_junction_table_for_order_and_coworker_tables', 1747270485),
       ('m241115_013403_create_junction_table_for_order_and_material_tables', 1747270485),
       ('m241115_013416_create_junction_table_for_order_and_technique_tables', 1747270485),
       ('m241115_072802_create_requirement_table', 1747270486),
       ('m241115_100613_create_junction_table_for_category_and_property_tables', 1747270486),
       ('m241115_103958_create_junction_table_for_category_and_coworker_tables', 1747270486),
       ('m241115_104008_create_junction_table_for_category_and_material_tables', 1747270488),
       ('m241115_104021_create_junction_table_for_category_and_technique_tables', 1747270488),
       ('m241128_083447_create_telegram_message_table', 1747270488),
       ('m250116_152819_create_attachment_table', 1747270489),
       ('m250124_012655_add_column_notify_stage_to_order_table', 1747270489),
       ('m250124_024540_add_column_type_to_coworker_table', 1747270489),
       ('m250128_070603_alter_column_category_id_to_coworker_table', 1747270490),
       ('m250128_073954_create_junction_table_for_building_and_coworker_tables', 1747270490),
       ('m250129_075816_add_column_message_id_to_telegram_message_table', 1747270490),
       ('m250205_065900_add_column_created_by_to_order_table', 1747270491),
       ('m250212_032455_create_hours_table', 1747270491),
       ('m250218_011602_add_column_chat_id_to_coworker_table', 1747270491),
       ('m250218_011934_add_column_priority_level_to_order_table', 1747270491),
       ('m250321_010519_add_column_create_by_to_coworker_table', 1747270491),
       ('m250326_080036_add_column_radius_to_building_table', 1747270491),
       ('m250328_085043_add_column_is_payed_to_hours_table', 1747270492),
       ('m250418_103105_add_column_order_id_to_hours_table', 1747270492);

-- --------------------------------------------------------

--
-- Структура таблицы `order`
--

CREATE TABLE `order`
(
    `id`             int NOT NULL,
    `status`         int DEFAULT NULL,
    `building_id`    int DEFAULT NULL,
    `date`           int DEFAULT NULL,
    `type`           int DEFAULT NULL,
    `created_at`     int DEFAULT NULL,
    `notify_date`    int DEFAULT NULL,
    `comment`        longtext,
    `notify_stage`   int DEFAULT NULL,
    `created_by`     int DEFAULT NULL,
    `priority_level` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `order_coworker`
--

CREATE TABLE `order_coworker`
(
    `order_id`    int NOT NULL,
    `coworker_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `order_filter`
--

CREATE TABLE `order_filter`
(
    `order_id`  int NOT NULL,
    `filter_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `order_material`
--

CREATE TABLE `order_material`
(
    `order_id`    int NOT NULL,
    `material_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `order_technique`
--

CREATE TABLE `order_technique`
(
    `order_id`     int NOT NULL,
    `technique_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `property`
--

CREATE TABLE `property`
(
    `id`    int          NOT NULL,
    `title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Дамп данных таблицы `property`
--

INSERT INTO `property` (`id`, `title`)
VALUES (1, 'Возраст'),
       (2, 'Опыт');

-- --------------------------------------------------------

--
-- Структура таблицы `property_dimension`
--

CREATE TABLE `property_dimension`
(
    `property_id`  int NOT NULL,
    `dimension_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Дамп данных таблицы `property_dimension`
--

INSERT INTO `property_dimension` (`property_id`, `dimension_id`)
VALUES (1, 1),
       (2, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `requirement`
--

CREATE TABLE `requirement`
(
    `id`           int NOT NULL,
    `property_id`  int          DEFAULT NULL,
    `dimension_id` int          DEFAULT NULL,
    `value` double DEFAULT NULL,
    `type`         varchar(255) DEFAULT NULL,
    `filter_id`    int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `technique`
--

CREATE TABLE `technique`
(
    `id`          int          NOT NULL,
    `title`       varchar(255) NOT NULL,
    `coworker_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `technique_property`
--

CREATE TABLE `technique_property`
(
    `technique_id` int NOT NULL,
    `property_id`  int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `telegram_message`
--

CREATE TABLE `telegram_message`
(
    `id`           int NOT NULL,
    `chat_id`      varchar(255) DEFAULT NULL,
    `device_id`    varchar(255) DEFAULT NULL,
    `order_id`     int          DEFAULT NULL,
    `created_at`   int          DEFAULT NULL,
    `updated_at`   int          DEFAULT NULL,
    `text`         text,
    `reply_markup` text,
    `status`       int          DEFAULT NULL,
    `message_id`   int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE `user`
(
    `id`            int          NOT NULL,
    `username`      varchar(255) DEFAULT NULL,
    `email`         varchar(255) DEFAULT NULL,
    `password_hash` varchar(255) NOT NULL,
    `auth_key`      varchar(255) NOT NULL,
    `access_token`  varchar(255) NOT NULL,
    `status`        int          DEFAULT NULL,
    `chat_id`       varchar(255) DEFAULT NULL,
    `device_id`     varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;

--
-- Дамп данных таблицы `user`
--

INSERT INTO `user` (`id`, `username`, `email`, `password_hash`, `auth_key`, `access_token`, `status`, `chat_id`,
                    `device_id`)
VALUES (4, 'garmayev', 'garmayev@yandex.ru', '$2y$13$rpQIIymy8WT3hnsotsZTRO.TwKoDnGsUjylCJ5eDPF/X2JZ10yaEi',
        'jgzaEBoy1c8K0gZqkJIEJ6pMmQukEx5w', 'bpIzow295LHdA9QMgDq6nfFhM5Os52Nc', 1, NULL, NULL);


-- --------------------------------------------------------

--
-- Структура таблицы `building`
--

CREATE TABLE `building`
(
    `id`          int NOT NULL,
    `title`       varchar(255) DEFAULT NULL,
    `location_id` int          DEFAULT NULL,
    `user_id`     int          DEFAULT NULL,
    `radius`      int          DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf32;
INSERT INTO `building` (`id`, `title`, `location_id`, `user_id`, `radius`)
VALUES (1, 'Лет', 1, 1, 20);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `attachment`
--
ALTER TABLE `attachment`
    ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `building`
--
ALTER TABLE `building`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-building-location_id` (`location_id`),
  ADD KEY `idx-building-user_id` (`user_id`);

--
-- Индексы таблицы `building_coworker`
--
ALTER TABLE `building_coworker`
    ADD PRIMARY KEY (`building_id`, `coworker_id`),
  ADD KEY `idx-building_coworker-building_id` (`building_id`),
  ADD KEY `idx-building_coworker-coworker_id` (`coworker_id`);

--
-- Индексы таблицы `category`
--
ALTER TABLE `category`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-category-parent_id` (`parent_id`);

--
-- Индексы таблицы `category_coworker`
--
ALTER TABLE `category_coworker`
    ADD PRIMARY KEY (`category_id`, `coworker_id`),
  ADD KEY `idx-category_coworker-category_id` (`category_id`),
  ADD KEY `idx-category_coworker-coworker_id` (`coworker_id`);

--
-- Индексы таблицы `category_material`
--
ALTER TABLE `category_material`
    ADD PRIMARY KEY (`category_id`, `material_id`),
  ADD KEY `idx-category_material-category_id` (`category_id`),
  ADD KEY `idx-category_material-material_id` (`material_id`);

--
-- Индексы таблицы `category_property`
--
ALTER TABLE `category_property`
    ADD PRIMARY KEY (`category_id`, `property_id`),
  ADD KEY `idx-category_property-category_id` (`category_id`),
  ADD KEY `idx-category_property-property_id` (`property_id`);

--
-- Индексы таблицы `category_technique`
--
ALTER TABLE `category_technique`
    ADD PRIMARY KEY (`category_id`, `technique_id`),
  ADD KEY `idx-category_technique-category_id` (`category_id`),
  ADD KEY `idx-category_technique-technique_id` (`technique_id`);

--
-- Индексы таблицы `coworker`
--
ALTER TABLE `coworker`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-coworker-created_by` (`created_by`);

--
-- Индексы таблицы `coworker_property`
--
ALTER TABLE `coworker_property`
    ADD PRIMARY KEY (`coworker_id`, `property_id`, `dimension_id`),
  ADD KEY `idx-coworker_property-coworker_id` (`coworker_id`),
  ADD KEY `idx-coworker_property-property_id` (`property_id`);

--
-- Индексы таблицы `dimension`
--
ALTER TABLE `dimension`
    ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `filter`
--
ALTER TABLE `filter`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-filter-category_id` (`category_id`);

--
-- Индексы таблицы `hours`
--
ALTER TABLE `hours`
    ADD PRIMARY KEY (`coworker_id`, `date`);

--
-- Индексы таблицы `location`
--
ALTER TABLE `location`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-location-user_id` (`user_id`);

--
-- Индексы таблицы `material`
--
ALTER TABLE `material`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-material-category_id` (`category_id`);

--
-- Индексы таблицы `material_property`
--
ALTER TABLE `material_property`
    ADD PRIMARY KEY (`material_id`, `property_id`),
  ADD KEY `idx-material_property-material_id` (`material_id`),
  ADD KEY `idx-material_property-property_id` (`property_id`);

--
-- Индексы таблицы `migration`
--
ALTER TABLE `migration`
    ADD PRIMARY KEY (`version`);

--
-- Индексы таблицы `order`
--
ALTER TABLE `order`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-order-building_id` (`building_id`),
  ADD KEY `idx-order-created_by` (`created_by`);

--
-- Индексы таблицы `order_coworker`
--
ALTER TABLE `order_coworker`
    ADD PRIMARY KEY (`order_id`, `coworker_id`),
  ADD KEY `idx-order_coworker-order_id` (`order_id`),
  ADD KEY `idx-order_coworker-coworker_id` (`coworker_id`);

--
-- Индексы таблицы `order_filter`
--
ALTER TABLE `order_filter`
    ADD PRIMARY KEY (`order_id`, `filter_id`),
  ADD KEY `idx-order_filter-order_id` (`order_id`),
  ADD KEY `idx-order_filter-filter_id` (`filter_id`);

--
-- Индексы таблицы `order_material`
--
ALTER TABLE `order_material`
    ADD PRIMARY KEY (`order_id`, `material_id`),
  ADD KEY `idx-order_material-order_id` (`order_id`),
  ADD KEY `idx-order_material-material_id` (`material_id`);

--
-- Индексы таблицы `order_technique`
--
ALTER TABLE `order_technique`
    ADD PRIMARY KEY (`order_id`, `technique_id`),
  ADD KEY `idx-order_technique-order_id` (`order_id`),
  ADD KEY `idx-order_technique-technique_id` (`technique_id`);

--
-- Индексы таблицы `property`
--
ALTER TABLE `property`
    ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `property_dimension`
--
ALTER TABLE `property_dimension`
    ADD PRIMARY KEY (`property_id`, `dimension_id`),
  ADD KEY `idx-property_dimension-property_id` (`property_id`),
  ADD KEY `idx-property_dimension-dimension_id` (`dimension_id`);

--
-- Индексы таблицы `requirement`
--
ALTER TABLE `requirement`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-requirement-property_id` (`property_id`),
  ADD KEY `idx-requirement-dimension_id` (`dimension_id`),
  ADD KEY `idx-requirement-filter_id` (`filter_id`);

--
-- Индексы таблицы `technique`
--
ALTER TABLE `technique`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-technique-coworker_id` (`coworker_id`);

--
-- Индексы таблицы `technique_property`
--
ALTER TABLE `technique_property`
    ADD PRIMARY KEY (`technique_id`, `property_id`),
  ADD KEY `idx-technique_property-technique_id` (`technique_id`),
  ADD KEY `idx-technique_property-property_id` (`property_id`);

--
-- Индексы таблицы `telegram_message`
--
ALTER TABLE `telegram_message`
    ADD PRIMARY KEY (`id`),
  ADD KEY `idx-telegram_message-order_id` (`order_id`);

--
-- Индексы таблицы `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `attachment`
--
ALTER TABLE `attachment`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `building`
--
ALTER TABLE `building`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `category`
--
ALTER TABLE `category`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `coworker`
--
ALTER TABLE `coworker`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `dimension`
--
ALTER TABLE `dimension`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `filter`
--
ALTER TABLE `filter`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `location`
--
ALTER TABLE `location`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `material`
--
ALTER TABLE `material`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `order`
--
ALTER TABLE `order`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `property`
--
ALTER TABLE `property`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `requirement`
--
ALTER TABLE `requirement`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `technique`
--
ALTER TABLE `technique`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `telegram_message`
--
ALTER TABLE `telegram_message`
    MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `user`
--
ALTER TABLE `user`
    MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `building`
--
ALTER TABLE `building`
    ADD CONSTRAINT `fk-building-location_id` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`),
  ADD CONSTRAINT `fk-building-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Ограничения внешнего ключа таблицы `building_coworker`
--
ALTER TABLE `building_coworker`
    ADD CONSTRAINT `fk-building_coworker-building_id` FOREIGN KEY (`building_id`) REFERENCES `building` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-building_coworker-coworker_id` FOREIGN KEY (`coworker_id`) REFERENCES `coworker` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `category`
--
ALTER TABLE `category`
    ADD CONSTRAINT `fk-category-parent_id` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`);

--
-- Ограничения внешнего ключа таблицы `category_coworker`
--
ALTER TABLE `category_coworker`
    ADD CONSTRAINT `fk-category_coworker-category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-category_coworker-coworker_id` FOREIGN KEY (`coworker_id`) REFERENCES `coworker` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `category_material`
--
ALTER TABLE `category_material`
    ADD CONSTRAINT `fk-category_material-category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-category_material-material_id` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `category_property`
--
ALTER TABLE `category_property`
    ADD CONSTRAINT `fk-category_property-category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-category_property-property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `category_technique`
--
ALTER TABLE `category_technique`
    ADD CONSTRAINT `fk-category_technique-category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-category_technique-technique_id` FOREIGN KEY (`technique_id`) REFERENCES `technique` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `coworker`
--
ALTER TABLE `coworker`
    ADD CONSTRAINT `fk-coworker-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);

--
-- Ограничения внешнего ключа таблицы `coworker_property`
--
ALTER TABLE `coworker_property`
    ADD CONSTRAINT `fk-coworker_property-coworker_id` FOREIGN KEY (`coworker_id`) REFERENCES `coworker` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-coworker_property-property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `filter`
--
ALTER TABLE `filter`
    ADD CONSTRAINT `fk-filter-category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Ограничения внешнего ключа таблицы `location`
--
ALTER TABLE `location`
    ADD CONSTRAINT `fk-location-user_id` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Ограничения внешнего ключа таблицы `material`
--
ALTER TABLE `material`
    ADD CONSTRAINT `fk-material-category_id` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Ограничения внешнего ключа таблицы `material_property`
--
ALTER TABLE `material_property`
    ADD CONSTRAINT `fk-material_property-material_id` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-material_property-property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `order`
--
ALTER TABLE `order`
    ADD CONSTRAINT `fk-order-building_id` FOREIGN KEY (`building_id`) REFERENCES `building` (`id`),
  ADD CONSTRAINT `fk-order-created_by` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`);

--
-- Ограничения внешнего ключа таблицы `order_coworker`
--
ALTER TABLE `order_coworker`
    ADD CONSTRAINT `fk-order_coworker-coworker_id` FOREIGN KEY (`coworker_id`) REFERENCES `coworker` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-order_coworker-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_filter`
--
ALTER TABLE `order_filter`
    ADD CONSTRAINT `fk-order_filter-filter_id` FOREIGN KEY (`filter_id`) REFERENCES `filter` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-order_filter-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_material`
--
ALTER TABLE `order_material`
    ADD CONSTRAINT `fk-order_material-material_id` FOREIGN KEY (`material_id`) REFERENCES `material` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-order_material-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `order_technique`
--
ALTER TABLE `order_technique`
    ADD CONSTRAINT `fk-order_technique-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-order_technique-technique_id` FOREIGN KEY (`technique_id`) REFERENCES `technique` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `property_dimension`
--
ALTER TABLE `property_dimension`
    ADD CONSTRAINT `fk-property_dimension-dimension_id` FOREIGN KEY (`dimension_id`) REFERENCES `dimension` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-property_dimension-property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `requirement`
--
ALTER TABLE `requirement`
    ADD CONSTRAINT `fk-requirement-dimension_id` FOREIGN KEY (`dimension_id`) REFERENCES `dimension` (`id`),
  ADD CONSTRAINT `fk-requirement-filter_id` FOREIGN KEY (`filter_id`) REFERENCES `filter` (`id`),
  ADD CONSTRAINT `fk-requirement-property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`);

--
-- Ограничения внешнего ключа таблицы `technique`
--
ALTER TABLE `technique`
    ADD CONSTRAINT `fk-technique-coworker_id` FOREIGN KEY (`coworker_id`) REFERENCES `coworker` (`id`);

--
-- Ограничения внешнего ключа таблицы `technique_property`
--
ALTER TABLE `technique_property`
    ADD CONSTRAINT `fk-technique_property-property_id` FOREIGN KEY (`property_id`) REFERENCES `property` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk-technique_property-technique_id` FOREIGN KEY (`technique_id`) REFERENCES `technique` (`id`) ON
DELETE
CASCADE;

--
-- Ограничения внешнего ключа таблицы `telegram_message`
--
ALTER TABLE `telegram_message`
    ADD CONSTRAINT `fk-telegram_message-order_id` FOREIGN KEY (`order_id`) REFERENCES `order` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
