<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%tech}}`.
 */
class m241114_093106_create_technique_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%technique}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'coworker_id' => $this->integer()
        ]);
        $this->createIndex(
            'idx-technique-coworker_id',
            '{{%technique}}',
            'coworker_id'
        );
        $this->addForeignKey(
            'fk-technique-coworker_id',
            '{{%technique}}',
            'coworker_id',
            '{{%coworker}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-technique-coworker_id', '{{%technique}}');
        $this->dropIndex('idx-technique-coworker_id', '{{%technique}}');
        $this->dropTable('{{%technique}}');
    }
}
