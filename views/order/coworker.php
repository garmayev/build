<?php

use app\models\Building;
use app\models\Order;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\View;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var View $this
 * @var Order $model
 */

\app\assets\ReactAsset::register($this);

$form = ActiveForm::begin();

$this->title = \Yii::t('app', 'Order coworker');

$this->params['breadcrumbs'][] = [
    'label' => \Yii::t('app', 'Orders'),
    'url' => ['/order/index']
];
$this->params['breadcrumbs'][] = $this->title;

echo $form->field($model, 'status')->dropDownList($model->statusList);

echo $form->field($model, 'type')->hiddenInput(['value' => Order::TYPE_COWORKER])->label(false);

echo $form->field($model, 'building_id')->dropDownList(
    ArrayHelper::map(
        Building::find()->where(['user_id' => \Yii::$app->user->id])->all(),
        'id',
        'title',
    )
)->label(\Yii::t('app', 'Select building'));

echo $form->field($model, 'date');

echo $form->field($model, 'orderFilters')->hiddenInput(['value' => Json::encode($model->orderFilters)])->label(false);

echo Html::tag('div', '', ['id' => 'dynamicTable']);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success']);

ActiveForm::end();

$this->registerJsFile('/js/react/DynamicTable.js', ['position' => View::POS_HEAD, 'type' => 'text/babel']);
$t = Json::encode([
    'submit.title' => \Yii::t('app', 'Add Filter'),
    'modal.close' => \Yii::t('app', 'Close'),
    'modal.save' => \Yii::t('app', 'Save'),
    'header.category' => \Yii::t('app', 'Category'),
    'header.count' => \Yii::t('app', 'Count'),
    'header.requirement' => \Yii::t('app', 'Requirement'),
    'modal.addRequirement' => \Yii::t('app', 'Add Requirement'),
    'modal.type.less' => \Yii::t('app', 'Less'),
    'modal.type.more' => \Yii::t('app', 'More'),
    'modal.type.equal' => \Yii::t('app', 'Equal'),
    'modal.type.not-equal' => \Yii::t('app', 'Not Equal'),
]);
$this->registerJsVar('filters', $model->filters); ;
$script = <<<JS
    yii.t = $t ;
    const root = ReactDOM.createRoot(document.getElementById('dynamicTable'));
    
    root.render(
        <DynamicTable 
            data={filters} 
            tableHeader={[{
                header: yii.t['header.category'],
                key: 'category.title',
                inputName: '[category_id]',
                inputValue: 'category.id'
            }, {
                header: yii.t['header.count'],
                key: 'count',
                inputName: '[count]',
                inputValue: 'count'
            }, {
                
                header: yii.t['header.requirement'],
                key: {
                    header: 'requirements',
                    subkey: ['property.title', 'type', 'value', 'dimension.short'],
                    values: [{
                        inputName: '[property][id]',
                        inputValue: 'property.id'
                    }, {
                        inputName: '[type]',
                        inputValue: 'type'
                    }, {
                        inputName: '[value]',
                        inputValue: 'value'
                    }, {
                        inputName: '[dimension][id]',
                        inputValue: 'dimension.id'
                    }]
                }
            }]} 
            dataUrl={'/api/order/detail?id={$model->id}'} 
            categoryUrl={'/api/category/index?type=1'} 
            propertyUrl={'/api/property/by-category'}
            formName={'Order[filters]'}
        />
    )
JS;




echo Html::script($script, ['type' => 'text/babel']);
