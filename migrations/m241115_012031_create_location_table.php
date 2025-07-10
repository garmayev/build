<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%location}}`.
 */
class m241115_012031_create_location_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%location}}', [
            'id' => $this->primaryKey(),
            'address' => $this->string(),
            'latitude' => $this->double(),
            'longitude' => $this->double(),
            'user_id' => $this->integer(),
        ]);
        $this->createIndex(
            'idx-location-user_id',
            '{{%location}}',
            'user_id'
        );
        $this->addForeignKey(
            'fk-location-user_id',
            '{{%location}}',
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
        $this->dropForeignKey( 'fk-location-user_id', '{{%location}}' );
        $this->dropIndex('idx-location-user_id', '{{%location}}');
        $this->dropTable('{{%location}}');
    }
}
