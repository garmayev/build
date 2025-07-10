<?php

namespace app\modules\notifications\migrations;

use yii\db\Migration;

class m250709_044258_create_notifications_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notifications}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(255)->notNull(),
            'message' => $this->text()->notNull(),
            'recipient' => $this->string(255)->notNull(),
            'channels' => $this->string(255)->notNull(),
            'actions' => $this->string(255)->notNull(),
            'created_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('{{%notification_log}}', [
            'id' => $this->primaryKey(),
            'notification_id' => $this->integer()->notNull(),
            'channel' => $this->string(50)->notNull(),
            'recipient' => $this->string(255)->notNull(),
            'status' => $this->smallInteger()->notNull(),
            'response' => $this->text()->notNull(),
            'action_data' => $this->text(),
            'action_status' => $this->smallInteger(),
            'retry_count' => $this->smallInteger()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer(),
        ]);

        $this->addForeignKey(
            'fk_notification_log_notification',
            '{{%notification_log}}',
            'notification_id',
            '{{%notifications}}',
            'id',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_notification_log_notification', '{{%notification_log}}');
        $this->dropTable('{{%notification_log}}');
        $this->dropTable('{{%notifications}}');
    }
}
