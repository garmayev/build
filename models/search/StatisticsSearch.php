<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Coworker;

class StatisticsSearch extends Model
{
    public $year;
    public $month;
    public $name;
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['year', 'month'], 'integer'],
            [['name', 'email'], 'safe'],
        ];
    }

    /**
     * Поиск данных
     */
    public function search($params)
    {
        $query = Coworker::find()->where(['referrer_id' => \Yii::$app->user->id]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 20,
            ],
            'sort' => [
                'defaultOrder' => [
                    'username' => SORT_ASC,
                ],
                'attributes' => [
                    'username',
                    'email',
                    'status',
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // Фильтрация по имени или email
        if (!empty($this->name)) {
            $query->andWhere(['or',
                ['like', 'username', $this->name],
                ['like', 'profile.first_name', $this->name],
                ['like', 'profile.last_name', $this->name],
            ]);
        }

        if (!empty($this->email)) {
            $query->andWhere(['like', 'email', $this->email]);
        }

        return $dataProvider;
    }
}