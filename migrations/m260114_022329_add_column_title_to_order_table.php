<?php

use yii\db\Migration;

class m260114_022329_add_column_title_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'title', $this->string());
        $this->addColumn('order', 'is_payed', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'title');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260114_022329_add_column_title_to_order_table cannot be reverted.\n";

        return false;
    }
    */
}
