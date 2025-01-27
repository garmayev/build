<?php

namespace app\models;

use Yii;

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
<<<<<<< HEAD
 * @property int $notify_date
 * @property int $user_id
=======
>>>>>>> a0d55bd (Server Fix)
 *
 * @property Category $category
 * @property CoworkerProperty[] $coworkerProperties
 * @property OrderCoworker[] $orderCoworkers
 * @property Order[] $orders
 * @property Property[] $properties
 * @property Technique[] $techniques
 * @property Attachment[] $attachments
 */
class Coworker extends \yii\db\ActiveRecord
{
    const PRIORITY_LOW = 0;
    const PRIORITY_NORMAL = 1;
    const PRIORITY_HIGH = 2;
    public $files;

    /**
     * {@inheritdoc}
     */
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
            [['category_id', 'priority', 'notify_date', 'user_id'], 'integer'],
            [['firstname', 'lastname', 'phone', 'email'], 'string', 'max' => 255],
            [['coworkerProperties'], 'safe'],
            [['phone', 'email'], 'unique'],
            [['phone'], 'filter', 'filter' => [$this, 'normalizePhone']],
            [['firstname', 'lastname', 'phone', 'email'], 'default', 'value' => ''],
            [['priority'], 'default', 'value' => self::PRIORITY_LOW],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
//            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
        ];
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
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $user = new User([
                'email' => $this->email,
                'username' => explode('@', $this->email)[0],
                'password_hash' => Yii::$app->security->generatePasswordHash($this->phone),
                'auth_key' => Yii::$app->security->generateRandomString(),
                'access_token' => Yii::$app->security->generateRandomString(),
                'status' => User::STATUS_ACTIVE,
            ]);
            if ($user->save()) {
                $this->link('user', $user);
            } else {
                \Yii::error($user->getErrorSummary(true));
            }
        }
        parent::afterSave($insert, $changedAttributes);
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

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getPriorityName($id = null)
    {
        $list = [
            Coworker::PRIORITY_GROUP => \Yii::t('app', 'PRIORITY_GROUP'),
            Coworker::PRIORITY_MEET => \Yii::t('app', 'PRIORITY_MEET'),
            Coworker::PRIORITY_UNKNOWN => \Yii::t('app', 'PRIORITY_UNKNOWN'),
        ];
        return $list[$this->priority];
    }

    public function setUser($data)
    {
        $this->save(false);
        $user = new User();
        if (isset($data['password'])) {
            $data['password_hash'] = \Yii::$app->security->generatePasswordHash( $data['password'] );
        }
        if ($user->load(['User' => $data]) && $user->save()) {
            $this->link('user', $user);
        }
    }
}