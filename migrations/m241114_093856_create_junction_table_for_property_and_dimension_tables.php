<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%property_dimension}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%property}}`
 * - `{{%dimension}}`
 */
class m241114_093856_create_junction_table_for_property_and_dimension_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%property_dimension}}', [
            'property_id' => $this->integer(),
            'dimension_id' => $this->integer(),
            'PRIMARY KEY(property_id, dimension_id)',
        ]);

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-property_dimension-property_id}}',
            '{{%property_dimension}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-property_dimension-property_id}}',
            '{{%property_dimension}}',
            'property_id',
            '{{%property}}',
            'id',
            'CASCADE'
        );

        // creates index for column `dimension_id`
        $this->createIndex(
            '{{%idx-property_dimension-dimension_id}}',
            '{{%property_dimension}}',
            'dimension_id'
        );

        // add foreign key for table `{{%dimension}}`
        $this->addForeignKey(
            '{{%fk-property_dimension-dimension_id}}',
            '{{%property_dimension}}',
            'dimension_id',
            '{{%dimension}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-property_dimension-property_id}}',
            '{{%property_dimension}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-property_dimension-property_id}}',
            '{{%property_dimension}}'
        );

        // drops foreign key for table `{{%dimension}}`
        $this->dropForeignKey(
            '{{%fk-property_dimension-dimension_id}}',
            '{{%property_dimension}}'
        );

        // drops index for column `dimension_id`
        $this->dropIndex(
            '{{%idx-property_dimension-dimension_id}}',
            '{{%property_dimension}}'
        );

        $this->dropTable('{{%property_dimension}}');
    }
}
