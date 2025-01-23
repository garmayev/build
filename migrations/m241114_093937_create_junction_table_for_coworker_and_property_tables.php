<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%coworker_property}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%coworker}}`
 * - `{{%property}}`
 */
class m241114_093937_create_junction_table_for_coworker_and_property_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%coworker_property}}', [
            'coworker_id' => $this->integer(),
            'property_id' => $this->integer(),
            'dimension_id' => $this->integer(),
            'value' => $this->integer(),
            'PRIMARY KEY(coworker_id, property_id, dimension_id)',
        ]);

        // creates index for column `coworker_id`
        $this->createIndex(
            '{{%idx-coworker_property-coworker_id}}',
            '{{%coworker_property}}',
            'coworker_id'
        );

        // add foreign key for table `{{%coworker}}`
        $this->addForeignKey(
            '{{%fk-coworker_property-coworker_id}}',
            '{{%coworker_property}}',
            'coworker_id',
            '{{%coworker}}',
            'id',
            'CASCADE'
        );

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-coworker_property-property_id}}',
            '{{%coworker_property}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-coworker_property-property_id}}',
            '{{%coworker_property}}',
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
        // drops foreign key for table `{{%coworker}}`
        $this->dropForeignKey(
            '{{%fk-coworker_property-coworker_id}}',
            '{{%coworker_property}}'
        );

        // drops index for column `coworker_id`
        $this->dropIndex(
            '{{%idx-coworker_property-coworker_id}}',
            '{{%coworker_property}}'
        );

        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-coworker_property-property_id}}',
            '{{%coworker_property}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-coworker_property-property_id}}',
            '{{%coworker_property}}'
        );

        $this->dropTable('{{%coworker_property}}');
    }
}
