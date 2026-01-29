<?php

use app\models\Building;
use app\models\Order;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\View;
use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/**
 * @var View $this
 * @var Order $model
 */

//\app\assets\ReactAsset::register($this);

\app\assets\ScheduleAsset::register($this);

// Собираем данные требований из модели
$requirementsData = [];
if (!empty($model->requirements)) {
    foreach ($model->requirements as $requirement) {
        $requirementsData[] = [
            'category_id' => $requirement->category_id ?? null,
            'property_id' => $requirement->property_id ?? null,
            'count' => $requirement->count ?? null,
            'type' => $requirement->type ?? null,
            'value' => $requirement->value ?? null,
            'dimension_id' => $requirement->dimension_id ?? null,
            'category_name' => $requirement->category->title ?? null,
            'property_name' => $requirement->property->title ?? null,
            'dimension_name' => $requirement->dimension->title ?? null,
        ];
    }
}

// Создаем массив переводов для JS
$jsTranslations = [
    'noRequirements' => \Yii::t('app', 'No requirements added'),
    'category' => \Yii::t('app', 'Categories'),
    'count' => \Yii::t('app', 'Count'),
    'property' => \Yii::t('app', 'Property'),
    'type' => \Yii::t('app', 'Type'),
    'value' => \Yii::t('app', 'Value'),
    'dimension' => \Yii::t('app', 'Dimension'),
    'actions' => \Yii::t('app', 'Actions'),
    'confirmDelete' => \Yii::t('app', 'Are you sure you want to delete this item?'),
    'edit' => \Yii::t('app', 'Edit'),
    'delete' => \Yii::t('app', 'Delete'),
    'save' => \Yii::t('app', 'Save'),
    'close' => \Yii::t('app', 'Close'),
];

// Регистрируем переменные для JS
$this->registerJsVar('initialRequirements', $requirementsData);
$this->registerJsVar('translations', $jsTranslations);
$this->registerJsVar('types', \app\models\Requirement::getTypes());

$form = ActiveForm::begin([
    'id' => 'order-form',
    'enableClientValidation' => true,
    'enableAjaxValidation' => true,
    'validationUrl' => ['order/validate'],
    'options' => [
        'enctype' => 'multipart/form-data',
    ]
]);

echo Html::beginTag('div', ['class' => 'row']);

$this->title = \Yii::t('app', 'Order works');

$this->params['breadcrumbs'][] = [
    'label' => \Yii::t('app', 'Orders'),
    'url' => ['/order/index']
];

$this->registerJsVar('token', \Yii::$app->user->identity->access_token);

$this->params['breadcrumbs'][] = $this->title;

echo $form->field($model, 'status')->hiddenInput(['value' => Order::STATUS_NEW])->label(false);

echo $form->field($model, 'type', ['options' => ['class' => 'mx-0 my-0']])->hiddenInput(['value' => Order::TYPE_COWORKER])->label(false);

echo $form->field($model, 'building_id')->dropDownList(
    ArrayHelper::map(
        Building::find()->where(['user_id' => \Yii::$app->user->id])->all(),
        'id',
        'title',
    )
)->label(\Yii::t('app', 'Select building'));

echo $form->field($model, 'title')->textInput();

echo $form->field($model, 'date', ['options' => ['class' => 'mx-0 my-0']])->hiddenInput(['value' => time()])->label(false);

if (count($model->attachments)) {
    echo \yii\grid\GridView::widget([
        'dataProvider' => new \yii\data\ArrayDataProvider([
            'allModels' => $model->attachments,
        ]),
        'summary' => false,
        'tableOptions' => [
            'class' => 'table table-striped',
        ],
        'columns' => [
            [
                'attribute' => 'url',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::a($model->url, $model->url);
                }
            ]
        ]
    ]);
}

echo $form->field($model, 'mode')->dropDownList($model->modes)->label(\Yii::t('app', 'Mode'));

echo $form->field($model, 'price', [
    'inputTemplate' => '
        <div class="input-group">
            <span class="input-group-text">₽</span>
            {input}
        </div>',
])
    ->widget(\yii\widgets\MaskedInput::class, [
        'options' => [
            // 1. Применяем стандартный класс Bootstrap 5
            'class' => 'form-control',
            // 2. Выравнивание текста по левому краю через CSS
            'style' => 'text-align: left;',
            'placeholder' => '0,00',
        ],
        'clientOptions' => [
            'alias' => 'numeric', // Используем числовой тип данных
            'groupSeparator' => ' ', // Разделитель тысяч (пробел)
            'digits' => 2,           // Количество знаков после запятой
            'digitsOptional' => true, // Десятичные знаки не обязательны при вводе
            'radixPoint' => ',',     // Десятичный разделитель (запятая)
            'autoGroup' => true,     // Автоматически разделять тысячи
            'removeMaskOnSubmit' => true, // Удалять маску (символ ₽ и пробелы) перед отправкой на сервер
            'rightAlign' => false,
        ],
    ])
    ->label(\Yii::t('app', 'Price'));

echo $form->field($model, 'start_datetime', [
    'inputTemplate' => "{input}\n<div id='start-date' class='form-control'></div>",
    'template' => "{label}\n{input}\n{error}"
])->textInput(['style' => 'display: none;', 'value' => date('Y-m-d 00:00:00')]);

echo $form->field($model, 'finish_datetime', [
    'inputTemplate' => "{input}\n<div id='finish-date' class='form-control'></div>",
    'template' => "{label}\n{input}\n{error}"
])->textInput(['style' => 'display: none;']);

echo $form->field($model, 'files[]')->fileInput([
    'multiple' => true,
])->label(\Yii::t('app', 'Attachments'));

echo $form->field($model, 'comment')->textarea(['rows' => 6]);

echo Html::endTag('div');

echo Html::tag('div', '', ['id' => 'requirement-container', 'class mb-3 mr-3']);

echo Html::a(\Yii::t('app', 'Add Requirements'), '#', ['class' => 'btn btn-primary mb-3 mr-3 showModalButton', 'value' => Url::to(['/order/create-requirement'])]);

echo Html::submitButton(\Yii::t('app', 'Save'), ['class' => 'btn btn-success mb-3']);

ActiveForm::end();

echo '<div class="modal fade" id="requirement" tabindex="-1" aria-labelledby="requirementLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="requirementLabel">Требования</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="content">
                <div id="modal-content"></div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-success save-modal" data-bs-dismiss="modal">' . \Yii::t('app', 'Save') . '</button>
                <button type="button" class="btn btn-secondary close-modal" data-bs-dismiss="modal">' . \Yii::t('app', 'Close') . '</button>
            </div>
        </div>
    </div>
</div>';

$this->registerJsVar('start_date_value', $model->start_datetime);
$this->registerJsVar('finish_date_value', $model->finish_datetime);
$this->registerJs(<<<JS
$(document).ready(function() {
    // Используем переводы из PHP
    const t = translations;
    // Получаем форму
    const form = $('#order-form');
    let startDate, endDate;
    const startDateContainer = $('#order-start_datetime');
    const finishDateContainer = $('#order-finish_datetime');
    form.on('submit', function(e) {
        if ($(e.target).hasClass('save-modal') || $(e.target).hasClass('close-modal') || $(e.target).hasClass('btn-close')) {
            e.preventDefault();
            return false;
        }
    });
    // Инициализация DateSelector
    endDate = new DateSelector('#finish-date', {
        initialDate: finish_date_value ? new Date(finish_date_value) : new Date(),
        startDate: new Date(),
        showValidationErrors: false,
        errorMessage: finishDateContainer.closest('.form-group').hasClass('has-error') ? 
                     finishDateContainer.closest('.form-group').find('.help-block').text() : null,
        useLeftRightButtons: true,
        calendarOptions: {
            allowPastDates: true
        },
        onChange: (date) => {
            finishDateContainer.val(DateUtils.formatDate(date, "YYYY-MM-DD") + " 23:59:59")
            form.yiiActiveForm('validateAttribute', 'order-finish_datetime');
        }
    })
        
    // Инициализация DateSelector с поддержкой ошибок
    startDate = new DateSelector('#start-date', {
        initialDate: start_date_value ? new Date(start_date_value) : new Date(),
        useLeftRightButtons: true,
        showValidationErrors: false,
        calendarOptions: {
            allowPastDates: true
        },
        // Получаем ошибку из Yii2 валидации
        errorMessage: startDateContainer.closest('.form-group').hasClass('has-error') ? 
                     startDateContainer.closest('.form-group').find('.help-block').text() : null,
        onChange: (date) => {
            const dateStr = DateUtils.formatDate(date, "YYYY-MM-DD") + " 00:00:00";
            startDateContainer.val(dateStr);
            
            // Очищаем ошибку при изменении
            if (endDate && date > endDate.getDate()) {
                endDate.setDate(new Date(date));
            }
            
            // Триггерим валидацию Yii2
            form.yiiActiveForm('validateAttribute', 'order-start_datetime');
        }
    });
    
    // Отслеживаем ошибки валидации Yii2
    form.on('afterValidateAttribute', function(event, attribute, messages) {
        // Обработка ошибок для start_datetime
        if (attribute.id === 'order-start_datetime') {
            if (messages.length > 0) {
                startDate.setError(messages[0]);
                $("#start-date").addClass("is-invalid").removeClass('is-valid');
            } else {
                startDate.clearError();
                $("#start-date").removeClass("is-invalid").addClass('is-valid');
            }
        }
        
        // Обработка ошибок для finish_datetime
        if (attribute.id === 'order-finish_datetime') {
            if (messages.length > 0) {
                endDate.setError(messages[0]);
                $("#finish-date").addClass("is-invalid").removeClass('is-valid');
            } else {
                endDate.clearError();
                $("#finish-date").removeClass("is-invalid").addClass('is-valid');
            }
        }
        
        if (attribute.id === 'order-price') {
            if (messages.length > 0) {
                $(attribute.input).addClass("is-invalid").removeClass('is-valid');
            } else {
                $(attribute.input).removeClass("is-invalid").addClass('is-valid');
            }
        }
    });
    
    // Добавьте обработчик для валидации всей формы, чтобы обновить оба поля:
    form.on('afterValidate', function(event, messages, errorAttributes) {
        // Проверяем, есть ли ошибки в связанных полях
        const hasStartError = errorAttributes.some(attr => attr.name === 'start_datetime');
        const hasFinishError = errorAttributes.some(attr => attr.name === 'finish_datetime');
        
        // Если есть ошибка в одном из полей дат, можем добавить дополнительную логику
        if (hasStartError || hasFinishError) {
            // Например, можно подсветить оба поля, если ошибка связана с их взаимосвязью
            const startMessages = messages['order-start_datetime'] || [];
            const finishMessages = messages['order-finish_datetime'] || [];
            
            // Проверяем, связаны ли ошибки с датами
            const dateErrors = startMessages.concat(finishMessages).filter(msg => 
                msg.includes('дата') || msg.includes('Дата') || msg.includes('date') || msg.includes('Date')
            );
            
            if (dateErrors.length > 0) {
                // Можно добавить дополнительную подсветку или логику
            }
        }
    });

    const modalWindow = $('#requirement');
    
    // Инициализация Table с данными из модели (если есть)
    const Table = {
        index: 0,
        data: initialRequirements || [],
        
        // Добавление/обновление записи
        save: function(data, index = null) {
            if (index !== null && index >= 0 && index < this.data.length) {
                // Редактирование существующей записи
                this.data[index] = data;
            } else {
                // Добавление новой записи
                this.data.push({...data, id: Date.now() + Math.random()});
            }
            this.render();
        },
        
        // Удаление записи
        remove: function(index) {
            if (index >= 0 && index < this.data.length) {
                this.data.splice(index, 1);
                this.render();
            }
        },
        
        // Редактирование записи
        edit: function(index) {
            if (index >= 0 && index < this.data.length) {
                const item = this.data[index];
                
                // Открываем модальное окно
                modalWindow.modal('show');
                
                const modalContent = modalWindow.find('#modal-content');
                modalContent.html('<div class="text-center p-5"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>');
                
                // Передаем данные через GET для простоты
                const baseUrl = $('.showModalButton').attr('value');
                const params = new URLSearchParams();
                if (item.category_id) params.append('category_id', item.category_id);
                if (item.count) params.append('count', item.count);
                if (item.property_id) params.append('property_id', item.property_id);
                if (item.type) params.append('type', item.type);
                if (item.value) params.append('value', item.value);
                if (item.dimension_id) params.append('dimension_id', item.dimension_id);
                
                const url = baseUrl + (params.toString() ? '?' + params.toString() : '');
                console.log(url)
                modalContent.load(url, function(response, status, xhr) {
                    if (status === "error") {
                        modalContent.html('<div class="alert alert-danger">Ошибка загрузки формы</div>');
                        return;
                    }
                    
                    // Инициализируем виджеты после загрузки контента
                    setTimeout(function() {
                        if (typeof window.initRequirementWidgets === 'function') {
                            // window.initRequirementWidgets();
                        } else {
                            // Альтернативный способ инициализации
                            // reinitDepdropWidgets();
                        }
                    }, 100);
                    
                    // Добавляем скрытое поле с индексом
                    modalContent.append(`<input type="hidden" id="edit-index" value="\${index}">`);
                });
            }
        },
        
        // Рендеринг таблицы
        render: function() {
            const container = $('#requirement-container');
            
            if (this.data.length === 0) {
                container.html('<div class="alert alert-info">' + 
                    t.noRequirements + 
                    '</div>');
                return;
            }
            
            let html = '<table class="table table-striped table-hover mt-3">';
            html += '<thead><tr>' +
                '<th>' + t.category + '</th>' +
                '<th>' + t.count +
                '<th>' + t.property + '</th>' +
                '<th>' + t.type + '</th>' +
                '<th>' + t.value + '</th>' +
                '<th>' + t.dimension + '</th>' +
                '<th></th>' +
                '</tr></thead>';
            html += '<tbody>';
            
            // Сохраняем контекст this для использования внутри forEach
            const self = this;
            this.data.forEach((item, index) => {
                // Определяем названия по ID (эти данные нужно будет получать из сервера)
                const categoryName = item.category_name || item.category_id;
                const count = item.count || 1;
                const propertyName = item.property_name || item.property_id;
                const typeName = item.type_name || item.type;
                const dimensionName = item.dimension_name || item.dimension_id;
                
                html += '<tr>';
                html += `<td>\${categoryName}</td>`;
                html += `<td>\${count}</td>`;
                html += `<td>\${propertyName}</td>`;
                html += `<td>\${types[item.type]}</td>`;
                html += `<td>\${item.value}</td>`;
                html += `<td>\${dimensionName}</td>`;
                html += `<td>
                    <a class="edit-requirement" href="#" data-index="\${index}" title="\${t.edit}">
                        <i class="fas fa-pencil"></i>
                    </a>
                    <a class="delete-requirement" href="#" data-index="\${index}" title="\${t.delete}">
                        <i class="fas fa-trash"></i>
                    </a>
                </td>`;
                html += '</tr>';
                console.log(item.count);
                // Добавляем скрытые поля для отправки на сервер
                html += `<input type="hidden" name="Order[requirements][\${index}][category_id]" value="\${item.category_id || ''}">`;
                html += `<input type="hidden" name="Order[requirements][\${index}][count]" value="\${item.count || ''}">`;
                html += `<input type="hidden" name="Order[requirements][\${index}][property_id]" value="\${item.property_id || ''}">`;
                html += `<input type="hidden" name="Order[requirements][\${index}][type]" value="\${item.type || ''}">`;
                html += `<input type="hidden" name="Order[requirements][\${index}][value]" value="\${item.value || ''}">`;
                html += `<input type="hidden" name="Order[requirements][\${index}][dimension_id]" value="\${item.dimension_id || ''}">`;
            });
            
            html += '</tbody></table>';
            container.html(html);
            
            // Назначаем обработчики событий для кнопок
            $('.edit-requirement').click(function(event) {
                event.preventDefault();
                const index = $(this).data('index');
                Table.edit(index); // Используем Table напрямую, а не this
            });
            
            $('.delete-requirement').click(function(event) {
                event.preventDefault();
                const index = $(this).data('index');
                if (confirm(t.confirmDelete)) {
                    Table.remove(index); // Используем Table напрямую, а не this
                }
            });
        },
        
        // Получение данных для отправки на сервер
        getData: function() {
            return this.data;
        },
        
        // Очистка всех данных
        clear: function() {
            this.data = [];
            this.render();
        }
    };
    
    // Инициализация данных из модели (если они были переданы из PHP)
    if (typeof initialRequirements !== 'undefined' && initialRequirements.length > 0) {
        Table.render();
    }

    // В обработчике change для select режима добавьте валидацию цены:
    $('#order-mode').on('change', function() {
        const startContainer = $(".field-order-start_datetime");
        const finishContainer = $(".field-order-finish_datetime");
        const priceContainer = $('.field-order-price');
        const priceInput = $('#order-price');
        
        switch ($(this).val()) {
            case '0': // MODE_SINGLE_FIXED
                startContainer.removeClass('col-6').addClass('col-12');
                finishContainer.addClass('d-none');
                priceContainer.show();
                // Включаем валидацию цены
                priceInput.removeAttr('disabled');
                break;
                
            case '1': // MODE_LONG_FIXED
                startContainer.addClass('col-6').removeClass('col-12');
                finishContainer.removeClass('d-none').addClass('col-6');
                priceContainer.show();
                priceInput.removeAttr('disabled');
                break;
                
            case '2': // MODE_LONG_DAILY
                startContainer.addClass('col-6').removeClass('col-12');
                finishContainer.removeClass('d-none').addClass('col-6');
                priceContainer.hide();
                break;
        }
        
        form.yiiActiveForm('validate');
    }).trigger('change');
    
    $('#order-price').on('blur', function() {
        $(this).addClass('is-valid')
    })
    
    $('.showModalButton').click(function(e) {
        e.preventDefault();
        modalWindow.modal('show')
            .find('#modal-content')
            .load($(this).attr('value'), function(response, status, xhr) {
                const modalContent = $('#modal-content');
                if (status === "error") {
                    var msg = "Sorry but there was an error: ";
                    modalContent.html(msg + xhr.status + " " + xhr.statusText);
                }

                modalContent.html(response)
                // Update the modal header title
                var title = $('.showModalButton[value="' + $(this).attr('value') + '"]').attr('title');
                $('#modal-header').text(title);
            });
    });
    
    modalWindow.find('.btn-close').click(function(e) {
        e.preventDefault();
        modalWindow.modal('hide');
    });
    modalWindow.find('.close-modal').click(function(e) {
        e.preventDefault();
        modalWindow.modal('hide');
    });
    modalWindow.find('.save-modal').click(function(e) {
        e.preventDefault();

        const formData = {
            category_id: modalWindow.find('#category_id').val(),
            category_name: modalWindow.find('#category_id').find(':selected').text(),
            count: modalWindow.find('#count').val(),
            property_id: modalWindow.find('#property_id').val(),
            property_name: modalWindow.find('#property_id').find(':selected').text(),
            type: modalWindow.find('#type').val(),
            type_name: modalWindow.find('#type').find(':selected').text(),
            value: modalWindow.find('#value').val(),
            dimension_id: modalWindow.find('#dimension_id').val(),
            dimension_name: modalWindow.find('#dimension_id').find(':selected').text()
        };
        
        // Проверяем, редактируем ли мы существующую запись
        const editIndex = modalWindow.find('#edit-index').val();
        
        if (editIndex !== undefined) {
            // Редактирование
            Table.save(formData, parseInt(editIndex));
        } else {
            // Добавление новой записи
            Table.save(formData);
        }
        
        // Закрываем модальное окно
        modalWindow.modal('hide');
    })
    
    Table.render();
});
JS
);
$this->registerCss(<<<CSS
#start-date {
    padding: 0;
}
#finish-date {
    padding: 0;
}
CSS);