<?php

namespace frontend\models;

use common\controllers\ElasticController;
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
    public $count;
    public $elastic;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $this->elastic = new ElasticController();
    }

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
        //ElasticSearch
        $cards = $this->elasticLastRows($limit);
        if(!empty($cards)){
            return $cards;
        }

        //Ну а если не сработает то сработает обычный поиск
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

        if($this->count = $query->count()){
            $this->pages = $this->getPagination($this->count,$limit);
        }

        $cards = $query->offset($this->pages->offset)->limit($this->pages->limit)->all();

        return $cards;
    }

    public function elasticLastRows($limit)
    {
        $cards = $this->getElasticLastRow();
        $count = count($cards);
        $cards = $this->sortAndSlice($cards,$limit);

        if($count > 0){
            $this->count = $count;
            $this->pages = $this->getPagination($this->count,$limit);
        }

        return $cards;
    }

    public function sortAndSlice($data,$limit){
        $count = count($data);
        if($_GET['page'] < 2 || !isset($_GET['page'])){
            $page = 1;
            $offset = 0;
        } else {
            $page = $_GET['page'];
        }
        if(!isset($offset)){
            $offset = ($count + $limit)/$page;
        }
        $output = array_slice($data, floor($offset), $limit);
        return $output;
    }

    public function getElasticLastRow(){
        $data = [];
        $elasticSearch = $this->elastic->getAllIndexes('cards');
        foreach ($elasticSearch['hits']['hits'] as $index => $rows){
            foreach ($rows as $k => $row){
                if($k == '_id'){
                    $data[$index]['id'] = $row;
                }
                if($k == '_source'){
                    foreach ($rows[$k] as $key => $val){
                        $data[$index][$key] = $val;
                    }
                }
            }
        }

        foreach ($data as $k => $row){
            if(is_array($row)){
                $data[$k] = (object)$row;
            }
        }
        foreach ($data as $k => $row){
            $data[$row->id] = (object)$row;
            unset($data[$k]);
        }
        arsort($data);

        return $data;
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

    public function getCount()
    {
        return $this->count;
    }

    public function getById($id)
    {
        return Cards::findOne($id);
    }
}
