<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category}}`.
 */
class m241114_092018_create_category_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'type' => $this->integer()->notNull(),
            'parent_id' => $this->integer()->null(),
        ]);

        $this->createIndex(
            'idx-category-parent_id',
            '{{%category}}',
            'parent_id'
        );
        $this->addForeignKey(
            'fk-category-parent_id',
            '{{%category}}',
            'parent_id',
            '{{%category}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-category-parent_id', '{{%category}}');
        $this->dropIndex('idx-category-parent_id', '{{%category}}');
        $this->dropTable('{{%category}}');
    }
}
