<?php

namespace backend\modules\cards\models;

use Yii;
use yii\data\Pagination;
use \yii\db\ActiveRecord;
use common\interfaces\iCardsFront;

/**
 * This is the model class for table "cards".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $image
 * @property int $countsViews
 */
class Cards extends ActiveRecord implements iCardsFront
{
    public $pages;

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

    public function findLastRows($limit)
    {
        $query = self::find()
            ->select([
                'id',
                'name',
                'description',
                'image',
                'countsViews'
            ]);
        if(!empty($limit)){
            $query->limit($limit);
        }
        $query->orderBy('id DESC');

        if($count = $query->count()){
            $this->pages = $this->getPagination($count,$limit);
        }

        $cards = $query->offset($this->pages->offset)->limit($this->pages->limit)->all();

        return $cards;
    }

    public function getPagination($count, $limit)
    {
        return new Pagination([
            'totalCount' => $count,
            'pageSize' => $limit,
            'pageSizeParam' => false,
            'forcePageParam' => false,
        ]);
    }

    public function getPages()
    {
        return $this->pages;
    }

    public function getbyId($id)
    {
        return Cards::findOne($id);
    }
}
