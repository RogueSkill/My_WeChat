<?php

    //引入操作微信的类
    include("./vxModel.php");

    //定义TOKEN
    define("TOKEN", "DNO");

    //实例化vxModel类
    $model = new vxModel();

    //检测echostr是否设置
    if (isset($_GET['echostr'])) {

        //调用valid方法判断此次GET请求是否来自微信服务器
        $model->valid();
        
    }else{
        $model->responseMsg();
    }

   //  $arr = $model->getMyip('192.168.35.1');
   // echo '<pre>';
   //  print_r($arr);

    $keyword = "IP192.168.35.35";
    preg_match("/^IP([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})$/",$keyword,$ip); 

    echo $ip[1];







