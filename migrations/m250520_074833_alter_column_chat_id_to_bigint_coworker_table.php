<?php

use yii\db\Migration;

class m250520_074833_alter_column_chat_id_to_bigint_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn("{{%coworker}}", "chat_id", $this->bigInteger());
        $this->alterColumn("{{%telegram_message}}", "chat_id", $this->bigInteger());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250520_074833_alter_column_chat_id_to_bigint_coworker_table cannot be reverted.\n";

        return false;
    }
    */
}
