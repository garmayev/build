<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%technique_property}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%technique}}`
 * - `{{%property}}`
 */
class m241114_093947_create_junction_table_for_technique_and_property_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%technique_property}}', [
            'technique_id' => $this->integer(),
            'property_id' => $this->integer(),
            'PRIMARY KEY(technique_id, property_id)',
        ]);

        // creates index for column `technique_id`
        $this->createIndex(
            '{{%idx-technique_property-technique_id}}',
            '{{%technique_property}}',
            'technique_id'
        );

        // add foreign key for table `{{%technique}}`
        $this->addForeignKey(
            '{{%fk-technique_property-technique_id}}',
            '{{%technique_property}}',
            'technique_id',
            '{{%technique}}',
            'id',
            'CASCADE'
        );

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-technique_property-property_id}}',
            '{{%technique_property}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-technique_property-property_id}}',
            '{{%technique_property}}',
            'property_id',
            '{{%property}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%technique}}`
        $this->dropForeignKey(
            '{{%fk-technique_property-technique_id}}',
            '{{%technique_property}}'
        );

        // drops index for column `technique_id`
        $this->dropIndex(
            '{{%idx-technique_property-technique_id}}',
            '{{%technique_property}}'
        );

        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-technique_property-property_id}}',
            '{{%technique_property}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-technique_property-property_id}}',
            '{{%technique_property}}'
        );

        $this->dropTable('{{%technique_property}}');
    }
}
