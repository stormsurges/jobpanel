<?php
namespace Jobpanel\Repositories;

use DB;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Jobpanel\JobRepository;

class FailedJobRepository implements JobRepository
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

    public function retry($id, $queue)
    {
        foreach ($this->getJobIds($id, $queue) as $id) {
            $job = $this->getConnection()->where('queue', $queue)->find($id);
            if (!is_null($job)) {
                app('queue')->connection($job->connection)->pushRaw(
                    $this->resetAttempts($job->payload), $job->queue
                );

                $this->forget($id, $queue);
            }
        }

    }

    public function forget($id, $queue)
    {

        return $this->getConnection()->where('queue', $queue)
            ->whereIn('id', $this->getJobIds($id, $queue))->delete() > 0;
    }

    protected function resetAttempts($payload)
    {
        $payload = json_decode($payload, true);

        if (isset($payload['attempts'])) {
            $payload['attempts'] = 0;
        }

        return json_encode($payload);
    }

    protected function getJobIds($id, $queue)
    {
        $ids = (array) $id;

        if (count($ids) === 1 && $ids[0] === 'all') {
            $ids = Arr::pluck($this->getConnection()->where('queue', $queue)->get(), 'id');
        }

        return $ids;
    }

    //未处理任务 统计
    public function failedCount($queue)
    {
        return $this->getConnection()->where('queue', $queue)->count();
    }

    //未处理任务列表
    public function failedLists($queue, $perPage = 15)
    {
        $jobs = $this->getConnection()->where('queue', $queue)->paginate($perPage);

        return [
            'lists' => $this->parse($jobs),
            'pagination' => $this->getPaginator($jobs),
        ];
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
                'connection' => $value->connection,
                'exception' => $value->exception,
                'created_at' => $value->failed_at,
            ];
        })->toArray();
    }

    protected function getConnection()
    {
        return DB::table($this->config['failed']['table']);
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
