<?php

namespace app\commands;

use app\models\Building;
use app\models\Category;
use app\models\Coworker;
use app\models\Dimension;
use app\models\Location;
use app\models\Order;
use app\models\Property;
use app\models\Telegram;
use app\models\User;
use Faker\Factory;
use yii\console\Controller;
use yii\helpers\Json;

class DataController extends Controller
{
    protected $building = [
        [
            'title' => "Объект 1",
            'user_id' => 1,
            'location' => [
                'address' => 'г.Улан-Удэ, ул.Революции 1905 г., 42',
                'latitude' => 51.838814,
                'longitude' => 107.590673,
                'user_id' => 1
            ]
        ]
    ];
    protected $dimension = [
        [
            'title' => 'Лет',
            'multiplier' => 1,
            'short' => 'л',
        ]
    ];
    protected $properties = [
        [
            'title' => 'Возраст',
            'dimensions' => [
                1
            ]
        ], [
            'title' => 'Опыт',
            'dimensions' => [
                1
            ]
        ]
    ];
    protected $categories = [
        [
            'id' => 1,
            'title' => 'Монтажник',
            'type' => 1,
            'parent_id' => null,
            'properties' => [
                1, 2
            ]
        ],
    ];
    public function actionDemo()
    {
        if ($this->createUser()) {
            foreach ($this->building as $item) {
                $build = new Building();
                $build->load(["Building" => $item]);
                $build->save();
            }
            foreach ($this->dimension as $item) {
                $dimension = new Dimension($item);
                $dimension->save();
            }
            foreach ($this->properties as $item) {
                $property = new Property($item);
                $property->save();
            }
            foreach ($this->categories as $item) {
                $category = new Category($item);
                $category->save();
            }
        }
    }

    protected function createUser(): bool
    {
        $user = new User([
            'username' => 'garmayev',
            'email' => 'garmayev@yandex.ru',
            'password_hash' => \Yii::$app->security->generatePasswordHash('rhbcnbyfgfrekjdf'),
            'auth_key' => \Yii::$app->security->generateRandomString(),
            'access_token' => \Yii::$app->security->generateRandomString(),
            'status' => User::STATUS_ACTIVE,
        ]);
        return $user->save();
    }

    public function actionFilter($order_id)
    {
        $order = Order::findOne($order_id);

        echo Json::encode($order->notify())."\n";
    }

    public function actionSetWebhook()
    {
        echo Json::encode(Telegram::setWebhook());
    }
}