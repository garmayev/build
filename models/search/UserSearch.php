<?php

namespace app\models\search;

use app\models\User;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends Model
{
    public $username;
    public $name;
    public $email;
    public $surname;
    public $family;

    public function rules()
    {
        return [
            [['username', 'name', 'surname', 'family', 'email'], 'string'],
        ];
    }

    public function search($params, $roleName = 'employee')
    {
        $userIds = array_merge(\Yii::$app->authManager->getUserIdsByRole('employee'), \Yii::$app->authManager->getUserIdsByRole('director'), \Yii::$app->authManager->getUserIdsByRole('admin'));
        $query = User::find()->joinWith(['profile'])->where(['user.id' => $userIds]);

        if ($this->load($params) && $this->validate()) {
            $query->andFilterWhere(['like', 'username', $this->username]);
            $query->andFilterWhere(['like', 'profile.name', $this->name]);
            $query->andFilterWhere(['like', 'profile.surname', $this->surname]);
            $query->andFilterWhere(['like', 'profile.family', $this->family]);
            $query->andFilterWhere(['like', 'email', $this->email]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }
}