<?php
namespace app\migrations;

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
            'coworker_id' => $this->integer(),
            'date' => $this->date(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%price}}');
    }
}
