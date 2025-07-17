<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%price}}`.
 */
class m250613_071603_create_price_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%price}}', [
            'id' => $this->primaryKey(),
            'price' => $this->float(),
            'user_id' => $this->integer(),
            'date' => $this->date(),
        ]);

        $this->createIndex('idx_price_user_id', '{{%price}}', 'user_id');
        $this->addForeignKey('fk_price_user_id', '{{%price}}', 'user_id', '{{%user}}', 'id');
        $this->createIndex('idx_price_date', '{{%price}}', 'date');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx_price_date', '{{%price}}');
        $this->dropForeignKey('fk_price_user_id', '{{%price}}');
        $this->dropIndex('idx_price_user_id', '{{%price}}');

        $this->dropTable('{{%price}}');
    }
}
