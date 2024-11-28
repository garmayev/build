<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%material}}`.
 */
class m241114_092105_create_material_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%material}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'price' => $this->double()->null(),
            'image' => $this->string(),
            'category_id' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-material-category_id',
            '{{%material}}',
            'category_id'
        );

        $this->addForeignKey(
            'fk-material-category_id',
            '{{%material}}',
            'category_id',
            '{{%category}}',
            'id',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-material-category_id', '{{%material}}');
        $this->dropIndex('idx-material-category_id', '{{%material}}');
        $this->dropTable('{{%material}}');
    }
}
