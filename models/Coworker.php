<?php

namespace app\models;

use app\models\forms\UserRegisterForm;
use app\models\telegram\TelegramMessage;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use floor12\phone\PhoneValidator;
use Yii;
use yii\behaviors\BlameableBehavior;

/**
 * This is the model class for table "coworker".
 *
 * @property int $id
 * @property string $firstname
 * @property string $lastname
 * @property string $phone
 * @property string $email
 * @property int|null $category_id
 * @property int $priority
 * @property int $user_id
 * @property int $created_by
 *
 * @property Category $category
 * @property CoworkerProperty[] $coworkerProperties
 * @property OrderCoworker[] $orderCoworkers
 * @property Order[] $orders
 * @property Property[] $properties
 * @property Technique[] $techniques
 * @property Attachment[] $attachments
 * @property User $user
 * @property string $name
 */
class Coworker extends \yii\db\ActiveRecord
{
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;
    const TYPE_CUSTOMER = 0;
    const TYPE_WORKER = 1;

    const SCENARIO_COWORKER = 'coworker';
    const SCENARIO_CUSTOMER = 'customer';

    public $files;

    public function behaviors()
    {
        return [
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ]
        ];
    }

    public static function tableName()
    {
        return 'coworker';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['category_id', 'priority', 'notify_date', 'user_id', 'created_by'], 'integer'],
            [['firstname', 'lastname', 'phone', 'email'], 'string', 'max' => 255],
            [['firstname'], 'default', 'value' => ''],
            [['coworkerProperties', 'attachments'], 'safe'],
            [['phone', 'email'], 'unique'],
            [['phone'], PhoneValidator::className()],
            [['firstname', 'lastname', 'phone', 'email'], 'default', 'value' => ''],
            [['priority'], 'default', 'value' => self::PRIORITY_LOW],
            [['type'], 'default', 'value' => self::TYPE_WORKER],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 15],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id'], 'on' => self::SCENARIO_COWORKER],
            [['category_id'], 'default', 'value' => 0, 'on' => self::SCENARIO_COWORKER],
//            [['created_by'], 'default', 'value' => \Yii::$app->user->identity->id],
        ];
    }

    public function scenarios()
    {
        return [
            self::SCENARIO_DEFAULT => ['firstname', 'lastname', 'phone', 'email', 'type', 'category_id', 'priority', 'user_id', 'coworkerProperties'],
            self::SCENARIO_CUSTOMER => ['firstname', 'lastname', 'phone', 'email', 'type', 'priority', 'user_id'],
        ];
    }

    public function fields()
    {
        return [
            'id',
            'name' => function (Coworker $model) {
                return $model->name;
            },
            'phone',
            'email',
            'category' => function (Coworker $model) {
                return $model->category;
            },
            'priority' => function (Coworker $model) {
                $list = [
                    \Yii::t('app', 'Priority low'),
                    \Yii::t('app', 'Priority normal'),
                    \Yii::t('app', 'Priority high'),
                ];
                return $model->priority;
            },
            'coworkerProperties' => function (Coworker $model) {
                $properties = $model->coworkerProperties;
                $result = [];
                foreach ($properties as $property) {
                    $result[] = ['property' => $property->property, 'value' => $property->value, 'dimension' => $property->dimension];
                }
                return $result;
            },
            'attachments',
            'hours'
        ];
    }

    public function load($data, $formName = null)
    {
        $model = new UserRegisterForm([
            'email' => $formName ? $data[$formName]['email'] : $data['Coworker']['email']
        ]);
        if ($flag = $model->update()) {
            $this->user_id = $model->getId();
        }
        return parent::load($data, $formName) && $flag;
    }

    public function normalizePhone($value)
    {
        return preg_replace("/(\+\(\)\ \-)/", "", $value);
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'firstname' => Yii::t('app', 'First Name'),
            'lastname' => Yii::t('app', 'Last Name'),
            'phone' => Yii::t('app', 'Phone'),
            'email' => Yii::t('app', 'Email'),
            'category_id' => Yii::t('app', 'Category'),
            'hours' => Yii::t('app', 'Hours'),
        ];
    }

    public function upload()
    {
        $this->save(false);
        foreach ($this->files as $item) {
            $attach = new Attachment();
            $attach->file = $item;
            $attach->target_class = Coworker::className();
            if ($attach->upload() && $attach->save()) {
                $this->link('attachments', $attach, ['target_class' => Coworker::className()]);
            }
        }
        return true;
    }

    /**
     * Gets query for [[Category]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[CoworkerProperties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoworkerProperties()
    {
        return $this->hasMany(CoworkerProperty::class, ['coworker_id' => 'id']);
    }

    public function setCoworkerProperties($data)
    {
        $this->save(false);
        foreach ($this->coworkerProperties as $coworkerProperty) {
            $this->unlink('coworkerProperties', $coworkerProperty, true);
        }
        foreach ($data as $item) {
            $link = new CoworkerProperty(array_merge($item, ['coworker_id' => $this->id]));
            if ($link->save()) {
                $this->link('coworkerProperties', $link);
            }
        }
    }

    /**
     * Gets query for [[OrderCoworkers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrderCoworkers()
    {
        return $this->hasMany(OrderCoworker::class, ['coworker_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])->viaTable('order_coworker', ['coworker_id' => 'id']);
    }

    public function getHours()
    {
        return $this->hasMany(Hours::class, ['coworker_id' => 'id']);
    }

    /**
     * Gets query for [[Properties]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])->viaTable('coworker_property', ['coworker_id' => 'id']);
    }

    public function setProperties($data)
    {
        foreach ($this->properties as $property) {
            $this->unlink('properties', $property, true);
        }
        foreach ($data as $item) {
            $p = Property::findOne($item);
            $this->link('properties', $p);
        }
    }

    /**
     * Gets query for [[Techniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechniques()
    {
        return $this->hasMany(Technique::class, ['coworker_id' => 'id']);
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['target_id' => 'id']);
    }

    public function setAttachments($data)
    {
        $this->save(false);
        foreach ($this->attachments as $attachment) {
            $this->unlink('attachments', $attachment, true);
        }
        foreach ($data as $item) {
            $attach = new Attachment();
            $attach->url = $item;
            $attach->target_class = self::className();
            if ($attach->save()) {
                $this->link('attachments', $attach, ['target_class' => self::className()]);
            }
        }
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public static function getPriorityList($priority = null)
    {
        $list = [
            self::PRIORITY_LOW => Yii::t('app', 'Priority low'),
            self::PRIORITY_NORMAL => Yii::t('app', 'Priority normal'),
            self::PRIORITY_HIGH => Yii::t('app', 'Priority high'),
        ];
        if ($priority !== null) {
            return $list[$priority];
        } else {
            return $list;
        }
    }

    public function getPriorityName($priority = null)
    {
        $list = [
            self::PRIORITY_LOW => Yii::t('app', 'Priority low'),
            self::PRIORITY_NORMAL => Yii::t('app', 'Priority normal'),
            self::PRIORITY_HIGH => Yii::t('app', 'Priority high'),
        ];
        if ($priority !== null) {
            return $list[$this->priority];
        } else {
            return $list[$priority];
        }
    }

    public function sendMessage($text, $keyboard, $order_id = null)
    {
//        if ($this->user->device_id)
        if ($order_id) {
            $messages = TelegramMessage::find()->where(["order_id" => $order_id])->andWhere(["chat_id" => $this->chat_id])->all();
            foreach ($messages as $message) {
                if ($message) {
                    $message->remove();
                }
            }
        }
        if ($this->chat_id) {
            $telegramMessage = new TelegramMessage([
                'chat_id' => $this->chat_id,
                'text' => $text,
                'reply_markup' => $keyboard,
                'order_id' => $order_id,
                'status' => TelegramMessage::STATUS_NEW,
            ]);
            $telegramMessage->send();
            echo "\tMessage sent!\n\n";
        }
    }

    public static function searchByFilter($filter, $priority = Coworker::PRIORITY_HIGH)
    {
        $query = $filter->getCoworkers($priority);
        return $query->all();
    }

    public function invite()
    {
        \Yii::$app->mailer
            ->compose('coworker\invite', ['model' => $this])
            ->setFrom(['build@amgcompany.ru' => 'build@amgcompany.ru'])
            ->setTo($this->email)
            ->setSubject(\Yii::$app->name . ' robot')
            ->send();
    }

    public function notify(Order $model)
    {
        \Yii::error( $model->id );
        if ($this->device_id) {
            $message = new ExpoMessage([
                "title" => \Yii::t("app", "New order") . " #{$model->id}",
                "body" => \Yii::t("app", "New order") . " #{$model->id}\n".
                    \Yii::t("app", "Building") . ": {$model->building->title}\n".
                    \Yii::t("app", "Address") . ": {$model->building->location->address}",
                    \Yii::t("app", "Date") . ": " . \Yii::$app->formatter->asDate($model->date) . "\n",
                "categoryId" => "new-order",
                "data" => ["order_id" => $this->id]
            ]);
            $expo = new Expo();
            $expo->send($message)->to($this->device_id)->push();
        }
    }
}
