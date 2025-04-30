<?php

use yii\db\Migration;

/**
 * Class m250418_103105_add_column_order_id_to_hours_table
 */
class m250418_103105_add_column_order_id_to_hours_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%hours}}', 'order_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%hours}}', 'order_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250418_103105_add_column_order_id_to_hours_table cannot be reverted.\n";

        return false;
    }
    */
}
