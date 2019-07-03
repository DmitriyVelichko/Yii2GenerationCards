<?php

namespace backend\modules\cards\models;

use Yii;
use \yii\db\ActiveRecord;
use common\interfaces\iCardsBack;
use common\controllers\ElasticController;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;

/**
 * This is the model class for table "cards".
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $image
 * @property int $countsViews
 */
class Cards extends ActiveRecord implements iCardsBack
{
    public $pages;
    public $searchModel;
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

    public function findLastRows($limit){}

    public function findAllRows()
    {
        $this->searchModel = new CardsSearch();
        return $this->searchModel->search(Yii::$app->request->queryParams);
    }

    public function getSearchModel()
    {
        return $this->searchModel;
    }

    public function createRow()
    {
        if ($this->load(Yii::$app->request->post()) && $this->save()) {

            $data = Yii::$app->request->post()['Cards'];
            $id = Yii::$app->db->lastInsertID;

            $image = UploadedFile::getInstance($this,'image');
            $imageName = 'card_'.$id.'.'.$image->getExtension();
            $image->saveAs(Yii::getAlias('@cardsImgPath').'/'.$imageName);
            $this->image = $imageName;
            $this->save();

            if(!empty($id) && !empty($data)){
                $this->elastic->actionCreateDocument('cards',$id,$data);
            }

            return true;
        }
        return false;
    }

    public function updateRow($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $data = Yii::$app->request->post()['Cards'];

            if(!empty($id) && !empty($data)){
                $this->elastic->actionUpdateDocument('cards',$id,$data);
            }

            return [
                'model' => $model,
                'status' => true
            ];
        }

        return [
            'model' => $model,
            'status' => false
        ];
    }

    public function deleteRow($id)
    {
        $this->elastic->actionDeleteDocument('cards',$id);
        return Cards::findOne($id)->delete();
    }

    /**
     * Finds the Cards model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Cards the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id)
    {
        if (($model = Cards::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
