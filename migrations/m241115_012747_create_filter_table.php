<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%filter}}`.
 */
class m241115_012747_create_filter_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%filter}}', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer(),
            'count' => $this->integer()
        ]);
        $this->createIndex(
            'idx-filter-category_id',
            '{{%filter}}',
            'category_id'
        );
        $this->addForeignKey(
            'fk-filter-category_id',
            '{{%filter}}',
            'category_id',
            '{{%category}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-filter-category_id', '{{%filter}}');
        $this->dropIndex('idx-filter-category_id', '{{%filter}}');
        $this->dropTable('{{%filter}}');
    }
}
