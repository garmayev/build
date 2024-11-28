<?php

use yii\db\Migration;

/**
 * Class m241127_095046_add_column_dimension_id_to_coworker_property_table
 */
class m241127_095046_add_column_dimension_id_to_coworker_property_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coworker_property}}', 'dimension_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%coworker_property}}', 'dimension_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m241127_095046_add_column_dimension_id_to_coworker_property_table cannot be reverted.\n";

        return false;
    }
    */
}
