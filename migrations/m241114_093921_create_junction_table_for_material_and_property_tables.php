<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%material_property}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%material}}`
 * - `{{%property}}`
 */
class m241114_093921_create_junction_table_for_material_and_property_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%material_property}}', [
            'material_id' => $this->integer(),
            'property_id' => $this->integer(),
            'PRIMARY KEY(material_id, property_id)',
        ]);

        // creates index for column `material_id`
        $this->createIndex(
            '{{%idx-material_property-material_id}}',
            '{{%material_property}}',
            'material_id'
        );

        // add foreign key for table `{{%material}}`
        $this->addForeignKey(
            '{{%fk-material_property-material_id}}',
            '{{%material_property}}',
            'material_id',
            '{{%material}}',
            'id',
            'CASCADE'
        );

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-material_property-property_id}}',
            '{{%material_property}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-material_property-property_id}}',
            '{{%material_property}}',
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
        // drops foreign key for table `{{%material}}`
        $this->dropForeignKey(
            '{{%fk-material_property-material_id}}',
            '{{%material_property}}'
        );

        // drops index for column `material_id`
        $this->dropIndex(
            '{{%idx-material_property-material_id}}',
            '{{%material_property}}'
        );

        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-material_property-property_id}}',
            '{{%material_property}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-material_property-property_id}}',
            '{{%material_property}}'
        );

        $this->dropTable('{{%material_property}}');
    }
}
