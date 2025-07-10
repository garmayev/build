<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Class m250218_011934_add_column_priority_level_to_order_table
 */
class m250218_011934_add_column_priority_level_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'priority_level', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'priority_level');
    }
}
