<?php

use yii\db\Migration;

/**
 * Class m241125_070733_add_column_value_for_coworker_property_table
 */
class m241125_070733_add_column_value_for_coworker_property_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coworker_property}}', 'value', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%coworker_property}}', 'value');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241125_070733_add_column_value_for_coworker_property_table cannot be reverted.\n";

        return false;
    }
    */
}
