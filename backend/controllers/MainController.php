<?php


namespace backend\controllers;

use yii\web\Controller;

class MainController extends Controller
{
    public function debug($arr)
    {
        var_dump($arr, true);
    }
}