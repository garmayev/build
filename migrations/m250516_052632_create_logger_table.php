<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%loggder}}`.
 */
class m250516_052632_create_logger_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%logger}}', [
            'id' => $this->primaryKey(),
            'created_at' => $this->integer(),
            'created_by' => $this->integer(),
            'target_class' => $this->string()->notNull(),
            'target_id' => $this->integer()->notNull(),
            'target_attribute' => $this->string(),
            'action' => $this->string()->notNull(),
            'new_value' => $this->string(),
            'old_value' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%logger}}');
    }
}
