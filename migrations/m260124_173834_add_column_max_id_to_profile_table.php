<?php

use yii\db\Migration;

class m260124_173834_add_column_max_id_to_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile', 'max_id', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('profile', 'max_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260124_173834_add_column_max_id_to_profile_table cannot be reverted.\n";

        return false;
    }
    */
}
