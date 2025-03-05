<?php

use yii\db\Migration;

/**
 * Class m250218_011602_add_column_chat_id_to_coworker_table
 */
class m250218_011602_add_column_chat_id_to_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coworker}}', 'chat_id', $this->string()->null());
        $this->addColumn('{{%coworker}}', 'device_id', $this->string()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%coworker}}', 'chat_id');
        $this->dropColumn('{{%coworker}}', 'device_id');
    }
}
