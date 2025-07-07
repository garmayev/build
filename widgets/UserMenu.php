<?php

namespace app\widgets;

use yii\widgets\Menu;
use Yii;
use yii\base\Widget;

/**
 * User menu widget.
 */
class UserMenu extends Widget
{
    public $items;

    /**
     * @inheritdoc
     */
    public function run()
    {
        return Menu::widget([
            'options' => [
                'tag' => 'div',
                'class' => 'btn-group-vertical w-100',
                'role' => 'group',
            ],
            'items' => $this->items,
            'itemOptions' => [
                'tag' => 'div',
                'class' => 'btn-group',
            ],
            'linkTemplate' => '<a href="{url}" class="btn btn-default w-100">{label}</a>',
        ]);
    }
}