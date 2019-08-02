# jobpanel

Laravel 队列管理

![队列](./assets/queue.png '队列')

![Supervisor](./assets/supervisor.png '进程')

# install

`composer require surges/jobpanel`

## providers

`Jobpanel\JobpanelServiceProvider::class`

## Facades

`Jobpanel\Facades\Jobpanel`
`Jobpanel\Facades\JobFailedManager`
`Jobpanel\Facades\Supervisor`

## 发布配置文件

`php artisan vendor:publish`

# 支持队列链接类型

`redis`、`database`

## Supervisor

配置文件见开启 `inet_http_server = 127.0.0.1:9001`
