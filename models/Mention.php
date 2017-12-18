<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "mention".
 *
 * @property integer $id
 * @property integer $post_id
 * @property integer $subscription_id
 * @property integer $is_notified
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Subscription $subscription
 * @property Post $post
 */
class Mention extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'mention';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['post_id', 'subscription_id', 'is_notified', 'created_at', 'updated_at'], 'integer'],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscription::className(), 'targetAttribute' => ['subscription_id' => 'id']],
            [['post_id'], 'exist', 'skipOnError' => true, 'targetClass' => Post::className(), 'targetAttribute' => ['post_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'post_id' => 'Post ID',
            'subscription_id' => 'Subscription ID',
            'is_notified' => 'Is Notified',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['id' => 'subscription_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */


    public static function createNewMention($subscriptionId, $postId)
    {
        $model = new Mention();
        $model->post_id = $postId;
        $model->subscription_id = $subscriptionId;
        $model->is_notified = 1;
        return $model->save;
    }
}
