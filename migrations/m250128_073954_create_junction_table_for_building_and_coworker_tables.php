<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%building_coworker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%building}}`
 * - `{{%coworker}}`
 */
class m250128_073954_create_junction_table_for_building_and_coworker_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%building_coworker}}', [
            'building_id' => $this->integer(),
            'coworker_id' => $this->integer(),
            'PRIMARY KEY(building_id, coworker_id)',
        ]);

        // creates index for column `building_id`
        $this->createIndex(
            '{{%idx-building_coworker-building_id}}',
            '{{%building_coworker}}',
            'building_id'
        );

        // add foreign key for table `{{%building}}`
        $this->addForeignKey(
            '{{%fk-building_coworker-building_id}}',
            '{{%building_coworker}}',
            'building_id',
            '{{%building}}',
            'id',
            'CASCADE'
        );

        // creates index for column `coworker_id`
        $this->createIndex(
            '{{%idx-building_coworker-coworker_id}}',
            '{{%building_coworker}}',
            'coworker_id'
        );

        // add foreign key for table `{{%coworker}}`
        $this->addForeignKey(
            '{{%fk-building_coworker-coworker_id}}',
            '{{%building_coworker}}',
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
        // drops foreign key for table `{{%building}}`
        $this->dropForeignKey(
            '{{%fk-building_coworker-building_id}}',
            '{{%building_coworker}}'
        );

        // drops index for column `building_id`
        $this->dropIndex(
            '{{%idx-building_coworker-building_id}}',
            '{{%building_coworker}}'
        );

        // drops foreign key for table `{{%coworker}}`
        $this->dropForeignKey(
            '{{%fk-building_coworker-coworker_id}}',
            '{{%building_coworker}}'
        );

        // drops index for column `coworker_id`
        $this->dropIndex(
            '{{%idx-building_coworker-coworker_id}}',
            '{{%building_coworker}}'
        );

        $this->dropTable('{{%building_coworker}}');
    }
}
