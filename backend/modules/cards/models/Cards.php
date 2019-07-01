<?php

namespace backend\modules\cards\models;

use Yii;

/**
 * This is the model class for table "cards".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $image
 * @property int $countsViews
 */
class Cards extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cards';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['countsViews'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['description', 'image'], 'string', 'max' => 500],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'image' => 'Image',
            'countsViews' => 'Counts Views',
        ];
    }
}
