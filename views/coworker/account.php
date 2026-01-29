<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\forms\UserRegisterForm $registerForm */

$this->title = Yii::t('app', 'Create Coworker');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Coworkers'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Подготавливаем данные свойств для JS
$propertiesData = [];

foreach ($registerForm->properties as $property) {
    $propertiesData[] = [
        'category_id' => $property->category_id ?? null,
        'category_name' => isset($property->category) ? $property->category->title : null,
        'property_id' => $property->property_id ?? null,
        'dimension_id' => $property->dimension_id ?? null,
        'value' => $property->value ?? null,
        'property_name' => $property->property->title ?? null,
        'dimension_name' => isset($property->dimension) ? $property->dimension->title : null,
    ];
}

// Получаем все доступные свойства
$availableProperties = \app\models\Property::find()->with('dimensions')->all();

// Создаем массив переводов для JS
$jsTranslations = [
    'category' => \Yii::t('app', 'Category'),
    'noProperties' => \Yii::t('app', 'No properties added'),
    'property' => \Yii::t('app', 'Property'),
    'dimension' => \Yii::t('app', 'Dimension'),
    'value' => \Yii::t('app', 'Value'),
    'actions' => \Yii::t('app', 'Actions'),
    'confirmDelete' => \Yii::t('app', 'Are you sure you want to delete this item?'),
    'edit' => \Yii::t('app', 'Edit'),
    'delete' => \Yii::t('app', 'Delete'),
    'save' => \Yii::t('app', 'Save'),
    'close' => \Yii::t('app', 'Close'),
    'createUser' => \Yii::t('app', 'Create User'),
    'updateUser' => \Yii::t('app', 'Update User'),
];

// Регистрируем переменные для JS
$this->registerJsVar('initialProperties', $propertiesData ?? []);
$this->registerJsVar('translations', $jsTranslations ?? []);

$form = ActiveForm::begin([
    'id' => 'coworker-form',
    'enableClientValidation' => true,
    'options' => [
        'enctype' => 'multipart/form-data',
    ]
]);
?>
    <div class="row">
        <?php
        echo $form->field($registerForm, 'username', ['options' => ['class' => 'col-6']])
            ->textInput();

        echo $form->field($registerForm, 'email', ['options' => ['class' => 'col-6']])
            ->textInput([
                'type' => 'email',
                'disabled' => !$registerForm->isNewRecord,
            ]);

        echo $form->field($registerForm, 'priority', ['options' => ['class' => 'col-12']])
            ->dropDownList(\app\models\Coworker::getPriorityList());

        echo $form->field($registerForm, 'family', ['options' => ['class' => 'col-4']])
            ->textInput();

        echo $form->field($registerForm, 'name', ['options' => ['class' => 'col-4']])
            ->textInput();

        echo $form->field($registerForm, 'surname', ['options' => ['class' => 'col-4']])
            ->textInput();

        echo $form->field($registerForm, 'birthday', ['options' => ['class' => 'my-0 py-0']])->hiddenInput()->label(false);
        ?>
        <div class="col-6 field-userregisterform-birthday">
            <label for="control-birthday" class="form-label"><?= Yii::t('app', 'Birthday') ?></label>
            <div id="control-birthday"></div>
            <div class="help-block"></div>
        </div>
        <?php
        echo $form->field($registerForm, 'phone', ['options' => ['class' => 'col-6']])->widget(\yii\widgets\MaskedInput::class, [
            'mask' => '+9 (999) 999-99-99'
        ]);
        ?>
    </div>

    <!-- Секция свойств сотрудника (только для редактирования) -->
    <div id="properties-container"></div>

    <button type="button" class="btn btn-primary mr-3 showPropertyModalButton"
            data-url="<?= Url::to(['/coworker/add-property']) ?>">
        <?= Yii::t('app', 'Add Property') ?>
    </button>

<?php
echo Html::submitButton(
    \Yii::t('app', 'Save'),
    ['class' => 'btn btn-success']
);

ActiveForm::end();

// Модальное окно для добавления/редактирования свойств (только для редактирования)
echo '<div class="modal fade" id="propertyModal" tabindex="-1" aria-labelledby="propertyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="propertyModalLabel">' . Yii::t('app', 'Property') . '</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="modal-property-content"></div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-success save-property-modal">' . Yii::t('app', 'Save') . '</button>
                    <button type="button" class="btn btn-secondary close-property-modal" data-bs-dismiss="modal">' . Yii::t('app', 'Close') . '</button>
                </div>
            </div>
        </div>
    </div>';

$js = <<<JS
$(document).ready(function() {
    const t = translations;
    const form = $('#coworker-form');

    // Для новых пользователей не показываем календарь и свойства
    const propertyModal = $('#propertyModal');
        
    // Таблица свойств (аналог требований в заказе)
    const PropertyTable = {
        data: initialProperties || [],
            
        // Сохранить свойство
        save: function(propertyData, index = null) {
            if (index !== null && index >= 0 && index < this.data.length) {
                // Редактирование существующего свойства
                this.data[index] = propertyData;
            } else {
                // Добавление нового свойства
                this.data.push({
                    ...propertyData,
                    id: Date.now() + Math.random()
                });
            }
            this.render();
        },
            
        // Удалить свойство
        remove: function(index) {
            if (index >= 0 && index < this.data.length) {
                this.data.splice(index, 1);
                this.render();
            }
        },
            
        // Редактировать свойство
        edit: function(index) {
            if (index >= 0 && index < this.data.length) {
                const property = this.data[index];
                    
                propertyModal.modal('show');
                const modalContent = propertyModal.find('#modal-property-content');
                modalContent.html('<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                    
                const baseUrl = $('.showPropertyModalButton').data('url');
                const params = new URLSearchParams();
                
                if (property.property_id) params.append('category_id', property.category_id);
                if (property.property_id) params.append('property_id', property.property_id);
                if (property.dimension_id) params.append('dimension_id', property.dimension_id);
                if (property.value) params.append('value', property.value);
                    
                const url = baseUrl + (params.toString() ? '?' + params.toString() : '');
                    
                $.get(url, function(response) {
                    modalContent.html(response);
                        
                    // Добавляем скрытое поле с индексом для редактирования
                    modalContent.append('<input type="hidden" id="edit-property-index" value="' + index + '">');
                }).fail(function() {
                    modalContent.html('<div class="alert alert-danger">Ошибка загрузки формы</div>');
                });
            }
        },
            
        // Отобразить таблицу
        render: function() {
            console.log(this.data)
            const container = $('#properties-container');
                
            if (this.data.length === 0) {
                container.html('<div class="alert alert-info mt-3">' + t.noProperties + '</div>');
                return;
            }
                
            let html = '<table class="table table-striped table-hover mt-3">';
            html += '<thead><tr>' +
                    '<th class="col-2">' + t.category + '</th>' +
                    '<th class="col-3">' + t.property + '</th>' +
                    '<th class="col-3">' + t.value + '</th>' +
                    '<th class="col-3">' + t.dimension + '</th>' +
                    '<th class="col-1">' + t.actions + '</th>' +
                    '</tr></thead>';
            html += '<tbody>';
                
            this.data.forEach((property, index) => {
                    html += '<tr>';
                    html += '<td>' + (property.category_name || property.category_id) + '</td>';
                    html += '<td>' + (property.property_name || property.property_id) + '</td>';
                    html += '<td>' + property.value + '</td>';
                    html += '<td>' + (property.dimension_name || '-') + '</td>';
                    html += '<td>';
                    html += '<a href="#" class="edit-property me-2" data-index="' + index + '" title="' + t.edit + '">';
                    html += '<i class="fas fa-pencil"></i>';
                    html += '</a>';
                    html += '<a href="#" class="delete-property" data-index="' + index + '" title="' + t.delete + '">';
                    html += '<i class="fas fa-trash"></i>';
                    html += '</a>';
                    html += '</td>';
                    html += '</tr>';
                    
                    // Добавляем скрытые поля для отправки на сервер
                    html += '<input type="hidden" name="UserRegisterForm[properties][' + index + '][category_id]" value="' + (property.category_id || '') + '">';
                    html += '<input type="hidden" name="UserRegisterForm[properties][' + index + '][property_id]" value="' + (property.property_id || '') + '">';
                    html += '<input type="hidden" name="UserRegisterForm[properties][' + index + '][dimension_id]" value="' + (property.dimension_id || '') + '">';
                    html += '<input type="hidden" name="UserRegisterForm[properties][' + index + '][value]" value="' + (property.value || '') + '">';
            });
                
            html += '</tbody></table>';
            container.html(html);
                
            // Назначаем обработчики событий
            $('.edit-property').click(function(e) {
                e.preventDefault();
                const index = $(this).data('index');
                PropertyTable.edit(index);
            });
                
            $('.delete-property').click(function(e) {
                e.preventDefault();
                const index = $(this).data('index');
                if (confirm(t.confirmDelete)) {
                    PropertyTable.remove(index);
                }
            });
        }
    };
        
    // Инициализируем таблицу с данными из модели
    if (initialProperties && initialProperties.length > 0) {
        PropertyTable.render();
    } else {
        PropertyTable.render();
    }
        
    const currentDate = new Date();
        // Инициализация календаря
    const dateSelector = new DateSelector('#control-birthday', {
        useLeftRightButtons: true,
        initialDate: currentDate.setFullYear(currentDate.getFullYear() - 18),
        onChange: (date) => {
            console.log('Выбрана дата:', DateUtils.formatDate(date, 'dd.MM.yyyy'));
            $("#userregisterform-birthday").val(DateUtils.formatDate(date, 'YYYY-MM-dd')).trigger("change");
        },
        calendarOptions: {
            showOtherMonthsDays: true,
            showNavigation: true,
            showYearSelector: true,
            allowPastDates: true,
            allowFutureDates: false,
            enableMonthNavigation: true,
            enableYearNavigation: true,
            yearNavigationRange: 80,
            locale: 'ru-RU',
            startWeekOnMonday: true,
            onYearSelect: (year) => {
                console.log('Выбран год рождения:', year);
            }
        }
    });
        
    // Открытие модального окна для добавления свойства
    $('.showPropertyModalButton').click(function(e) {
        e.preventDefault();
        propertyModal.modal('show');
        const modalContent = propertyModal.find('#modal-property-content');
        modalContent.html('<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
            
        $.get($(this).data('url'), function(response) {
            modalContent.html(response);
        }).fail(function() {
            modalContent.html('<div class="alert alert-danger">Ошибка загрузки формы</div>');
        });
    });
        
    // Закрытие модального окна
    propertyModal.find('.btn-close, .close-property-modal').click(function(e) {
        propertyModal.find('#property-form')[0].reset();
        propertyModal.modal('hide');
    });
        
        // Сохранение свойства из модального окна
    propertyModal.find('.save-property-modal').click(function(e) {
        e.preventDefault();
            
        console.log(propertyModal)
        
        const modalContent = propertyModal.find('#modal-property-content');
        const categoryId = modalContent.find('#userproperty-category_id').val();
        const propertyId = modalContent.find('#userproperty-property_id').val();
        const value = modalContent.find('#userproperty-value').val();
        const dimensionId = modalContent.find('#userproperty-dimension_id').val();
        
        // Проверяем, все ли поля заполнены
        if (!categoryId || !propertyId || !value || value.trim() === '' || !dimensionId) {
            alert('Пожалуйста, заполните все поля');
            return;
        }
            
        const formData = {
            category_id: categoryId,
            category_name: modalContent.find('#userproperty-category_id').find(':selected').text(),
            property_id: propertyId,
            property_name: modalContent.find('#userproperty-property_id').find(':selected').text(),
            dimension_id: dimensionId,
            dimension_name: modalContent.find('#userproperty-dimension_id').find(':selected').text() || '',
            value: value
        };
            
        // Проверяем, редактируем ли мы существующее свойство
        const editIndex = modalContent.find('#edit-property-index').val();
            
        if (editIndex !== undefined && editIndex !== '') {
            PropertyTable.save(formData, parseInt(editIndex));
        } else {
            PropertyTable.save(formData);
        }
        propertyModal.find('#property-form')[0].reset();
        propertyModal.modal('hide');
    });
        
        // Обработка закрытия модального окна
    propertyModal.on('hidden.bs.modal', function() {
        console.log('hide')
        propertyModal.find('#property-form')[0].reset();
        propertyModal.find('#modal-property-content').empty();
    });
});
JS;

$this->registerJs($js);