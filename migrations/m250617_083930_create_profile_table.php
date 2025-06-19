<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%profile}}`.
 */
class m250617_083930_create_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%profile}}', [
            'id' => $this->primaryKey(),
            'family' => $this->string()->null(),
            'name' => $this->string()->null(),
            'surname' => $this->string()->null(),
            'birthday' => $this->date()->null(),
            'phone' => $this->string()->null(),
            'chat_id' => $this->string()->null(),
            'device_id' => $this->string()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%profile}}');
    }
}
