<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%attachment}}`.
 */
class m250116_152819_create_attachment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%attachment}}', [
            'id' => $this->primaryKey(),
            'url' => $this->string(),
            'target_class' => $this->string(),
            'target_id' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%attachment}}');
    }
}
