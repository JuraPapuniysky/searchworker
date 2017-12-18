<?php


namespace app\models;


use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class Worker
 * @package app\models
 * @property Subscription[] $subscriptions;
 */
class Worker
{
    public $subscriptions;
    public $connection;
    public $channel;
    public $updateTime;

    public function __construct()
    {
        $this->subscriptions = Subscription::find()->all();
        $this->updateTime = time();
        $this->connection = new AMQPStreamConnection('185.16.42.168', 5672, 'tbot', 'ASDFwEWEW');
        $this->channel = $this->connection->channel();
    }

    public function work()
    {
        $this->channel->queue_declare('tbot_message_analyze');
        $callback = function($msg) {
            $this->search($msg);
            $this->updateSubscriptions();
        };
        $this->channel->basic_consume('tbot_message_analyze', '', false, true, false, false, $callback);

        while(count($this->channel->callbacks)) {
            $this->channel->wait();
        }
    }

    private function search($msg)
    {
        $task = json_decode($msg->body);
        if ($task->task_type == 'tbot_message_analyze'){
            foreach ($this->subscriptions as $subscription) {
                $searchEngine = new SearchEngine();
                $index = $searchEngine->makeIndex($task->task_data->post->post_data);
                $result = $searchEngine->search($searchEngine->makeIndex($subscription->user_keywords), $index);
                if ($result > 0){
                   $postId = Post::savePost($task->task_data->post);
                   Mention::createNewMention($subscription->id, $postId);
                }
            }
        }
    }

    private function updateSubscriptions()
    {
        if ($this->updateTime + 300 <= time()){
            $lastCreated = $this->subscriptions[count($this->subscriptions-1)]->created_at;
            $newSubscriptions = Subscription::findAll('created_at' > $lastCreated);
            if (count($newSubscriptions) > 0){
                foreach ($newSubscriptions as $newSubscription){
                    array_push($this->subscriptions, $newSubscription);
                }
            }
            $this->updateTime = time();
        }
    }
}