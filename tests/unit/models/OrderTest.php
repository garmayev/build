<?php

namespace tests\unit\models;

use app\models\Coworker;
use app\models\Order;
use Codeception\Test\Unit;
use yii\base\InvalidConfigException;
use yii\db\Exception;

class OrderTest extends Unit
{
    /**
     * @throws Exception
     * @throws InvalidConfigException
     */
    public function testSuccessfully()
    {
        $order = new Order();
        verify($order->load(["Order" => [
            'user_id' => 1,
            'status' => Order::STATUS_NEW,
            'building_id' => 1,
            'date' => time(),
            'type' => 1,
            'comment' => '',
            'created_at' => time(),
            'notify_date' => time(),
            'notify_stage' => 2,
            'created_by' => 1,
            'priority_level' => 1,
            'filters' => [
                [
                    'category_id' => 2,
                    'count' => 3,
                    'requirements' => [
                        [
                            'property' => [
                                'id' => 2,
                            ],
                            'type' => 'Меньше',
                            'value' => 11,
                            'dimension' => [
                                'id' => 1
                            ]
                        ]
                    ]
                ]
            ]
        ]]))->true();
        verify($order->save())->true();
        $coworker = new Coworker();
        $coworker2 = new Coworker();
        $coworker3 = new Coworker();
        verify($coworker->load([
            "Coworker" => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'email' => 'johndoe@example.com',
                'phone' => '0123456789',
                'priority' => Coworker::PRIORITY_HIGH,
                'user_id' => 1,
                'category_id' => 1,
                'coworkerProperties' => [
                    [
                        'property_id' => 2,
                        'value' => 3,
                        'dimension_id' => 1
                    ]
                ]
            ]
        ]));
        verify($coworker2->load([
            "Coworker" => [
                'firstname' => 'James',
                'lastname' => 'Smith',
                'email' => 'james.smith@example.com',
                'phone' => '79999999998',
                'priority' => Coworker::PRIORITY_HIGH,
                'category_id' => 1,
                'coworkerProperties' => [
                    [
                        'property_id' => 2,
                        'value' => 10,
                        'dimension_id' => 1
                    ]
                ]
            ]
        ]));
        verify($coworker3->load([
            "Coworker" => [
                'firstname' => 'Joseph',
                'lastname' => 'Markoff',
                'email' => 'joseph.markoff@example.com',
                'phone' => '79999999997',
                'priority' => Coworker::PRIORITY_HIGH,
                'category_id' => 1,
                'coworkerProperties' => [
                    [
                        'property_id' => 2,
                        'value' => 1,
                        'dimension_id' => 1
                    ]
                ]
            ]
        ]));
        $coworker->save();
        $coworker2->save();
        $coworker3->save();
        verify(count(Coworker::find()->all()))->equals(3);
        $order->assignCoworker($coworker);
        verify($order->checkSuccessfully())->false();
        $order->assignCoworker($coworker2);
        verify($order->checkSuccessfully())->false();
        $order->assignCoworker($coworker3);
        verify($order->checkSuccessfully())->true();
        verify(count($order->filters))->equals(1);
        $filter = $order->filters[0];

        verify($filter->count)->equals(3);
        verify(count($order->coworkers))->equals(3);
    }
}