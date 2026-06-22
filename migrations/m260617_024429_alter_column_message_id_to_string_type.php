<?php

use yii\db\Migration;

class m260617_024429_alter_column_message_id_to_string_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn("{{%telegram_message}}", "message_id", $this->string(255)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn("{{%telegram_message}}", "message_id", $this->string(255)->notNull());
    }
}
