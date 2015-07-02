<?php

namespace jerryhsia\comment;
use yii\db\ActiveRecord;
use Yii;

/**
 * This is the model class for table "{{%comment}}".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property integer $owner_group_id
 * @property integer $owner_id
 * @property string $content
 * @property integer $creator_id
 * @property integer $created_at
 */
class Comment extends ActiveRecord
{
    public static function tableName()
    {
        return '{{%comment}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['creator_id', 'default', 'value' => function() {
                if (empty($this->creator_id) && Yii::$app->hasProperty('user')) {
                    $userId = Yii::$app->user->getId();
                    return $userId ? $userId : 0;
                }
                return 0;
            }],
            [['owner_group_id', 'owner_id', 'creator_id', 'content'], 'required'],
            [['parent_id'], 'default', 'value' => 0]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'parent_id' => 'Parent ID',
            'owner_group_id' => 'Owner Group ID',
            'owner_id' => 'Owner ID',
            'content' => 'Content',
            'creator_id' => 'Creator ID',
            'created_at' => 'Created At'
        ];
    }

    public function getCreator()
    {
        return $this->hasOne(Yii::$app->commentService->commentCreatorModel,
            [Yii::$app->commentService->commentCreatorIdField => 'creator_id']
        );
    }
}
