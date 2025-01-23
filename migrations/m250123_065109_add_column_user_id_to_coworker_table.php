<?php

use yii\db\Migration;

/**
 * Class m250123_065109_add_column_user_id_to_coworker_table
 */
class m250123_065109_add_column_user_id_to_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%coworker}}', 'user_id', $this->integer());
        $this->createIndex(
            '{{%idx-coworker-user_id}}',
            '{{%coworker}}',
            'user_id'
        );
        $this->addForeignKey(
            '{{%fk-coworker-user_id}}',
            '{{%coworker}}',
            'user_id',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-coworker-user_id}}', '{{%coworker}}');
        $this->dropIndex('{{%idx-coworker-user_id}}', '{{%coworker}}');
        $this->dropColumn('{{%coworker}}', 'user_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250123_065109_add_column_user_id_to_coworker_table cannot be reverted.\n";

        return false;
    }
    */
}
