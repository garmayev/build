<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%order_user}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%order}}`
 * - `{{%user}}`
 */
class m250707_035114_create_junction_table_for_order_and_user_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%order_user}}', [
            'order_id' => $this->integer(),
            'user_id' => $this->integer(),
            'PRIMARY KEY(order_id, user_id)',
        ]);

        // creates index for column `order_id`
        $this->createIndex(
            '{{%idx-order_user-order_id}}',
            '{{%order_user}}',
            'order_id'
        );

        // add foreign key for table `{{%order}}`
        $this->addForeignKey(
            '{{%fk-order_user-order_id}}',
            '{{%order_user}}',
            'order_id',
            '{{%order}}',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-order_user-user_id}}',
            '{{%order_user}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-order_user-user_id}}',
            '{{%order_user}}',
            'user_id',
            '{{%user}}',
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
            '{{%fk-order_user-order_id}}',
            '{{%order_user}}'
        );

        // drops index for column `order_id`
        $this->dropIndex(
            '{{%idx-order_user-order_id}}',
            '{{%order_user}}'
        );

        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-order_user-user_id}}',
            '{{%order_user}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-order_user-user_id}}',
            '{{%order_user}}'
        );

        $this->dropTable('{{%order_user}}');
    }
}
