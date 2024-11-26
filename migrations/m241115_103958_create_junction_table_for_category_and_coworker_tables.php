<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%category_coworker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%category}}`
 * - `{{%coworker}}`
 */
class m241115_103958_create_junction_table_for_category_and_coworker_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%category_coworker}}', [
            'category_id' => $this->integer(),
            'coworker_id' => $this->integer(),
            'PRIMARY KEY(category_id, coworker_id)',
        ]);

        // creates index for column `category_id`
        $this->createIndex(
            '{{%idx-category_coworker-category_id}}',
            '{{%category_coworker}}',
            'category_id'
        );

        // add foreign key for table `{{%category}}`
        $this->addForeignKey(
            '{{%fk-category_coworker-category_id}}',
            '{{%category_coworker}}',
            'category_id',
            '{{%category}}',
            'id',
            'CASCADE'
        );

        // creates index for column `coworker_id`
        $this->createIndex(
            '{{%idx-category_coworker-coworker_id}}',
            '{{%category_coworker}}',
            'coworker_id'
        );

        // add foreign key for table `{{%coworker}}`
        $this->addForeignKey(
            '{{%fk-category_coworker-coworker_id}}',
            '{{%category_coworker}}',
            'coworker_id',
            '{{%coworker}}',
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
            '{{%fk-category_coworker-category_id}}',
            '{{%category_coworker}}'
        );

        // drops index for column `category_id`
        $this->dropIndex(
            '{{%idx-category_coworker-category_id}}',
            '{{%category_coworker}}'
        );

        // drops foreign key for table `{{%coworker}}`
        $this->dropForeignKey(
            '{{%fk-category_coworker-coworker_id}}',
            '{{%category_coworker}}'
        );

        // drops index for column `coworker_id`
        $this->dropIndex(
            '{{%idx-category_coworker-coworker_id}}',
            '{{%category_coworker}}'
        );

        $this->dropTable('{{%category_coworker}}');
    }
}
