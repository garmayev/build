<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "coworker".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $category_id
 *
 * @property Category $category
 * @property CoworkerProperty[] $coworkerProperties
 * @property OrderCoworker[] $orderCoworkers
 * @property Order[] $orders
 * @property Property[] $properties
 * @property Technique[] $techniques
 * @property User $user
 */
class Coworker extends \yii\db\ActiveRecord
{
    public $isset_user = false;
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
            [['user_id', 'category_id'], 'integer'],
            [['category_id'], 'exist', 'skipOnError' => true, 'targetClass' => Category::class, 'targetAttribute' => ['category_id' => 'id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['user_id' => 'id']],
            [['user', 'coworkerProperties'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'category_id' => Yii::t('app', 'Category ID'),
        ];
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

    /**
     * Gets query for [[Techniques]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTechniques()
    {
        return $this->hasMany(Technique::class, ['coworker_id' => 'id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
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
