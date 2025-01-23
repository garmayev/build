<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%coworker}}`.
 */
class m241114_092607_create_coworker_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%coworker}}', [
            'id' => $this->primaryKey(),
            'firstname' => $this->string()->notNull(),
            'lastname' => $this->string()->notNull(),
            'email' => $this->string()->notNull(),
            'phone' => $this->string()->notNull(),
            'priority' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
        ]);
        $this->createIndex(
            'idx-coworker-user_id',
            '{{%coworker}}',
            'user_id'
        );
        $this->addForeignKey(
            'fk-coworker-user_id',
            '{{%coworker}}',
            'user_id',
            'user',
            'id'
        );
        $this->createIndex(
            'idx-coworker-category_id',
            '{{%coworker}}',
            'category_id',
        );
        $this->addForeignKey(
            'fk-coworker-category_id',
            '{{%coworker}}',
            'category_id',
            '{{%category}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-coworker-category_id', '{{%coworker}}');
        $this->dropIndex('idx-coworker-category_id', '{{%coworker}}');
        $this->dropForeignKey('fk-coworker-user_id', '{{%coworker}}');
        $this->dropIndex('idx-coworker-user_id', '{{%coworker}}');
        $this->dropTable('{{%coworker}}');
    }
}
