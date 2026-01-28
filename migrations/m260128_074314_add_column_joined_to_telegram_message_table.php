<?php

use yii\db\Migration;

class m260128_074314_add_column_joined_to_telegram_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('telegram_message', 'joined', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m260128_074314_add_column_joined_to_telegram_message_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260128_074314_add_column_joined_to_telegram_message_table cannot be reverted.\n";

        return false;
    }
    */
}
