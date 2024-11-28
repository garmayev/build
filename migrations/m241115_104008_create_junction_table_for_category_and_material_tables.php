<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_material}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 * - `{{%material}}`
 */
class m241115_104008_create_junction_table_for_category_and_material_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_material}}', [
            'category_id' => $this->integer(),
            'material_id' => $this->integer(),
            'PRIMARY KEY(category_id, material_id)',
        ]);

        // creates index for column `category_id`
        $this->createIndex(
            '{{%idx-category_material-category_id}}',
            '{{%category_material}}',
            'category_id'
        );

        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-category_material-category_id}}',
            '{{%category_material}}',
            'category_id',
            '{{%category}}',
            'id',
            'CASCADE'
        );

        // creates index for column `material_id`
        $this->createIndex(
            '{{%idx-category_material-material_id}}',
            '{{%category_material}}',
            'material_id'
        );

        // add foreign key for table `{{%material}}`
        $this->addForeignKey(
            '{{%fk-category_material-material_id}}',
            '{{%category_material}}',
            'material_id',
            '{{%material}}',
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
            '{{%fk-category_material-category_id}}',
            '{{%category_material}}'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            '{{%idx-category_material-category_id}}',
            '{{%category_material}}'
        );

        // drops foreign key for table `{{%material}}`
        $this->dropForeignKey(
            '{{%fk-category_material-material_id}}',
            '{{%category_material}}'
        );

        // drops index for column `material_id`
        $this->dropIndex(
            '{{%idx-category_material-material_id}}',
            '{{%category_material}}'
        );

        $this->dropTable('{{%category_material}}');
    }
}
