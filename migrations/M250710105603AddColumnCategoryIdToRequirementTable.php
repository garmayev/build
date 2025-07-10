<?php

namespace app\migrations;

use yii\db\Migration;

class M250710105603AddColumnCategoryIdToRequirementTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "M250710105603AddColumnCategoryIdToRequirementTable cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M250710105603AddColumnCategoryIdToRequirementTable cannot be reverted.\n";

        return false;
    }
    */
}
