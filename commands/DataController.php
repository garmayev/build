<?php

namespace app\commands;

use app\models\Building;
use app\models\Category;
use app\models\Coworker;
use app\models\Location;
use app\models\Order;
use app\models\Profile;
use app\models\Telegram;
use app\models\User;
use Faker\Factory;
use yii\console\Controller;
use yii\helpers\Json;

class DataController extends Controller
{
    protected $locations = [
        [
            'address' => 'г.Улан-Удэ, ул.Революции 1905 г., 42',
            'latitude' => 51.838814,
            'longitude' => 107.590673,
        ], [
            'address' => 'г.Улан-Удэ, 111-й микрорайон, 3/1',
            'latitude' => 51.771460,
            'longitude' => 107.585060,
        ], [
            'address' => 'г.Улфн-Удэ, ул.Революции 1905 г., 102',
            'latitude' => 51.850472,
            'longitude' => 107.569824,
        ]
    ];
    protected $categories = [
        [
            'id' => 1,
            'title' => 'Монтажник',
            'type' => 1,
            'parent_id' => null,
        ], [
            'id' => 2,
            'title' => 'Штукатурщик',
            'type' => 1,
            'parent_id' => 1,
        ], [
            'id' => 3,
            'title' => 'Сварщик',
            'type' => 1,
            'parent_id' => 1,
        ], [
            'id' => 4,
            'title' => 'Шпатлевщик',
            'type' => 1,
            'parent_id' => 1,
        ],
    ];
    public function actionDemo()
    {
        $faker = Factory::create('ru-RU');

        foreach ($this->locations as $location) {
            $l = new Location($location);
            $l->save();
        }
        for ($i = 0; $i < 2; $i++) {
            $building = new Building([
                'title' => $faker->address,
                'location_id' => $i
            ]);
            $building->save();
        }
        foreach ($this->categories as $category) {
            $category = new Category($category);
            $category->save();
        }
        for ($i = 0; $i < 16; $i++) {
            $user = new User([
                'username' => $faker->userName,
                'email' => $faker->email,
                'password' => $faker->password,
                'auth_key' => \Yii::$app->security->generateRandomString(),
                'access_token' => \Yii::$app->security->generateRandomString(),
            ]);
            $user->save();
            $coworker = new Coworker([
                'user_id' => $user->id,
                'category_id' => 1
            ]);
            $coworker->save();
        }
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