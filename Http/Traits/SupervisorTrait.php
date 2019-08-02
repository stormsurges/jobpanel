<?php
namespace Jobpanel\Http\Traits;

use Illuminate\Http\Request;
use Jobpanel\Facades\Supervisor;
use Response;

trait SupervisorTrait
{

    public function index()
    {

        $lists = Supervisor::getAllProcessInfo();

        return Response::success([
            'pid' => Supervisor::getPID(),
            'state' => Supervisor::getState(),
            'process_count' => collect($lists)->count(),
            'group_count' => collect($lists)->groupBy('group')->count(),
            'lists' => collect($lists)->groupBy('group'),
        ]);
    }

    // 获取状态
    public function getState()
    {
        return Response::success(
            Supervisor::getState()
        );
    }

    // 获取主进程ID
    public function getPID()
    {
        return Response::success([
            'pid' => Supervisor::getPID(),
        ]);
    }

    // 获取所有进程列表
    public function getAllProcessInfo()
    {
        $lists = Supervisor::getAllProcessInfo();

        return Response::success([
            'lists' => collect($lists)->groupBy('group'),
        ]);
    }

    // 重启主进程
    public function restart()
    {
        return Response::success([
            'result' => Supervisor::restart(),
        ]);
    }

    // 终止进程
    public function shutdown()
    {
        return Response::success([
            'result' => Supervisor::shutdown(),
        ]);
    }

    // 启动线程
    public function startProcess(Request $request)
    {
        return Response::success([
            'result' => Supervisor::startProcess(
                $request->input('process_name')
            ),
        ]);
    }

    // 启动线程组
    public function startProcessGroup(Request $request)
    {
        return Response::success([
            'lists' => Supervisor::startProcessGroup(
                $request->input('group_name')
            ),
        ]);
    }

    // 终止线程
    public function stopProcess(Request $request)
    {
        return Response::success([
            'result' => Supervisor::stopProcess(
                $request->input('process_name')
            ),
        ]);
    }

    // 终止线程组
    public function stopProcessGroup(Request $request)
    {
        return Response::success([
            'lists' => Supervisor::stopProcessGroup(
                $request->input('group_name')
            ),
        ]);
    }

}
