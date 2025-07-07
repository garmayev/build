<?php

use yii\db\Migration;

class m250619_025901_add_column_priority_level_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'priority_level', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%user}}', 'priority_level');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250619_025901_add_column_priority_level_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
