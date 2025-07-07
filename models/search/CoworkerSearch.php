<?php

namespace app\models\search;

use app\models\User;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CoworkerSearch represents the model behind the search form of `app\models\Coworker`.
 * @property int $id
 * @property int $category_id
 * @property string $text
 */
class CoworkerSearch extends User
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
        $ids = \Yii::$app->authManager->getUserIdsByRole("employee");
        $query = User::find()
            ->joinWith('profile')
            ->joinWith('userProperties')
            ->where(['user.id' => $ids])
            ->andWhere(['or',
                ['user.referrer_id' => \Yii::$app->user->identity->getId()],
                ['user.priority_level' => \app\models\Coworker::PRIORITY_LOW]
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
