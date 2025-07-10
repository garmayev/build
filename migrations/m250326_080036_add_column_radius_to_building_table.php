<?php
namespace app\migrations;

use yii\db\Migration;

/**
 * Class m250326_080036_add_column_radius_to_building_table
 */
class m250326_080036_add_column_radius_to_building_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%building}}', 'radius', $this->integer());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%building}}', 'radius');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m250326_080036_add_column_radius_to_building_table cannot be reverted.\n";

        return false;
    }
    */
}
