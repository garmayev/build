<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification}}`.
 */
class m250515_034438_create_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification}}', [
            'order_id' => $this->integer()->notNull(),
            'coworker_id' => $this->integer()->notNull(),
            'type' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'PRIMARY KEY(order_id, coworker_id, type)',
        ]);

        $this->createIndex('idx-notification-order_id', '{{%notification}}', 'order_id');
        $this->createIndex('idx-notification-coworker_id', '{{%notification}}', 'coworker_id');
        $this->addForeignKey('fk-notification-order_id', '{{%notification}}', 'order_id', '{{%order}}', 'id');
        $this->addForeignKey('fk-notification-coworker_id', '{{%notification}}', 'coworker_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-notification-coworker_id', '{{%notification}}');
        $this->dropForeignKey('fk-notification-order_id', '{{%notification}}');
        $this->dropIndex('idx-notification-coworker_id', '{{%notification}}');
        $this->dropIndex('idx-notification-order_id', '{{%notification}}');
        $this->dropTable('{{%notification}}');
    }
}
