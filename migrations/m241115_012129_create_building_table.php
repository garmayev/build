<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%building}}`.
 */
class m241115_012129_create_building_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%building}}', [
            'id' => $this->primaryKey(),
            'title' => $this->string(),
            'location_id' => $this->integer(),
            'user_id' => $this->integer()
        ]);
        $this->createIndex(
            'idx-building-location_id',
            '{{%building}}',
            'location_id',
        );
        $this->addForeignKey(
            'fk-building-location_id',
            '{{%building}}',
            'location_id',
            '{{%location}}',
            'id'
        );
        $this->createIndex(
            'idx-building-user_id',
            '{{%building}}',
            'user_id');
        $this->addForeignKey(
            'fk-building-user_id',
            '{{%building}}',
            'user_id',
            '{{%user}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-building-user_id', '{{%building}}');
        $this->dropIndex('idx-building-user_id', '{{%building}}');
        $this->dropForeignKey('fk-building-location_id', '{{%building}}');
        $this->dropIndex('idx-building-location_id', '{{%building}}');
        $this->dropTable('{{%building}}');
    }
}
