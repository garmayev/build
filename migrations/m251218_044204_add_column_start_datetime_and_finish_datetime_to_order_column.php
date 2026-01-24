<?php

use yii\db\Migration;

class m251218_044204_add_column_start_datetime_and_finish_datetime_to_order_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'start_datetime', $this->dateTime());
        $this->addColumn('order', 'finish_datetime', $this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'start_datetime');
        $this->dropColumn('order', 'finish_datetime');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m251218_044204_add_column_start_datetime_and_finish_datetime_to_order_column cannot be reverted.\n";

        return false;
    }
    */
}
