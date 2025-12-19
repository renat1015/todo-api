<?php

use app\enums\TaskStatus;
use yii\db\Migration;

class m251210_111337_insert_test_data extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->batchInsert('{{%task}}', ['title', 'description', 'status'], [
            ['Update PHP', 'Update PHP7.4 to PHP7.5', TaskStatus::NEW->value],
            ['Add API endpoint', 'Add task update API endpoint', TaskStatus::IN_PROGRESS->value],
            ['Fix frontend', 'Fix bug on frontend (CSS)', TaskStatus::COMPLETED->value],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->truncateTable('{{%task}}');
    }
}
