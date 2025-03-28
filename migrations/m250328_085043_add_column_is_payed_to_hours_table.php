<?php

use yii\db\Migration;

/**
 * Class m250328_085043_add_column_is_payed_to_hours_table
 */
class m250328_085043_add_column_is_payed_to_hours_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('hours', 'order_id');
        $this->addColumn('hours', 'is_payed', $this->boolean()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('hours', 'is_payed');
        $this->addColumn('hours', 'order_id', $this->integer());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250328_085043_add_column_is_payed_to_hours_table cannot be reverted.\n";

        return false;
    }
    */
}
