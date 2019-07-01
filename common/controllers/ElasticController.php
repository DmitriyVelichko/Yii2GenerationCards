<?php

namespace common\controllers;

use yii\base\Controller;
use common\models\Elastic;

class ElasticController extends Controller
{
    public function actionIndex()
    {
        $elastic = new Elastic();

        foreach ($_POST as $field => $value){
            if(!empty($field) && !empty($value)){
                $elastic->$field = $value;
            }
        }

        if($elastic->insert()){
//            echo 'exist index';
        } else {
            print_r($elastic);
        }
    }

    public function actionFind()
    {
        $data = [];

        if(isset($_GET['q'])){
            $elastic = new Elastic();
            $q = $_GET['q'];

            $query = $elastic->findAll([
                'body' => [
                    'query' => [
                        'bool' => [
                            'should' => [
                                'match' => ['name' => $q],
                                'match' => ['description' => $q]
                            ]
                        ]
                    ]
                ]
            ]);

            if($query['hits']['total'] >= 1){
                $results = $query['hits']['hits'];
            }

            if(isset($results)){
                foreach ($results as $r){
                    $data[]['id'] = $r['_id'];
                    $data[]['name'] = $r['_source']['name'];
                    $data[]['description'] = $r['_source']['description'];
                    $data[]['image'] = $r['_source']['image'];
                    $data[]['countsViews'] = $r['_source']['countsViews'];
                }
            }

        }

        return $data;
    }
}