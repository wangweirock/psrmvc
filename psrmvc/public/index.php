<?php

//我们要写一个超牛逼的框架，我们没有其他目的，只是为了装逼
//我们要盖房子，我们要生孩子

//我们的目录结构要让别人看不懂，要深入。
//app:放置你的业务逻辑，这里面有mvc三个层。
//bootstrap: 整个框架启动运行前的准备工作都放在这里
//cache: 这里是项目运行中的缓存文件相关的
//config: 这里是配置文件存放的目录
//public : 这里是我们的项目的入口目录，也是公共目录，也是我们网站的根目录，是为了安全。
//vendor: 包目录，就是工具类，特定的功能的类。
//vendor/bob: 供应商名称
//vendor/bob/framework : 具体的包目录
//vendor/bob/message : 具体的包目录
//vendor/bob/framework/src : 包的源代码目录 [核心]，
//vendor/bob/framework/unit : 包的单元测试目录
//vendor/bob/framework/demo : 包的示例代码

//vendor包的目录结构以及自动加载流程叫做一种规范，psr-4自动加载规范
//vendor/bob/framework/src/Mysqli.php  framework;  

//vendor/db/Mysqli.php  vendor\db   目录结构与命名空间完全对应是psr-0规范
//vendor/db/Pdo.php
//定义项目的绝对根目录
define('BASE_PATH', '../');

try{
    //加载启动文件类
    include BASE_PATH.'bootstrap/App.php';

    //程序开始运行，点火
    App::run();
    App::test();
}catch (Exception $e){
    print_r($e->getMessage());
}





