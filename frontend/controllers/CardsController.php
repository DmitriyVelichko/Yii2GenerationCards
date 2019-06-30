<?php


namespace frontend\controllers;
use frontend\models\Cards;
use yii\data\Pagination;
use Yii;
use yii\web\HttpException;

class CardsController extends MainController
{
    public function actionIndex()
    {
        $query = Cards::find()
            ->select([
                'id',
                'name',
                'description',
                'image',
                'countsViews'
            ])
            ->limit(6)
            ->orderBy('id DESC');

        $pages = new Pagination([
            'totalCount' => $query->count(),
            'pageSize' => 6,
            'pageSizeParam' => false,
            'forcePageParam' => false,
        ]);

        $cards = $query->offset($pages->offset)->limit($pages->limit)->all();
//        debug($cards);
        return $this->render('index', compact('cards','pages'));
    }

    public function actionView()
    {
        $id = Yii::$app->request->get('id');
        $card = Cards::findOne($id);
        if(empty($card)) throw new HttpException(404,'Такой страницы нет!');
        return $this->render('view', compact('id','card'));
    }
}