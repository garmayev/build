<?php

use yii\db\Migration;

class m260124_035432_add_column_user_id_to_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile', 'user_id', $this->integer()->notNull());
        $this->createIndex('idx_profile_user_id', 'profile', 'user_id');
        $this->addForeignKey('fk_profile_user_id', 'profile', 'user_id', 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_profile_user_id', 'profile');
        $this->dropIndex('idx_profile_user_id', 'profile');
        $this->dropColumn('profile', 'user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m260124_035432_add_column_user_id_to_profile_table cannot be reverted.\n";

        return false;
    }
    */
}
