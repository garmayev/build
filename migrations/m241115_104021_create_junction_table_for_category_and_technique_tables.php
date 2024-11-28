<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_technique}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 * - `{{%technique}}`
 */
class m241115_104021_create_junction_table_for_category_and_technique_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_technique}}', [
            'category_id' => $this->integer(),
            'technique_id' => $this->integer(),
            'PRIMARY KEY(category_id, technique_id)',
        ]);

        // creates index for column `category_id`
        $this->createIndex(
            '{{%idx-category_technique-category_id}}',
            '{{%category_technique}}',
            'category_id'
        );

        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-category_technique-category_id}}',
            '{{%category_technique}}',
            'category_id',
            '{{%category}}',
            'id',
            'CASCADE'
        );

        // creates index for column `technique_id`
        $this->createIndex(
            '{{%idx-category_technique-technique_id}}',
            '{{%category_technique}}',
            'technique_id'
        );

        // add foreign key for table `{{%technique}}`
        $this->addForeignKey(
            '{{%fk-category_technique-technique_id}}',
            '{{%category_technique}}',
            'technique_id',
            '{{%technique}}',
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
            '{{%fk-category_technique-category_id}}',
            '{{%category_technique}}'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            '{{%idx-category_technique-category_id}}',
            '{{%category_technique}}'
        );

        // drops foreign key for table `{{%technique}}`
        $this->dropForeignKey(
            '{{%fk-category_technique-technique_id}}',
            '{{%category_technique}}'
        );

        // drops index for column `technique_id`
        $this->dropIndex(
            '{{%idx-category_technique-technique_id}}',
            '{{%category_technique}}'
        );

        $this->dropTable('{{%category_technique}}');
    }
}
