<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%hours}}`.
 */
class m250212_032455_create_hours_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%hours}}', [
            'order_id' => $this->integer(),
            'user_id' => $this->integer(),
            'date' => $this->date(),
            'count' => $this->integer(),
            'PRIMARY KEY(order_id, user_id, date)'
        ]);

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%hours}}');
    }
}
