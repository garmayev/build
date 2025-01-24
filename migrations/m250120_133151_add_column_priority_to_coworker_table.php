<?php

use yii\db\Migration;

/**
 * Class m250120_133151_add_column_priority_to_coworker_table
 */
class m250120_133151_add_column_priority_to_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn("{{%coworker}}", "priority", $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn("{{%coworker}}", "priority");
    }
}
