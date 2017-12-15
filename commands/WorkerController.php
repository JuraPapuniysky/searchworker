<?php


namespace app\commands;


use PhpAmqpLib\Connection\AMQPStreamConnection;
use yii\console\Controller;


class WorkerController extends Controller
{
    public function actionIndex()
    {

            $connection = new AMQPStreamConnection('185.16.42.168', 5672, 'tbot', 'ASDFwEWEW');
            $channel = $connection->channel();

            $channel->queue_declare('tbot_notification');
            $callback = function($msg) {
                echo " [x] Received ", $msg->body, "\n";
            };

            $channel->basic_consume('tbot_notification', '', false, true, false, false, $callback);

            while(count($channel->callbacks)) {
                $channel->wait();
            }

    }
}