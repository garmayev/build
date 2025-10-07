<?php

use yii\db\Migration;

class m251004_042817_add_column_summary_to_order_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%order}}', 'summary', $this->string(15));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%order}}', 'summary');
    }

}
