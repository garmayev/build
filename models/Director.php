<?php

namespace app\models;

class Director extends User
{
    public static function tableName(): string
    {
        return "{{%user}}";
    }
}