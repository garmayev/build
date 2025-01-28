<?php

use yii\db\Migration;

/**
 * Class m250128_070603_alter_column_category_id_to_coworker_table
 */
class m250128_070603_alter_column_category_id_to_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropForeignKey('fk-coworker-category_id', 'coworker');
        $this->dropIndex('idx-coworker-category_id', 'coworker');
        $this->alterColumn('coworker', 'category_id', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->createIndex(
            'idx-coworker-category_id',
            '{{%coworker}}',
            'category_id',
        );
        $this->addForeignKey(
            'fk-coworker-category_id',
            '{{%coworker}}',
            'category_id',
            '{{%category}}',
            'id'
        );
        $this->alterColumn('{{%coworker}}', 'category_id', $this->integer());
    }
}
