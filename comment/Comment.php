<?php

namespace jerryhsia\comment;
use yii\db\ActiveRecord;

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
            [['owner_group_id', 'owner_id', 'creator_id', 'content'], 'required'],
            [['parent_id', 'is_hot', 'weight'], 'default', 'value' => 0]
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
}
