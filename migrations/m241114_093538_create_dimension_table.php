<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%dimension}}`.
 */
class m241114_093538_create_dimension_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%dimension}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'multiplier' => $this->double()->notNull(),
            'short' => $this->string(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%dimension}}');
    }
}
