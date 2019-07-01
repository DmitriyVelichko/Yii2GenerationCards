<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cards}}`.
 */
class m190701_184434_create_cards_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cards}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(50)->notNull()->defaultValue(''),
            'description' => $this->string(500)->notNull()->defaultValue(''),
            'image' => $this->string(500)->notNull()->defaultValue(''),
            'countsViews' => $this->integer(11)->notNull()->defaultValue(0),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%cards}}');
    }
}
