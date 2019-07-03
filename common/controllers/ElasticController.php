<?php

namespace common\controllers;

use Elasticsearch\ClientBuilder;

class ElasticController
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()->build();
    }

    public function actionCreateDocument($index, $id, $data)
    {
        $params = [
            'index' => $index,
            'id' => $id,
            'body' => $data
        ];

        return $this->client->index($params);
    }

    public function actionUpdateDocument($index,$id,$data)
    {
        $params = [
            'index' => $index,
            'id'    => $id,
            'body'  => [
                'doc' => $data
            ]
        ];

        return $this->client->update($params);
    }

    public function actionDeleteDocument($index, $id)
    {
        $params = [
            'index' => $index,
            'id' => $id
        ];

        return $this->client->delete($params);
    }

    /**
     * @param $index
     * @param array $settings
     * @return array|callable
     */
    public function actionCreateIndex($index, array $settings)
    {
        $params = [
            'index' => $index,
            'body' => [
                'settings' => $settings
            ]
        ];

        return $this->client->indices()->create($params);
    }

    public function actionDeleteIndex($index)
    {
        $deleteParams = [
            'index' => $index
        ];
        return $this->client->indices()->delete($deleteParams);
    }

    public function actionGetDocument($index, $id)
    {
        $params = [
            'index' => $index,
            'id' => $id
        ];

        return $this->client->get($params);
    }

    /**
     * @param $index
     * @param array $match ['field' => 'value','field' => 'value', ...]
     * @return array|callable
     */
    public function actionSearchDocument($index, array $match)
    {
        $params = [
            'index' => $index,
            'body' => [
                'query' => [
                    'match' => $match
                ]
            ]
        ];

        return $this->client->search($params);
    }
}