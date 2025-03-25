<?php

namespace app\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Coworker;

/**
 * CoworkerSearch represents the model behind the search form of `app\models\Coworker`.
 * @property int $id
 * @property int $category_id
 * @property string $text
 */
class CoworkerSearch extends Coworker
{
    public $text = "";
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'category_id'], 'integer'],
            [['text'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
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
        $query = Coworker::find()->where(['created_by' => \Yii::$app->user->identity->getId()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

//        $this->load($params);
//
        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'category_id' => $this->category_id,
        ]);
        $query->andFilterWhere(['or', ['like', 'phone', $this->text], ['like', 'firstname', $this->text], ['like', 'lastname', $this->text], ['like', 'email', $this->text]]);

//        \Yii::error($query->createCommand()->getRawSql());

        return $dataProvider;
    }
}
