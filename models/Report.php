<?php

namespace app\models;

use Yii;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "report".
 *
 * @property int $id
 * @property string|null $comment
 * @property int|null $created_at
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
                'attributes' => [
                    'createdAtAttribute' => 'created_at',
                    'updatedAtAttribute' => false,
                ]
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
            [['comment', 'created_at'], 'default', 'value' => null],
            [['comment'], 'string'],
            [['created_at'], 'integer'],
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
        return $this->hasMany(Attachment::class, ['and', ['target_id' => 'id'], ['target_class' => self::class]]);
    }

    public function setAttachment($attachments): void
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $this->save(false);
            if (!empty($this->files)) {
                foreach ($this->files as $file) {
                    $this->attachFile($file);
                }
            } else if($attachments) {
                $this->unlinkAll('attachments', true);
                foreach ($attachments as $attachment) {
                    $this->attachFile($attachment);
                }
            }
            $transaction->commit();
        } catch (\Exception $exception) {
            $transaction->rollBack();
        }
    }

    private function attachFile($file)
    {
        $attach = new Attachment();
        $attach->file = $file;
        if ($attach->upload() && $attach->save()) {
            $this->link('attachments', $attach, ['target_class' => self::class, 'target_id' => $this->id]);
        }
    }
}
