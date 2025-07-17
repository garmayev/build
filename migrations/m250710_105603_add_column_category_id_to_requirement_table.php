<?php

use yii\db\Migration;

class m250710_105603_add_column_category_id_to_requirement_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%requirement}}', 'order_id', $this->integer());
        $this->createIndex('idx-requirement-order_id', '{{%requirement}}', 'order_id');
        $this->addForeignKey('fk-requirement-order_id', '{{%requirement}}', 'order_id', '{{%order}}', 'id');

        $this->addColumn('{{%requirement}}', 'count', $this->integer());

        $this->addColumn('{{%requirement}}', 'category_id', $this->integer());
        $this->createIndex('idx-requirement-category_id', '{{%requirement}}', 'category_id');
        $this->addForeignKey('fk-requirement-category_id', '{{%requirement}}', 'category_id', '{{%category}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Восстанавливаем структуру таблицы requirement
        $this->dropForeignKey('fk-requirement-category_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-category_id', '{{%requirement}}');
        $this->dropColumn('{{%requirement}}', 'category_id');

        $this->dropColumn('{{%requirement}}', 'count');

        $this->dropForeignKey('fk-requirement-order_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-order_id', '{{%requirement}}');
        $this->dropColumn('{{%requirement}}', 'order_id');
    }
}
