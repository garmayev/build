<?php

use yii\db\Migration;

/**
 * Class m250129_075816_add_column_message_id_to_telegram_message_table
 */
class m250129_075816_add_column_message_id_to_telegram_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('telegram_message', 'message_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('telegram_message', 'message_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250129_075816_add_column_message_id_to_telegram_message_table cannot be reverted.\n";

        return false;
    }
    */
}
