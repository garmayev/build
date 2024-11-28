<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_coworker}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%order}}`
 * - `{{%coworker}}`
 */
class m241115_013353_create_junction_table_for_order_and_coworker_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_coworker}}', [
            'order_id' => $this->integer(),
            'coworker_id' => $this->integer(),
            'PRIMARY KEY(order_id, coworker_id)',
        ]);

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_coworker-order_id}}',
            '{{%order_coworker}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_coworker-order_id}}',
            '{{%order_coworker}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );

        // creates index for column `coworker_id`
        $this->createIndex(
            '{{%idx-order_coworker-coworker_id}}',
            '{{%order_coworker}}',
            'coworker_id'
        );

        // add foreign key for table `{{%coworker}}`
        $this->addForeignKey(
            '{{%fk-order_coworker-coworker_id}}',
            '{{%order_coworker}}',
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
        // drops foreign key for table `{{%order}}`
        $this->dropForeignKey(
            '{{%fk-order_coworker-order_id}}',
            '{{%order_coworker}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_coworker-order_id}}',
            '{{%order_coworker}}'
        );

        // drops foreign key for table `{{%coworker}}`
        $this->dropForeignKey(
            '{{%fk-order_coworker-coworker_id}}',
            '{{%order_coworker}}'
        );

        // drops index for column `coworker_id`
        $this->dropIndex(
            '{{%idx-order_coworker-coworker_id}}',
            '{{%order_coworker}}'
        );

        $this->dropTable('{{%order_coworker}}');
    }
}
