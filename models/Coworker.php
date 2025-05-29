<?php

namespace app\models;

use app\models\forms\UserRegisterForm;
use app\models\telegram\TelegramMessage;
use ExpoSDK\Expo;
use ExpoSDK\ExpoMessage;
use floor12\phone\PhoneValidator;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\caching\TagDependency;
use yii\db\ActiveQuery;

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
 * @property int $created_at
 * @property string $chat_id
 * @property string $device_id
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
 *
 * @property Notification[] $notifications
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
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => false,
            ],
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
            [['firstname', 'lastname', 'phone', 'email', 'chat_id', 'device_id'], 'string', 'max' => 255],
            [['firstname'], 'default', 'value' => ''],
            [['coworkerProperties', 'attachments'], 'safe'],
            [['phone', 'email'], 'unique'],
            [['phone'], PhoneValidator::class],
            [['firstname', 'lastname', 'phone', 'email'], 'default', 'value' => ''],
            [['priority'], 'default', 'value' => self::PRIORITY_LOW],
            [['type'], 'default', 'value' => self::TYPE_WORKER],
            [['files'], 'file', 'skipOnEmpty' => true, 'maxFiles' => 15],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id'], 'on' => self::SCENARIO_COWORKER],
            [['category_id'], 'default', 'value' => 0, 'on' => self::SCENARIO_COWORKER],
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
        $formName = $formName ?? self::formName();
        $flag = true;
        if (empty($this->user_id)) {
            if (isset($data[$formName]['email'])) {
                $email = $data[$formName]['email'];
                if ($email) {
                    $model = new UserRegisterForm([
                        'email' => $email
                    ]);
                    if ($flag = $model->update()) {
                        $this->user_id = $model->getId();
                    }
                }
            }
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
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->files as $item) {
                $attach = new Attachment();
                $attach->file = $item;
                $attach->target_class = self::class;
                if ($attach->upload() && $attach->save()) {
                    $this->link('attachments', $attach, ['target_class' => self::class]);
                }
            }
            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error uploading files: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets query for [[Category]].
     *
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * Gets query for [[CoworkerProperties]].
     *
     * @return ActiveQuery
     */
    public function getCoworkerProperties()
    {
        return $this->hasMany(CoworkerProperty::class, ['coworker_id' => 'id']);
    }

    public function setCoworkerProperties($data)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->coworkerProperties as $coworkerProperty) {
                $this->unlink('coworkerProperties', $coworkerProperty, true);
            }
            if ($data) {
                foreach ($data as $item) {
                    $link = new CoworkerProperty(array_merge($item, ['coworker_id' => $this->id]));
                    if ($link->save()) {
                        $this->link('coworkerProperties', $link);
                    }
                }
            }
            $transaction->commit();
            TagDependency::invalidate(Yii::$app->cache, ['coworker-' . $this->id]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error setting coworker properties: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Gets query for [[OrderCoworkers]].
     *
     * @return ActiveQuery
     */
    public function getOrderCoworkers()
    {
        return $this->hasMany(OrderCoworker::class, ['coworker_id' => 'id']);
    }

    /**
     * Gets query for [[Orders]].
     *
     * @return ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])
            ->viaTable('order_coworker', ['coworker_id' => 'id']);
    }

    public function getHours()
    {
        return $this->hasMany(Hours::class, ['coworker_id' => 'id']);
    }

    /**
     * Gets query for [[Properties]].
     *
     * @return ActiveQuery
     */
    public function getProperties()
    {
        return $this->hasMany(Property::class, ['id' => 'property_id'])
            ->viaTable('coworker_property', ['coworker_id' => 'id']);
    }

    public function setProperties($data)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->properties as $property) {
                $this->unlink('properties', $property, true);
            }
            if ($data) {
                foreach ($data as $item) {
                    $property = Property::findOne($item);
                    if ($property) {
                        $this->link('properties', $property);
                    }
                }
            }
            $transaction->commit();
            TagDependency::invalidate(Yii::$app->cache, ['coworker-' . $this->id]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error setting properties: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getAttachments()
    {
        return $this->hasMany(Attachment::class, ['target_id' => 'id'])
            ->andWhere(['target_class' => self::class]);
    }

    public function setAttachments($data)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            foreach ($this->attachments as $attachment) {
                $this->unlink('attachments', $attachment, true);
            }
            if ($data) {
                foreach ($data as $item) {
                    $attach = new Attachment();
                    $attach->url = $item;
                    $attach->target_class = self::class;
                    if ($attach->save()) {
                        $this->link('attachments', $attach, ['target_class' => self::class]);
                    }
                }
            }
            $transaction->commit();
            TagDependency::invalidate(Yii::$app->cache, ['coworker-' . $this->id]);
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error('Error setting attachments: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getNotifications()
    {
        return $this->hasMany(Notification::class, ['coworker_id' => 'id']);
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getName()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }

    public static function getPriorityList($priority = null)
    {
        $list = [
            self::PRIORITY_LOW => Yii::t('app', 'Priority low'),
            self::PRIORITY_NORMAL => Yii::t('app', 'Priority normal'),
            self::PRIORITY_HIGH => Yii::t('app', 'Priority high'),
        ];
        return $priority !== null ? ($list[$priority] ?? null) : $list;
    }

    public function getPriorityName($priority = null)
    {
        return self::getPriorityList($priority ?? $this->priority);
    }

    public static function searchByFilter($filter, $priority = self::PRIORITY_HIGH)
    {
        return static::find()
            ->joinWith('filter')
            ->where(['filter.id' => $filter])
            ->andWhere(['coworker.priority' => $priority])
            ->all();
    }

    public function invite()
    {
        \Yii::$app->mailer
            ->compose('coworker/invite', ['model' => $this])
            ->setFrom(['build@amgcompany.ru' => 'build@amgcompany.ru'])
            ->setTo($this->email)
            ->setSubject(\Yii::$app->name . ' robot')
            ->send();
    }
}