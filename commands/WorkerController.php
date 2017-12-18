<?php


namespace app\commands;


use app\models\Subscription;
use app\models\Worker;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use yii\console\Controller;


class WorkerController extends Controller
{
    public function actionIndex()
    {
           $worker = new Worker();
           $worker->work();
    }
}