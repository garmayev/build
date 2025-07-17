<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%requirement}}`.
 */
class m241115_072802_create_requirement_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%requirement}}', [
            'id' => $this->primaryKey(),
            'property_id' => $this->integer(),
            'dimension_id' => $this->integer(),
            'value' => $this->double(),
            'type' => $this->string(),
        ]);
        $this->createIndex(
            'idx-requirement-property_id',
            '{{%requirement}}',
            'property_id'
        );
        $this->addForeignKey(
            'fk-requirement-property_id',
            '{{%requirement}}',
            'property_id',
            '{{%property}}',
            'id'
        );
        $this->createIndex(
            'idx-requirement-dimension_id',
            '{{%requirement}}',
            'dimension_id',
        );
        $this->addForeignKey(
            'fk-requirement-dimension_id',
            '{{%requirement}}',
            'dimension_id',
            '{{%dimension}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-requirement-property_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-property_id', '{{%requirement}}');
        $this->dropForeignKey('fk-requirement-dimension_id', '{{%requirement}}');
        $this->dropIndex('idx-requirement-dimension_id', '{{%requirement}}');
        $this->dropTable('{{%requirement}}');
    }
}
