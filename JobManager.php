<?php

namespace Surges\Jobpanel;

class JobManager
{
    public $connection;

    public $queue;

    public function __construct(JobRepository $connection, $queue)
    {
        $this->queue = $queue;

        $this->connection = $connection;

    }

    // 未处理任务
    public function untreated($queue, $per_page = 15)
    {
        return $this->connection->untreated($queue, $per_page);
    }

    // 延迟任务
    public function delayed($queue, $per_page = 15)
    {
        return $this->connection->delayed($queue, $per_page);
    }

    // 待处理任务
    public function reserved($queue, $per_page = 15)
    {
        return $this->connection->reserved($queue, $per_page);
    }

    // 未处理任务统计
    public function untreatedCount($queue)
    {
        return $this->connection->untreatedCount($this->getQueueName($queue));
    }

    // 延迟任务统计
    public function delayedCount($queue)
    {
        return $this->connection->delayedCount($this->getQueueName($queue));
    }

    // 待处理任务统计
    public function reservedCount($queue)
    {
        return $this->connection->reservedCount($this->getQueueName($queue));
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
