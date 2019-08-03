<?php

namespace Surges\Jobpanel\Http\Traits;

use Illuminate\Http\Request;
use Response;
use Surges\Jobpanel\Facades\Jobpanel;
use Surges\Jobpanel\Facades\JobpanelFailed;

trait JobTrait
{

    public function index(Request $request)
    {

        $queue = $request->input('queue', 'default');
        $state = $request->input('state', 'untreated');

        $counts['counts'] = [
            'untreated' => Jobpanel::untreatedCount($queue),
            'delayed' => Jobpanel::delayedCount($queue),
            'reserved' => Jobpanel::reservedCount($queue),
            'failed' => JobpanelFailed::failedCount($queue),
        ];

        if ($state === 'failed') {
            $queues['queues'] = JobpanelFailed::getQueues();
            $results = JobpanelFailed::failedLists($queue);
        } else {
            $queues['queues'] = Jobpanel::getQueues();
            $results = Jobpanel::$state($queue);
        }

        return Response::success(
            \array_merge($counts, $queues, $results)
        );
    }

    public function retry(Request $request)
    {
        $result = JobpanelFailed::retry(
            $request->input('id')
        );
        return Response::success([
            'result' => $result,
        ]);

    }

    public function forget(Request $request)
    {
        $result = JobpanelFailed::forget(
            $request->input('id')
        );
        return Response::success([
            'result' => $result,
        ]);

    }

}
