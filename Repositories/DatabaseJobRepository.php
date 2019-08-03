<?php
namespace Surges\Jobpanel\Repositories;

use DB;
use Illuminate\Support\Carbon;
use Surges\Jobpanel\JobRepository;

class DatabaseJobRepository implements JobRepository
{
    public $config;

    public function __construct($config)
    {
        $this->config = $config;

    }

    public function getQueues()
    {
        return $this->getConnection()->groupBy('queue')->pluck('queue')->all();
    }

    //未处理任务 统计
    public function untreatedCount($queue = 'default')
    {
        return $this->untreatedQuery($queue)->count();
    }

    // 延迟任务统计
    public function delayedCount($queue = 'default')
    {
        return $this->delayedQuery($queue)->count();

    }

    // 待处理任务
    public function reservedCount($queue = 'default')
    {
        return $this->reservedQuery($queue)->count();

    }

    // 未处理任务
    public function untreated($queue = 'default', $perPage = 15)
    {
        $jobs = $this->untreatedQuery($queue)->paginate($perPage);

        return [
            'lists' => $this->parse($jobs),
            'pagination' => $this->getPaginator($jobs),
        ];
    }

    // 延迟任务
    public function delayed($queue = 'default', $perPage = 15)
    {
        $jobs = $this->delayedQuery($queue)->paginate($perPage);

        return [
            'lists' => $this->parse($jobs),
            'pagination' => $this->getPaginator($jobs),
        ];
    }

    // 待处理任务
    public function reserved($queue = 'default', $perPage = 15)
    {
        $jobs = $this->reservedQuery($queue)->paginate($perPage);

        return [
            'lists' => $this->parse($jobs),
            'pagination' => $this->getPaginator($jobs),
        ];
    }

    protected function untreatedQuery($queue = 'default')
    {
        return $this->getConnection()
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                return $query->whereNull('reserved_at')->whereRaw('available_at = created_at');
            });
    }

    protected function delayedQuery($queue = 'default')
    {
        return $this->getConnection()
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                return $query->whereNull('reserved_at')->whereRaw('available_at > created_at');
            });
    }

    protected function reservedQuery($queue = 'default')
    {
        return $this->getConnection()
            ->where('queue', $this->getQueue($queue))
            ->where(function ($query) {
                return $query->whereNotNull('reserved_at');
            });
    }

    protected function getPaginator($jobs)
    {
        return [
            'total' => $jobs->total(),
            'current_page' => $jobs->currentPage(),
            'has_more' => $jobs->hasMorePages(),
            'per_page' => $jobs->perPage(),
            'count' => $jobs->count(),
        ];
    }

    protected function parse($jobs)
    {
        return $jobs->map(function ($value) {
            $payload = json_decode($value->payload, true);
            return [
                'id' => $value->id,
                'queue' => $value->queue,
                'payload' => [
                    'displayName' => $payload['displayName'],
                    'job' => $payload['job'],
                    'maxTries' => (int) $payload['maxTries'],
                    'delay' => (string) $payload['delay'],
                    'timeout' => (string) $payload['timeout'],
                    'timeoutAt' => empty($payload['timeoutAt']) ? '' : date('Y-m-d H:i:s', $payload['timeoutAt']),
                    'data' => [
                        'commandName' => $payload['data']['commandName'],
                        'command' => unserialize($payload['data']['command']),
                    ],
                ],
                'attempts' => $value->attempts,
                'reserved_at' => empty($value->reserved_at) ? '' : date('Y-m-d H:i:s', $value->reserved_at),
                'available_at' => empty($value->available_at) ? '' : date('Y-m-d H:i:s', $value->available_at),
                'created_at' => empty($value->created_at) ? '' : date('Y-m-d H:i:s', $value->created_at),
            ];
        })->toArray();
    }

    protected function getConnection()
    {
        return DB::table($this->config['database']['table']);
    }

    protected function getQueue($queue)
    {
        return $queue ?: $this->default;
    }

    protected function currentTime()
    {
        return Carbon::now()->getTimestamp();
    }
}
