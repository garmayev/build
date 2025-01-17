<?php

use yii\db\Migration;

/**
 * Class m250116_171441_add_column_status_to_telegram_message_table
 */
class m250116_171441_add_column_status_to_telegram_message_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('telegram_message', 'status', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('telegram_message', 'status');
    }
}
