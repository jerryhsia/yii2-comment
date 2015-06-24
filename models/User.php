<?php

namespace jerryhsia\models;
use jerryhsia\comment\CommentOwnerInterface;

class User implements CommentOwnerInterface
{
    public $id;

    public function getOwnerGroupId()
    {
        return 1;
    }

    public function getId()
    {
        return $this->id;
    }
}
