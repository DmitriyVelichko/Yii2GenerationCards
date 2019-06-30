<?php

namespace backend\controllers;

class AdminController extends MainController
{
    public function actionIndex()
    {
        return $this->render('index');
    }
}