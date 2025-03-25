<?php

use yii\db\Migration;

/**
 * Class m250321_010519_add_column_create_by_to_coworker_table
 */
class m250321_010519_add_column_create_by_to_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('coworker', 'created_by', $this->integer());

        $this->createIndex('idx-coworker-created_by', 'coworker', 'created_by');
        $this->addForeignKey('fk-coworker-created_by', 'coworker', 'created_by', 'user', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-coworker-created_by', 'coworker');
        $this->dropIndex('idx-coworker-created_by', 'coworker');
        $this->dropColumn('coworker', 'created_by');
    }
}
