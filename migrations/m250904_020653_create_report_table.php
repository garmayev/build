<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%report}}`.
 */
class m250904_020653_create_report_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%report}}', [
            'id' => $this->primaryKey(),
            'comment' => $this->text(),
            'order_id' => $this->integer(),
            'created_at' => $this->integer(),
        ]);

        $this->createIndex('idx-report-order_id', '{{%report}}', 'order_id');
        $this->addForeignKey('fk-report-order_id', '{{%report}}', 'order_id', '{{%order}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-report-order_id', '{{%report}}');
        $this->dropIndex('idx-report-order_id', '{{%report}}');
        $this->dropTable('{{%report}}');
    }
}
