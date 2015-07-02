<?php

namespace jerryhsia\comment;
use yii\base\Component;

class CommentService extends Component
{
    public $commentClass = 'jerryhsia\comment\Comment';

    public $commentCreatorClass = '';

    public $commentCreatorIdField = 'id';

    /**
     * @param array $attributes
     * @return \jerryhsia\comment\Comment
     */
    public function save(array $attributes = [])
    {
        $model = new $this->commentClass();
        $model->setAttributes($attributes, false);

        return $model->save();
    }

    public function search(array $filter = [])
    {
        $class = $this->commentClass;
        $query = $class::find();

        $eqFields = ['owner_group_id', 'owner_id'];
        foreach ($eqFields as $field) {
            if (isset($filters[$field])) {
                $query->andFilterWhere([$field => $filters[$field]]);
            }
        }

        return $query;
    }
}
