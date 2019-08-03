<?php

namespace Surges\Jobpanel;

class JobFailedManager
{
    public $connection;

    public $queue;

    public function __construct(JobRepository $connection, $queue)
    {
        $this->queue = $queue;

        $this->connection = $connection;

    }

    // 失败任务重试
    public function retry($ids)
    {
        return $this->connection->retry($ids);
    }

    // 失败任务清除
    public function forget($ids)
    {
        return $this->connection->forget($ids);
    }

    // 失败任务统计
    public function failedCount($queue)
    {
        return $this->connection->failedCount($this->getQueueName($queue));
    }

    // 失败任务列表
    public function failedLists($queue)
    {
        return $this->connection->failedLists($this->getQueueName($queue));
    }

    // 获取所有任务列表
    public function getQueues()
    {
        return $this->connection->getQueues();
    }

    protected function getQueueName($queue)
    {
        return $queue ?: $this->queue;
    }

}
