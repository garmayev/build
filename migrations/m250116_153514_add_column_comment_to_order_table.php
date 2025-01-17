<?php

use yii\db\Migration;

/**
 * Class m250116_153514_add_column_comment_to_order_table
 */
class m250116_153514_add_column_comment_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'comment', 'LONGTEXT');
        $this->addColumn('{{%order}}', 'created_at', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'comment');
    }
}
