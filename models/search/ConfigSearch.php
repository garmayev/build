<?php

namespace app\models\search;

use app\models\Config;
use yii\data\ActiveDataProvider;

class ConfigSearch extends Config
{
    public $configName;
    public $configValue;

    public static function tableName()
    {
        return "{{%config}}";
    }

    public function rules()
    {
        return [
            [['configName', 'configValue'], 'safe'],
        ];
    }

    public function search($params)
    {
        $query = Config::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => [
                    'id',
                    'label',
                    'value',
                ]
            ]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'name' => $this->configName,
        ]);
        $query->andFilterWhere(['like', 'value', $this->configValue]);
        return $dataProvider;
    }
}