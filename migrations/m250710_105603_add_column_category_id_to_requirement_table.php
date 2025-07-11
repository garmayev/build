<?php

use yii\db\Migration;

class m250710_105603_add_column_category_id_to_requirement_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-requirement-filter_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-filter_id', '{{%requirement}}');
        $this->dropColumn('{{%requirement}}', 'filter_id');

        $this->addColumn('{{%requirement}}', 'order_id', $this->integer());
        $this->createIndex('idx-requirement-order_id', '{{%requirement}}', 'order_id');
        $this->addForeignKey('fk-requirement-order_id', '{{%requirement}}', 'order_id', '{{%order}}', 'id');

        $this->addColumn('{{%requirement}}', 'count', $this->integer());

        $this->addColumn('{{%requirement}}', 'category_id', $this->integer());
        $this->createIndex('idx-requirement-category_id', '{{%requirement}}', 'category_id');
        $this->addForeignKey('fk-requirement-category_id', '{{%requirement}}', 'category_id', '{{%category}}', 'id');

        // drops foreign key for table `{{%order}}`
        $this->dropForeignKey('{{%fk-order_filter-order_id}}','{{%order_filter}}');
        // drops index for column `order_id`
        $this->dropIndex('{{%idx-order_filter-order_id}}','{{%order_filter}}');
        // drops foreign key for table `{{%filter}}`
        $this->dropForeignKey('{{%fk-order_filter-filter_id}}','{{%order_filter}}');
        // drops index for column `filter_id`
        $this->dropIndex('{{%idx-order_filter-filter_id}}', '{{%order_filter}}');
        $this->dropTable('{{%order_filter}}');

        $this->dropForeignKey('fk-filter-category_id', '{{%filter}}');
        $this->dropIndex('idx-filter-category_id', '{{%filter}}');
        $this->dropTable('{{%filter}}');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Восстанавливаем таблицу filter
        $this->createTable('{{%filter}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            // Другие колонки, которые были в исходной таблице
        ]);

        // Восстанавливаем индексы и внешние ключи для filter
        $this->createIndex('idx-filter-category_id', '{{%filter}}', 'category_id');
        $this->addForeignKey(
            'fk-filter-category_id',
            '{{%filter}}',
            'category_id',
            '{{%category}}',
            'id',
            'CASCADE'
        );
        // Восстанавливаем таблицу order_filter
        $this->createTable('{{%order_filter}}', [
            'id' => $this->primaryKey(),
            'order_id' => $this->integer()->notNull(),
            'filter_id' => $this->integer()->notNull(),
            // Другие колонки, которые были в исходной таблице
        ]);

        // Восстанавливаем индексы и внешние ключи для order_filter
        $this->createIndex('idx-order_filter-order_id', '{{%order_filter}}', 'order_id');
        $this->addForeignKey(
            'fk-order_filter-order_id',
            '{{%order_filter}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );

        $this->createIndex('idx-order_filter-filter_id', '{{%order_filter}}', 'filter_id');
        $this->addForeignKey(
            'fk-order_filter-filter_id',
            '{{%order_filter}}',
            'filter_id',
            '{{%filter}}',
            'id',
            'CASCADE'
        );

        // Восстанавливаем структуру таблицы requirement
        $this->dropForeignKey('fk-requirement-category_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-category_id', '{{%requirement}}');
        $this->dropColumn('{{%requirement}}', 'category_id');

        $this->dropColumn('{{%requirement}}', 'count');

        $this->dropForeignKey('fk-requirement-order_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-order_id', '{{%requirement}}');
        $this->dropColumn('{{%requirement}}', 'order_id');

        $this->addColumn('{{%requirement}}', 'filter_id', $this->integer()->notNull());
        $this->createIndex('idx-requirement-filter_id', '{{%requirement}}', 'filter_id');
        $this->addForeignKey(
            'fk-requirement-filter_id',
            '{{%requirement}}',
            'filter_id',
            '{{%filter}}',
            'id',
            'CASCADE'
        );
    }
}
