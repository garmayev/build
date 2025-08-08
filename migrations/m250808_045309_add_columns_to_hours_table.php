<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%hours}}`.
 */
class m250808_045309_add_columns_to_hours_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%hours}}', 'start_time', $this->datetime());
        $this->addColumn('{{%hours}}', 'stop_time', $this->datetime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%hours}}', 'start_time');
        $this->dropColumn('{{%hours}}', 'stop_time');
    }
}
