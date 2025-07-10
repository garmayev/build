<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%property}}`.
 */
class m241114_093708_create_property_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%property}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%property}}');
    }
}
