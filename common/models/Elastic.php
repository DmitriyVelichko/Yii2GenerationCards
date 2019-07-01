<?php

namespace common\models;

use yii\elasticsearch\ActiveRecord;

class Elastic extends ActiveRecord
{
    public function attributes()
    {
        return ['id','name','description','image','countsViews'];
    }
}