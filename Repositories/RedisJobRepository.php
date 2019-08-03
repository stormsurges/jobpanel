<?php
namespace Surges\Jobpanel\Repositories;

use function Opis\Closure\unserialize;
use Illuminate\Support\Facades\Redis;
use Surges\Jobpanel\JobRepository;

class RedisJobRepository implements JobRepository
{
    public $config;

    public $redis;
    protected $default = 'default';

    public function __construct()
    {

        $this->config = config('queue.connections.redis');

    }

    public function getQueues()
    {
        $keys = $this->getConnection()->keys('queues*');

        return collect($keys)->map(function ($item) {
            $explode = explode(':', $item);
            if (!empty($explode[1])) {
                return $explode[1];
            }
        })->unique()->values()->all();
    }

    //未处理任务 统计
    public function untreatedCount($queue = 'default')
    {
        return $this->getConnection()->llen($this->getQueue($queue));
    }

    // 延迟任务统计
    public function delayedCount($queue = 'default')
    {
        return $this->getConnection()->zcard($this->getQueue($queue) . ':delayed');

    }

    // 待处理任务
    public function reservedCount($queue = 'default')
    {
        return $this->getConnection()->zcard($this->getQueue($queue) . ':reserved');

    }

    // 未处理任务
    public function untreated($queue = 'default', $perPage = 15, $page = 1)
    {
        $total = $this->getConnection()->llen($this->getQueue($queue));
        $paginator = $this->getPaginator($total, $perPage, $page);
        $lists = $this->getConnection()->lrange($this->getQueue($queue), $paginator['limit']['start'], $paginator['limit']['stop']);

        $jobs = collect($lists)->map(function ($value) use ($queue) {
            return $this->parse($value, $queue);
        })->values()->toArray();

        return [
            'lists' => $jobs,
            'pagination' => $paginator['page'],
        ];
    }

    // 延迟任务
    public function delayed($queue = 'default', $perPage = 15, $page = 1)
    {
        $total = $this->getConnection()->zcard($this->getQueue($queue) . ':delayed');
        $paginator = $this->getPaginator($total, $perPage, $page);

        $lists = $this->getConnection()->zrange($this->getQueue($queue) . ':delayed', $paginator['limit']['start'], $paginator['limit']['stop'], 'WITHSCORES');

        $jobs = collect($lists)->map(function ($scores, $payload) use ($queue) {
            return $this->parse($payload, $queue, ['available_at' => $scores]);
        })->values()->toArray();

        return [
            'lists' => $jobs,
            'pagination' => $paginator['page'],
        ];
    }

    // 待处理任务
    public function reserved($queue = 'default', $perPage = 15, $page = 1)
    {
        $total = $this->getConnection()->zcard($this->getQueue($queue) . ':reserved');
        $paginator = $this->getPaginator($total, $perPage, $page);
        $lists = $this->getConnection()->zrange($this->getQueue($queue) . ':reserved', $paginator['limit']['start'], $paginator['limit']['stop'], 'WITHSCORES');

        $jobs = collect($lists)->map(function ($scores, $payload) use ($queue) {
            return $this->parse($payload, $queue, ['reserved_at' => $scores]);
        })->values()->toArray();

        return [
            'lists' => $jobs,
            'pagination' => $paginator['page'],
        ];
    }

    protected function parse($value, $queue, $options = [])
    {
        $payload = json_decode($value, true);

        $item = [
            'id' => $payload['id'],
            'queue' => $queue,
            'payload' => [
                'displayName' => $payload['displayName'],
                'job' => $payload['job'],
                'maxTries' => $payload['maxTries'],
                'delay' => $payload['delay'],
                'timeout' => $payload['timeout'],
                'timeoutAt' => empty($payload['timeoutAt']) ? '' : date('Y-m-d H:i:s', $payload['timeoutAt']),
                'data' => [
                    'commandName' => $payload['data']['commandName'],
                    'command' => unserialize($payload['data']['command']),
                ],
            ],
            'attempts' => $payload['attempts'],
            'tags' => $payload['tags'],
            'available_at' => empty($options['available_at']) ? '' : date('Y-m-d H:i:s', $options['available_at']),
            'reserved_at' => empty($options['reserved_at']) ? '' : date('Y-m-d H:i:s', $options['reserved_at']),
            'created_at' => empty($payload['pushedAt']) ? '' : date('Y-m-d H:i:s', $payload['pushedAt']),
        ];

        return $item;
    }

    protected function getPaginator($total = 0, $perPage = 15, $page = 1)
    {
        $page = !empty($_REQUEST['page']) ? $_REQUEST['page'] : $page;
        $perPage = !empty($_REQUEST['per_page']) ? $_REQUEST['per_page'] : $perPage;

        $count = intval(ceil($total / $perPage));
        $start = $page <= 1 ? 0 : ($page - 1) * $perPage;
        return [
            'page' => [
                'total' => $total,
                'count' => $count,
                'current_page' => $page,
                'has_more' => $count > $page,
                'per_page' => $perPage,
            ],
            'limit' => [
                'start' => $start,
                'stop' => $start + $perPage - 1,
            ],
        ];
    }

    protected function getConnection()
    {
        return Redis::connection($this->config['connection']);
    }

    protected function getQueue($queue)
    {
        return 'queues:' . ($queue ?: $this->default);
    }
}
