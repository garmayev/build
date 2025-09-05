<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "report".
 *
 * @property int $id
 * @property string|null $comment
 * @property int $created_at
 * @property int $order_id
 *
 * @property Attachment[] $attachments
 */
class Report extends \yii\db\ActiveRecord
{
    public $files;

    /**
     * @return array[]
     */
    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => 'yii\behaviors\TimestampBehavior',
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => false,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     * @return string
     */
    public static function tableName(): string
    {
        return 'report';
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function rules(): array
    {
        return [
            [['comment'], 'string'],
            [['created_at', 'order_id'], 'integer'],
            [['order_id'], 'exist', 'skipOnError' => true, 'targetClass' => Order::class, 'targetAttribute' => ['order_id' => 'id']],
            [['files'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg, jpeg', 'maxFiles' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'comment' => Yii::t('app', 'Comment'),
            'created_at' => Yii::t('app', 'Created At'),
        ];
    }

    public function getAttachments(): ActiveQuery
    {
        return $this->hasMany(Attachment::class, ['target_id' => 'id'])
            ->andWhere(['target_class' => self::class]);
    }

    public function setUrl($filenames)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save();
            foreach ($filenames as $filename) {
                $attach = new Attachment();
                $attach->url = $filename;
                $attach->target_id = $this->id;
                $attach->target_class = self::class;
                if (!$attach->save()) {
                    \Yii::error($attach->errors);
                }
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            \Yii::error($exception->message);
            $transaction->rollback();
        }
    }

    public function upload()
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save();
            if ($this->files && !empty($this->files)) {
                \Yii::error('upload files');
                foreach ($this->files as $file) {
                    $this->attachFile($file);
                }
            } else {
                \Yii::error('no files');
            }
            $transaction->commit();
            return true;
        } catch (\Exception $exception) {
            \Yii::error($exception->getMessage());
            $transaction->rollBack();
            return false;
        }
    }

    private function attachFile($file)
    {
        $attach = new Attachment([
            'file' => $file,
            'target_class' => self::class,
            'target_id' => $this->id,
        ]);
        if ($attach->upload() && $attach->save()) {
            \Yii::error('attach file');
            $this->link('attachments', $attach, ['target_class' => self::class]);
        } else {
            \Yii::error('attach file error');
            \Yii::error($attach->errors);
        }
    }
}
