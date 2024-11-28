<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_material}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%order}}`
 * - `{{%material}}`
 */
class m241115_013403_create_junction_table_for_order_and_material_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_material}}', [
            'order_id' => $this->integer(),
            'material_id' => $this->integer(),
            'PRIMARY KEY(order_id, material_id)',
        ]);

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_material-order_id}}',
            '{{%order_material}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_material-order_id}}',
            '{{%order_material}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );

        // creates index for column `material_id`
        $this->createIndex(
            '{{%idx-order_material-material_id}}',
            '{{%order_material}}',
            'material_id'
        );

        // add foreign key for table `{{%material}}`
        $this->addForeignKey(
            '{{%fk-order_material-material_id}}',
            '{{%order_material}}',
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
        // drops foreign key for table `{{%order}}`
        $this->dropForeignKey(
            '{{%fk-order_material-order_id}}',
            '{{%order_material}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_material-order_id}}',
            '{{%order_material}}'
        );

        // drops foreign key for table `{{%material}}`
        $this->dropForeignKey(
            '{{%fk-order_material-material_id}}',
            '{{%order_material}}'
        );

        // drops index for column `material_id`
        $this->dropIndex(
            '{{%idx-order_material-material_id}}',
            '{{%order_material}}'
        );

        $this->dropTable('{{%order_material}}');
    }
}
