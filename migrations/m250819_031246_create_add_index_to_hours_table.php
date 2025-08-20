<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%add_index_to_hours}}`.
 */
class m250819_031246_create_add_index_to_hours_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('{{%idx-hours-order_id}}', '{{%hours}}', 'order_id');
        $this->addForeignKey('{{%fk-hours-order_id}}', '{{%hours}}', 'order_id', '{{%order}}', 'id', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-hours-order_id}}', '{{%hours}}');
        $this->dropIndex('{{%idx-hours-order_id}}', '{{%hours}}');
    }
}
