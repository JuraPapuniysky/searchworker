<?php
/**
 * Created by PhpStorm.
 * User: jura
 * Date: 17.12.17
 * Time: 8:42
 */

namespace app\models;


use PhpAmqpLib\Connection\AMQPStreamConnection;

class Worker
{
    public $subscriptions;
    public $connection;
    public $channel;
    public $updateTime;

    public function __construct()
    {
        $this->subscriptions = Subscription::find()->all();
        $this->connection = new AMQPStreamConnection('185.16.42.168', 5672, 'tbot', 'ASDFwEWEW');
        $this->channel = $this->connection->channel();
    }

    public function work()
    {
        $this->channel->queue_declare('tbot_message_analyze');
        $callback = function($msg) {
          $this->search($msg);
        };
        $this->channel->basic_consume('tbot_message_analyze', '', false, true, false, false, $callback);

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
            $callback->bindTo($this);
        }
    }

    private function search($msg)
    {
        $task = json_decode($msg->body);
        if ($task->task_type == 'tbot_message_analyze'){
            foreach ($this->subscriptions as $subscription) {
                $searchEngine = new SearchEngine();
                $begin_time = microtime(true);
                echo "Indexing started: $begin_time\n";

                // Индексирование //
                $index = $searchEngine->makeIndex($task->task_data->post->post_data);

                // Засекаем время конца //
                $finish_time = microtime(true);
                echo "Indexing finished: $finish_time\n";

                // Результаты //
                $total_time = $finish_time - $begin_time;
                echo "Total time: $total_time\n";

                //print_r($index);

                echo $searchEngine->search($searchEngine->makeIndex($subscription->user_keywords), $index);
            }
        }
    }
}