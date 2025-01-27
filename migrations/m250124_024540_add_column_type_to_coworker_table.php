<?php

use yii\db\Migration;

/**
 * Class m250124_024540_add_column_type_to_coworker_table
 */
class m250124_024540_add_column_type_to_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('coworker', 'type', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('coworker', 'type');
    }
}
