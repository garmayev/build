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
            'chat_id' => $this->string(),
            'device_id' => $this->string(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%telegram_message}}');
    }
}
