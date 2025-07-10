<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Class m250205_065900_add_column_created_by_to_order_table
 */
class m250205_065900_add_column_created_by_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'created_by', $this->integer());
        $this->createIndex(
            'idx-order-created_by',
            '{{%order}}',
            'created_by'
        );

        $this->addForeignKey(
            'fk-order-created_by',
            '{{%order}}',
            'created_by',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-order-created_by', '{{%order}}');
        $this->dropIndex('idx-order-created_by', '{{%order}}');
        $this->dropColumn('{{%order}}', 'created_by');
    }
}
