<?php

namespace tests\unit\models;

use app\models\Coworker;
use Codeception\Test\Unit;

class CoworkerTest extends Unit
{
    /**
     * @var \UnitTester $tester
     */
    public $tester;

    public function testLoadData()
    {
        $model = new Coworker();
        verify($model->load([
            "Coworker" => [
                "firstname" => "John",
                "lastname" => "Smith",
                "email" => "john@smith.com",
                "phone" => "+7 (999) 999-99-99",
                "priority" => Coworker::PRIORITY_HIGH,
                "category_id" => 2
            ]
        ]))->true();
        verify($model->save())->true();
    }
}