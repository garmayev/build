<?php

use yii\db\Migration;

/**
 * Class m250124_012655_add_column_notify_stage_to_order_table
 */
class m250124_012655_add_column_notify_stage_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'notify_stage', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'notify_stage');
    }
}
