<?php

use yii\db\Migration;

class m260630_074527_add_column_user_id_to_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('report', 'user_id', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('report', 'user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260630_074527_add_column_user_id_to_report_table cannot be reverted.\n";

        return false;
    }
    */
}
