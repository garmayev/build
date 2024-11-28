<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_technique}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%order}}`
 * - `{{%technique}}`
 */
class m241115_013416_create_junction_table_for_order_and_technique_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_technique}}', [
            'order_id' => $this->integer(),
            'technique_id' => $this->integer(),
            'PRIMARY KEY(order_id, technique_id)',
        ]);

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_technique-order_id}}',
            '{{%order_technique}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_technique-order_id}}',
            '{{%order_technique}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );

        // creates index for column `technique_id`
        $this->createIndex(
            '{{%idx-order_technique-technique_id}}',
            '{{%order_technique}}',
            'technique_id'
        );

        // add foreign key for table `{{%technique}}`
        $this->addForeignKey(
            '{{%fk-order_technique-technique_id}}',
            '{{%order_technique}}',
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
        // drops foreign key for table `{{%order}}`
        $this->dropForeignKey(
            '{{%fk-order_technique-order_id}}',
            '{{%order_technique}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_technique-order_id}}',
            '{{%order_technique}}'
        );

        // drops foreign key for table `{{%technique}}`
        $this->dropForeignKey(
            '{{%fk-order_technique-technique_id}}',
            '{{%order_technique}}'
        );

        // drops index for column `technique_id`
        $this->dropIndex(
            '{{%idx-order_technique-technique_id}}',
            '{{%order_technique}}'
        );

        $this->dropTable('{{%order_technique}}');
    }
}
