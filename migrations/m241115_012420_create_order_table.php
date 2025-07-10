<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order}}`.
 */
class m241115_012420_create_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order}}', [
            'id' => $this->primaryKey(),
            'status' => $this->integer(),
            'building_id' => $this->integer(),
            'date' => $this->integer(),
            'type' => $this->integer(),
            'created_at' => $this->integer(),
            'notify_date' => $this->integer(),
            'comment' => 'LONGTEXT',
        ]);
        $this->createIndex(
            'idx-order-building_id',
            '{{%order}}',
            'building_id'
        );
        $this->addForeignKey(
            'fk-order-building_id',
            '{{%order}}',
            'building_id',
            '{{%building}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-order-building_id', '{{%order}}');
        $this->dropIndex('idx-order-building_id', '{{%order}}');
        $this->dropTable('{{%order}}');
    }
}
