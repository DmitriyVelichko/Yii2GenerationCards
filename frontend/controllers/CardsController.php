<?php

namespace frontend\controllers;

use Yii;
use yii\web\HttpException;
use frontend\models\Cards;
use common\controllers\ElasticController;

class CardsController extends MainController
{
    public $model;
    public $limit = 6;
    public $elastic;

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
        $this->model = new Cards;
    }

    public function actionIndex()
    {
        $cards = $this->model->findLastRows($this->limit);
        $pages = $this->model->getPages();

        return $this->render('index', compact('cards','pages'));
    }

    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $card = $this->model->getById($id);

        if(!empty($card)){
            $this->incCount($card);
        }

        if(empty($card)) throw new HttpException(404,'Такой страницы нет!');
        return $this->render('view', compact('id','card'));
    }

    public function incCount($card)
    {
        $id = $card->getAttribute('id');

        if(!isset($_COOKIE['userCards'.$id])) {
            $cookie_name = 'userCards'.$id;
            $cookie_value = md5('value_'.rand(1,10000));
            setcookie($cookie_name, $cookie_value, time() + (86400 * 30), "/");

            $data['Cards'] = $card->getAttributes();
            unset($data['Cards']['id']);
            $data['Cards']['countsViews'] = $card->getAttribute('countsViews') + 1;

            $model = \backend\modules\cards\models\Cards::findOne($id);
            $model->load($data,'Cards');
            $model->save();
        }
    }
}
