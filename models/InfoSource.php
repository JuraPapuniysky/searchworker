<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "info_source".
 *
 * @property integer $id
 * @property string $title
 * @property string $url
 * @property integer $subscribers_quantity
 * @property integer $info_source_type_id
 * @property integer $indexing_priority
 * @property integer $last_indexed_date_time
 * @property integer $created_at
 * @property integer $updated_at
 * @property integer $info_source_id
 * @property static $access_hash
 *
 * @property Post[] $posts
 */
class InfoSource extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'info_source';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['url', 'access_hash'], 'string'],
            [['info_source_id', 'subscribers_quantity', 'indexing_priority', 'last_indexed_date_time', 'created_at', 'updated_at'], 'integer'],
            [['title'], 'string', 'max' => 255],
        ];
    }

    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'url' => 'Url',
            'subscribers_quantity' => 'Subscribers Quantity',
            'info_source_type_id' => 'Info Source Type ID',
            'indexing_priority' => 'Indexing Priority',
            'last_indexed_date_time' => 'Last Indexed Date Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Post::className(), ['info_source_id' => 'id']);
    }


}
