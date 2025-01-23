<?php

use yii\db\Migration;

/**
 * Class m250123_050059_add_notify_status_to_order_table
 */
class m250123_050059_add_notify_status_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'notify_status', $this->integer()->defaultValue(2));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'notify_status');
    }
}
