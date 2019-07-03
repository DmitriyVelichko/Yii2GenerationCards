<?php

namespace common\models;

use yii\elasticsearch\ActiveRecord;

class Cards extends ActiveRecord
{
    public function attributes()
    {
        return ['id','name','description','image','countsViews'];
    }

    /**
     * @return array Сопоставление для этой модели
     */
    public static function mapping()
    {
        return [
            static::type() => [
                'properties' => [
                    'name'           => ['type' => 'string'],
                    'description'    => ['type' => 'string'],
                    'image'          => ['type' => 'string'],
                    'countsViews'    => ['type' => 'integer'],
                ]
            ],
        ];
    }

    /**
     * Установка (update) для этой модели
     */
    public static function updateMapping()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->setMapping(static::index(), static::type(), static::mapping());
    }

    /**
     * Создать индекс этой модели
     */
    public static function createIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->createIndex(static::index(), [
            'settings' => [ /* ... */ ],
            'mappings' => static::mapping(),
            //'warmers' => [ /* ... */ ],
            //'aliases' => [ /* ... */ ],
            //'creation_date' => '...'
        ]);
    }

    /**
     * Удалить индекс этой модели
     */
    public static function deleteIndex()
    {
        $db = static::getDb();
        $command = $db->createCommand();
        $command->deleteIndex(static::index(), static::type());
    }
}