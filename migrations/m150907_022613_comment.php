<?php

use yii\db\Migration;

class m150907_022613_comment extends Migration
{
    public function up()
    {
        $options = "CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=MyISAM COMMENT='CommentTable'";

        $this->createTable('{{%comment}}', [
            'id'            => "int(10)    UNSIGNED NOT NULL AUTO_INCREMENT",
            'parent_id'     => "int(10)    UNSIGNED NOT NULL DEFAULT 0",
            'owner_group_id'=> "int(10)    UNSIGNED NOT NULL",
            'owner_id'      => "int(10)    UNSIGNED NOT NULL",
            'content'       => "text                NOT NULL",
            'creator_id'    => "int(10)    UNSIGNED NOT NULL DEFAULT 0",
            'created_at'    => "int(10)    UNSIGNED NOT NULL DEFAULT 0",
            'PRIMARY KEY `id`(`id`)'
        ], $options);
    }

    public function down()
    {
        $this->dropTable('{{%comment}}');
        return true;
    }
}
