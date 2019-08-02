from xmlrpc.client import ServerProxy

import sys

server = ServerProxy('http://localhost:9001/RPC2')


def getState():  # 状态
    print(server.supervisor.getState())


def getAllProcessInfo():  # 进程列表
    print(server.supervisor.getAllProcessInfo())


def restart():  # 重启主进程
    print(server.supervisor.restart())


def shutdown():  # 终止主进程
    print(server.supervisor.shutdown())


def getPID():  # 获取主进程ID
    print(server.supervisor.getPID())


def startProcess(name):  # 启动进程
    print(server.supervisor.startProcess(name))


def startProcessGroup(name):  # 启动进程组
    print(server.supervisor.startProcessGroup(name))


def stopProcess(name):  # 终止进程
    print(server.supervisor.stopProcess(name))


def stopProcessGroup(name):  # 终止进程组
    print(server.supervisor.stopProcessGroup(name))


if __name__ == '__main__':
    args = sys.argv
    if args[1] == "getState":
        getState()
    elif args[1] == 'getAllProcessInfo':
        getAllProcessInfo()
    elif args[1] == 'restart':
        restart()
    elif args[1] == 'shutdown':
        shutdown()
    elif args[1] == 'getPID':
        getPID()
    elif args[1] == 'startProcess':
        startProcess(args[2])
    elif args[1] == 'startProcessGroup':
        startProcessGroup(args[2])
    elif args[1] == 'stopProcess':
        stopProcess(args[2])
    elif args[1] == 'stopProcessGroup':
        stopProcessGroup(args[2])
    else:
        getState()
