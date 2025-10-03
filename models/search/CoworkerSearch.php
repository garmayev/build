<?php

namespace app\models\search;

use app\models\Coworker;
use app\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CoworkerSearch represents the model behind the search form of `app\models\Coworker`.
 * @property int $id
 * @property int $category_id
 * @property string $text
 */
class CoworkerSearch extends Coworker
{
    public string $name = "";
    public string $phone = "";
    public string $email = "";

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['name', 'phone', 'email'], 'string']
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        if (\Yii::$app->user->can('admin')) {
            $query = Coworker::find()->joinWith('profile');
        } else if (\Yii::$app->user->can('director')) {
            $query = Coworker::find()->joinWith('profile')->where(['referrer_id' => \Yii::$app->user->getId()]);
        } else {
            $query = Coworker::find()->joinWith('profile')->where(['id' => \Yii::$app->user->getId()]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ],
                'attributes' => [
                    'id',
//                    'name'
                ],
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'user.id' => $this->id,
        ]);
        $query->andFilterWhere(['or',
            ['like', 'profile.phone', preg_replace('/[\+\-\ \(\)]*/', "", $this->phone)],
            ['like', 'profile.family', $this->name],
            ['like', 'profile.name', $this->name],
            ['like', 'user.username', $this->name],
            ['like', 'user.email', $this->email]]);

        return $dataProvider;
    }

    public function getPhone()
    {
        return $this->profile->phone;
    }

    public function getBirthday()
    {
        return $this->profile->birthday;
    }
}
