<?php

namespace Jobpanel\Supervisors;

use Symfony\Component\Process\Process;

class SupervisorManager
{
    public $command = 'python3';

    public $path;

    public function __construct()
    {
        $this->path = dirname(__FILE__) . '/supervisor.py';

    }

    // 获取状态
    public function getState()
    {
        $process = new Process($this->paseScript(__FUNCTION__));

        $process->run();

        if ($process->isSuccessful()) {
            return $this->parseOutput($process->getOutput());
        }

        return ['statecode' => -1, 'statename' => 'SHUTDOWN'];
    }

    // 获取主进程ID
    public function getPID()
    {
        $process = new Process($this->paseScript(__FUNCTION__));

        $process->run();

        if ($process->isSuccessful()) {
            return (int) trim($process->getOutput());
        }

        return 0;
    }

    // 获取所有进程列表
    public function getAllProcessInfo()
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__)
        );

        $process->run();

        if ($process->isSuccessful()) {
            return $this->parseOutput(
                $process->getOutput()
            );
        }

        return [];
    }

    // 重启主进程
    public function restart()
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__)
        );

        $process->run();

        if ($process->isSuccessful()) {

            return (bool) trim($process->getOutput());
        }

        return false;
    }

    // 终止线程
    public function shutdown()
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__)
        );

        $process->run();

        if ($process->isSuccessful()) {
            return (bool) trim($process->getOutput());
        }

        return false;
    }

    // 启动 线程
    public function startProcess($name)
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__, $name)
        );

        $process->run();

        if ($process->isSuccessful()) {
            return (bool) trim($process->getOutput());
        }

        return false;
    }

    // 启动线程组
    public function startProcessGroup($name)
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__, $name)
        );

        $process->run();

        if ($process->isSuccessful()) {
            return $this->parseOutput(
                $process->getOutput()
            );
        }

        return [];
    }

    // 终止线程
    public function stopProcess($name)
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__, $name)
        );

        $process->run();

        if ($process->isSuccessful()) {
            return (bool) trim($process->getOutput());
        }

        return false;
    }

    // 终止线程组
    public function stopProcessGroup($name)
    {
        $process = new Process(
            $this->paseScript(__FUNCTION__, $name)
        );

        $process->run();

        if ($process->isSuccessful()) {
            return $this->parseOutput(
                $process->getOutput()
            );
        }

        return [];
    }

    // 解析相应参数
    protected function parseOutput($output)
    {
        $output = str_replace("'", '"', trim($output));

        return json_decode($output, true);
    }

    // 组合请求命令
    protected function paseScript(...$argv)
    {
        return implode(' ', array_merge(
            [$this->command, $this->path],
            $argv
        ));
    }

}
