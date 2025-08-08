<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%config}}`.
 */
class m250808_064823_create_config_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%config}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->unique()->notNull(),
            'value' => $this->string()->notNull(),
            'label' => $this->string(),
        ]);

        $this->insert('config', [
            'id' => 1,
            'name' => 'priority_level_0',
            'value' => "3:00",
            'label' => 'Priority low',
        ]);
        $this->insert('config', [
            'id' => 2,
            'name' => 'priority_level_1',
            'value' => "2:00",
            'label' => 'Priority normal',
        ]);
        $this->insert('config', [
            'id' => 3,
            'name' => 'priority_level_2',
            'value' => "1:00",
            'label' => 'Priority high',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%config}}');
    }
}
