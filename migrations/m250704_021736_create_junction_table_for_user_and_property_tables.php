<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_property}}`.
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%property}}`
 */
class m250704_021736_create_junction_table_for_user_and_property_tables extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_property}}', [
            'user_id' => $this->integer()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'property_id' => $this->integer()->notNull(),
            'dimension_id' => $this->integer()->notNull(),
            'value' => $this->integer()->notNull(),
            'PRIMARY KEY(user_id, category_id, property_id, dimension_id)',
        ]);

        // creates index for column `user_id`
        $this->createIndex(
            '{{%idx-user_property-user_id}}',
            '{{%user_property}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        $this->addForeignKey(
            '{{%fk-user_property-user_id}}',
            '{{%user_property}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `property_id`
        $this->createIndex(
            '{{%idx-user_property-property_id}}',
            '{{%user_property}}',
            'property_id'
        );

        // add foreign key for table `{{%property}}`
        $this->addForeignKey(
            '{{%fk-user_property-property_id}}',
            '{{%user_property}}',
            'property_id',
            '{{%property}}',
            'id',
            'CASCADE'
        );

        // creates index for column `category_id`
        $this->createIndex('idx-user_property-category_id', '{{%user_property}}', 'category_id');

        // add foreign key for table `{{%category}}`
        $this->addForeignKey('fk-user_property-category_id', '{{%user_property}}', 'category_id', '{{%category}}', 'id');

        // creates index for column `dimension_id`
        $this->createIndex('idx-user_property-dimension_id', '{{%user_property}}', 'dimension_id');

        // add foreign key for table `{{%dimension}}`
        $this->addForeignKey('fk-user_property-dimension_id', '{{%user_property}}', 'dimension_id', '{{%dimension}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        $this->dropForeignKey(
            '{{%fk-user_property-user_id}}',
            '{{%user_property}}'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            '{{%idx-user_property-user_id}}',
            '{{%user_property}}'
        );

        // drops foreign key for table `{{%property}}`
        $this->dropForeignKey(
            '{{%fk-user_property-property_id}}',
            '{{%user_property}}'
        );

        // drops index for column `property_id`
        $this->dropIndex(
            '{{%idx-user_property-property_id}}',
            '{{%user_property}}'
        );

        $this->dropForeignKey('{{%fk-user_property-category_id}}', '{{%user_property}}');

        $this->dropIndex('idx-user_property-category_id', '{{%user_property}}');

        $this->dropForeignKey('{{%fk-user_property-dimension_id}}', '{{%user_property}}');

        $this->dropIndex('idx-user_property-dimension_id', '{{%user_property}}');

        $this->dropTable('{{%user_property}}');
    }
}
