<?php

namespace app\models\search;

use app\models\User;
use floor12\phone\PhoneValidator;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 *
 */
class UserSearch extends Model
{
    public $username;
    public $name;
    public $email;
    public $phone;

    public function rules()
    {
        return [
            [['username', 'name', 'email', 'phone'], 'string'],
        ];
    }

    public function search($params, $roleName = 'employee')
    {
        $userIds = array_merge(\Yii::$app->authManager->getUserIdsByRole('employee'), \Yii::$app->authManager->getUserIdsByRole('director'), \Yii::$app->authManager->getUserIdsByRole('admin'));
        $query = User::find()->joinWith(['profile'])->where(['user.id' => $userIds]);


        if ($this->load($params) && $this->validate()) {
            $this->phone = preg_replace('/[\-\+\ \(\)]*/', '', $this->phone);
            $query->andFilterWhere(['like', 'username', $this->username]);
            $query->andFilterWhere(['or', ['like', 'profile.family', $this->name], ['like', 'name', $this->name], ['like', 'profile.surname', $this->name]]);
            $query->andFilterWhere(['like', 'profile.phone', $this->phone]);
            $query->andFilterWhere(['like', 'email', $this->email]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        return $dataProvider;
    }
}