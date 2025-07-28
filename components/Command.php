<?php
namespace app\components;

use Closure;
use Yii;
use yii\base\Component;

/**
 * @author Akbar Joudi <akbar.joody@gmail.com>
 */
class Command extends Component{


    /**
     *  run command bot
     * @param String $command
     * @param Callable $fun
     * @return mixed|void
     */
    public static function run($command, callable $fun){
        $telegram = Yii::$app->telegram;
        $text = "";
        if (isset($telegram->input->message)) {
            $text = $telegram->input->message->text;
        }
        if (isset($telegram->input->callback_query)) {
            $text = $telegram->input->callback_query->data;
        }
        if (!empty($text)) {
            $args = explode(' ', $text);
            $inputCommand = array_shift($args);
            if($inputCommand === $command){
                return call_user_func_array($fun, [$telegram, $args]);
            }
        }
    }
}