<?php

namespace app\commands;

use app\models\Building;
use app\models\Category;
use app\models\Dimension;
use app\models\Order;
use app\models\Property;
use app\models\Telegram;
use app\models\User;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
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
        ], [
            'id' => 2,
            'title' => 'Разнорабочий',
            'type' => 1,
            'parent_id' => null,
            'properties' => [
                1, 2
            ]
        ]
    ];

    protected $faker;
    public function init()
    {
        parent::init();
        $this->faker = Factory::create();
    }

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
        return true;
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

        echo Json::encode($order->notify()) . "\n";
    }

    public function actionSetWebhook()
    {
        echo Json::encode(Telegram::setWebhook());
    }

    public function actionMessage($user_id)
    {
        $user = User::findOne($user_id);
        $message = new ExpoMessage([
            'title' => "Title",
            'body' => "Body",
        ]);
        $expo = new Expo();
        $expo->send($message)->to($user->profile->device_id)->push();
    }

    public function actionFixtureUsers()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            for ($i = 1; $i <= 5; $i++) {
                if (empty($temp = $this->generateUser(
                    $this->faker->username,
                    $this->faker->email,
                    '123456',
                    User::STATUS_ACTIVE,
                    $this->faker->numberBetween(0, 2),
                ))) {
                    echo \Yii::t('app', "Fixture user #{$temp->id} {$temp->username} created")."\n";
                }
            }
            for ($i = 1; $i <= 10; $i++) {
                if (empty($temp = $this->generateUser(
                    $this->faker->username,
                    $this->faker->email,
                    '123456',
                    User::STATUS_ACTIVE,
                    $this->faker->numberBetween(0, 2),
                ))) {
                    echo \Yii::t('app', "Fixture user #{$temp->id} {$temp->username} created")."\n";
                }
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            \Yii::error($e->getMessage());
            throw $e;
        }

    }

    protected function generateUser($username, $email, $password, $status, $priority)
    {
        $user = new User([
            'username' => $username,
            'email' => $email,
            'password_hash' => \Yii::$app->security->generatePasswordHash($password),
            'auth_key' => \Yii::$app->security->generateRandomString(),
            'access_token' => \Yii::$app->security->generateRandomString(),
            'status' => $status,
            'referrer_id' => 1,
            'priority_level' => $priorities[$priority],
        ]);
        if( $user->save()) {
            return $user;
        } else {
            \Yii::error($user->errors);
            return null;
        }
    }

    protected function createOrder($date)
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {

        }catch (\Exception $e){
            $transaction->rollBack();
            \Yii::error($e->getMessage());
            return null;
        }
    }
}