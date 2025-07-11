<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%telegram_message}}`.
 */
class m241128_083447_create_telegram_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%telegram_message}}', [
            'id' => $this->primaryKey(),
            'chat_id' => $this->string()->notNull(),
            'device_id' => $this->string(),
            'order_id' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'message_id' => $this->integer()->notNull(),
            'text' => $this->text(),
            'reply_markup' => $this->text(),
            'status' => $this->integer(),
        ]);

        $this->createIndex(
            'idx-telegram_message-order_id',
            '{{%telegram_message}}',
            'order_id'
        );

        $this->addForeignKey(
            'fk-telegram_message-order_id',
            '{{%telegram_message}}',
            'order_id',
            '{{%order}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-telegram_message-order_id', '{{%telegram_message}}');
        $this->dropIndex('idx-telegram_message-order_id', '{{%telegram_message}}');
        $this->dropTable('{{%telegram_message}}');
    }
}
