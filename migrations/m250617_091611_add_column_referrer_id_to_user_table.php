<?php
namespace app\migrations;

use yii\db\Migration;

class m250617_091611_add_column_referrer_id_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'referrer_id', $this->integer());

        $this->createIndex('idx_user_referrer_id', '{{%user}}', 'referrer_id');
        $this->addForeignKey('fk_user_referrer_id', '{{%user}}', 'referrer_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_user_referrer_id', '{{%user}}');
        $this->dropIndex('idx_user_referrer_id', '{{%user}}');
        $this->dropColumn('{{%user}}', 'referrer_id');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250617_091611_add_column_refferrer_id_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
