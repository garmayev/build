<?php

use yii\db\Migration;

class m250905_021958_add_column_mode_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('order', 'mode', $this->smallInteger()->notNull()->defaultValue(0));
        $this->addColumn('order', 'price', $this->decimal(10,2)->notNull()->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('order', 'mode');
        $this->dropColumn('order', 'price');
    }
}
