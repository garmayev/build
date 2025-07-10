<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_property}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 * - `{{%property}}`
 */
class m241115_100613_create_junction_table_for_category_and_property_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_property}}', [
            'category_id' => $this->integer(),
            'property_id' => $this->integer(),
            'PRIMARY KEY(category_id, property_id)',
        ]);

        // creates index for column `category_id`
        $this->createIndex(
            '{{%idx-category_property-category_id}}',
            '{{%category_property}}',
            'category_id'
        );

        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-category_property-category_id}}',
            '{{%category_property}}',
            'category_id',
            '{{%category}}',
            'id',
            'CASCADE'
        );

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-category_property-property_id}}',
            '{{%category_property}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-category_property-property_id}}',
            '{{%category_property}}',
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
        // drops foreign key for table `{{%category}}`
        $this->dropForeignKey(
            '{{%fk-category_property-category_id}}',
            '{{%category_property}}'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            '{{%idx-category_property-category_id}}',
            '{{%category_property}}'
        );

        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-category_property-property_id}}',
            '{{%category_property}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-category_property-property_id}}',
            '{{%category_property}}'
        );

        $this->dropTable('{{%category_property}}');
    }
}
