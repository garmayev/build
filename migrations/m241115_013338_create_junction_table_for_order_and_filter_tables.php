<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_filter}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%order}}`
 * - `{{%filter}}`
 */
class m241115_013338_create_junction_table_for_order_and_filter_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_filter}}', [
            'order_id' => $this->integer(),
            'filter_id' => $this->integer(),
            'PRIMARY KEY(order_id, filter_id)',
        ]);

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_filter-order_id}}',
            '{{%order_filter}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_filter-order_id}}',
            '{{%order_filter}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );

        // creates index for column `filter_id`
        $this->createIndex(
            '{{%idx-order_filter-filter_id}}',
            '{{%order_filter}}',
            'filter_id'
        );

        // add foreign key for table `{{%filter}}`
        $this->addForeignKey(
            '{{%fk-order_filter-filter_id}}',
            '{{%order_filter}}',
            'filter_id',
            '{{%filter}}',
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
            '{{%fk-order_filter-order_id}}',
            '{{%order_filter}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_filter-order_id}}',
            '{{%order_filter}}'
        );

        // drops foreign key for table `{{%filter}}`
        $this->dropForeignKey(
            '{{%fk-order_filter-filter_id}}',
            '{{%order_filter}}'
        );

        // drops index for column `filter_id`
        $this->dropIndex(
            '{{%idx-order_filter-filter_id}}',
            '{{%order_filter}}'
        );

        $this->dropTable('{{%order_filter}}');
    }
}
