<?php

//这是一个操作微信的类库
class vxModel{

    //此方法判断此次GET请求是否来自微信服务器，是则原样返回echostr参数的内容
    public function valid ()
    {
        //获取到开发者传过来的随机字符串
        $echostr = $_GET['echostr'];

        //判断是否来自微信服务器
        if ($this->checkSignature()) {

            //为真则原样输出echostr,并终止程序往下运行
            echo $echostr;
            exit;
        }
    }


    //检验signature的代码：
    private function  checkSignature ()
    {
        //判断是否定义了TOKEN,没有则抛出一个异常
        if ( !defined( "TOKEN" )) {
            throw new Exception( "TOKEN is not defined!" );
        }

        //拿到开发者能过get方式提交过来的数据

        //微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
        $signature = $_GET["signature"];

        //时间戳
        $timestamp = $_GET["timestamp"];

        //随机数
        $nonce = $_GET["nonce"];

        //声明TOKEN
        $token = TOKEN;

        //把开发者传过来的token,timestamp,nonce拼成一个数组
        $tmpArr = array($token, $timestamp, $nonce);

        //将token、timestamp、nonce三个参数进行字典序排序
        sort($tmpArr, SORT_STRING);

        //将三个参数字符串拼接成一个字符串
        $tmpArr = implode($tmpArr);

        //进行sha1加密
        $tmpArr = sha1($tmpArr);

        //判断加密后的字符串是否为开发者传过来的加密签名一致，一致则返回真，否则返加假
        if ($tmpArr == $signature) {

            return true;

        } else {

            return false;

        }
    }

    //微信发送消息，开发者服务器接收xml格式数据，然后进行业务的逻辑处理
    public function responseMsg ()
    {

        $postStr = file_get_contents('php://input');
        file_put_contents("./a.txt",$postStr);

        //判断$postStr是否不为空
        if (!empty($postStr)) {

            //禁用加载外部实体的能力
            libxml_disable_entity_loader(true);

            //接收到微信服务器发送过来的xml数据：分为：时间、消息，按照 msgType 分，转换为对象
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

            //开发者微信号
            $tousername = $postObj->ToUserName;

            //发送方帐号（一个OpenID）
            $fromusername = $postObj->FromUserName;

            //消息类型
            $msgtype = $postObj->MsgType;

            //消息内容
            $keyword = trim($postObj->Content);

            //判断消息类型是否为文本类型
            if ($msgtype == 'text') {

                //判断关键字，根据关键字自定义回复内容
                if ($keyword == '图文') {
                    //此处写死只是为了测试，建议从数据库里拿数据
                    $arr = array(

                        array(
                            'title' => "套路太深！唯品会对清空微博作出解释 网友：这广告6到飞",
                            'date' => "2017-6-2",
                            'url' => "http://www.chinaz.com/news/quka/2017/0602/715449.shtml",
                            'description' => '日前，唯品会清空了官方微博，成功的引起了众人的注意。',
                            'picUrl' => "http://upload.chinaz.com/2017/0602/6363201407728157524057839.jpeg"
                        ),
                        array(
                            'title' => "刘强东章泽天向中国人民大学捐赠3亿 设人大京东基金",
                            'date' => "2017-6-2",
                            'url' => "http://www.chinaz.com/news/2017/0602/715434.shtml",
                            'description' => '京东集团创始人、董事局主席兼首席执行官及京东集团今天下午在中国人民大学宣布',
                            'picUrl' => "http://upload.chinaz.com/2017/0602/6363201407728157524057839.jpeg"
                        ),
                        array(
                            'title' => "高通发布 QC 4+ 快充技术，让努比亚 Z17 当了一次“业界领先”",
                            'date' => "2017-6-2",
                            'url' => "http://www.chinaz.com/mobile/2017/0602/715429.shtml",
                            'description' => '充电 5 分钟，通话 2 小时这句广告词',
                            'picUrl' => "http://upload.chinaz.com/2017/0602/6363201407728157524057839.jpeg"
                        )
                    );

                    //拼接一个xml格式的图文模版，以便返回给微信
                    $textTpl = <<<EOT
                                <xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <ArticleCount>%s</ArticleCount>
                                <Articles>
EOT;

                    $str = "";
                    //循环拼接(多条数据)
                    foreach ($arr as $v) {
                        $str .= "<item>";
                        $str .= "<Title><![CDATA[" . $v['title'] . "]]></Title>";
                        $str .= "<Description><![CDATA[" . $v['description'] . "]]></Description>";
                        $str .= "<PicUrl><![CDATA[" . $v['picUrl'] . "]]></PicUrl>";
                        $str .= "<Url><![CDATA[" . $v['url'] . "]]></Url>";
                        $str .= "</item>";
                    }
                    $textTpl .= $str;
                    $textTpl .= "</Articles></xml>";

                    //声明消息创建时间
                    $time = time();

                    //声明消息类型为图文类型
                    $msgtype = 'news';

                    //图文消息个数
                    $nums = count($arr);

                    //sprintf()函数就是把百分号（%）符号替换成一个作为参数进行传递的变量
                    $retStr  =  sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $nums);

                    //把拼接好的xml图文信息返回给微信
                    echo $retStr;
                }

                //判断关键字，根据关键字自定义回复内容
                if ($keyword == 'saber') {

                    $textTpl = <<<EOT
                                <xml>
                                <ToUserName><![CDATA[%s]]></ToUserName>
                                <FromUserName><![CDATA[%s]]></FromUserName>
                                <CreateTime>%s</CreateTime>
                                <MsgType><![CDATA[%s]]></MsgType>
                                <Image>
                                <MediaId><![CDATA[%s]]></MediaId>
                                </Image>
                                </xml>
EOT;
                    //声明消息创建时间
                    $time = time();

                    //声明消息类型为图文类型
                    $msgtype = 'image';

                    $mediaid = 'fcXiw2uHWM3YO3JpeoXyQKK0E_-dAfSz66SdTWnKp2DmPellqwn9ZmL1hTSSyV58';

                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $mediaid);

                    echo $retStr;

                }

                //判断关键字，根据关键字自定义回复内容:格式:如:广州天气
                if (substr($keyword,-6) == '天气') {

                    //拿天气前面的所有字符
                    $city = substr($keyword,0,-6);

                    //拿到指定城市的天气的信息
                    $str = $this->getWeather($city);

                    // 发送天气的消息
                    $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";

                    $time = time();
                    $msgtype = 'text';
                    $content = "温度:".$str['result']['today']['temperature']."\n"."天气:".$str['result']['today']['weather']."\n"."风向:".$str['result']['today']['wind']."\n"."气候:".$str['result']['today']['dressing_index']."\n"."建议:".$str['result']['today']['dressing_advice'];

                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $content);
                    echo $retStr;

                }

            }

            if (substr($keyword,-2) == "IP") {

            	preg_match("/^([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})IP$/",$keyword,$ip); 
            	$myip = $ip[0];

            	$str = $this->getMyip($myip);

            	$textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
               	$time = time();
               	$msgtype = 'text';
               	$content = "IP:".$str['data']['ip']."\n"."地址:".$str['data']['country'].$str['data']['region'].$str['data']['city']."\n"."营运商:".$str['data']['isp'];
               	$retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $content);
               	echo $retStr;

            }


            //判断是否发生了事件推送
            if ($msgtype == 'event') {

               //拿到事件类型
                $even = $postObj->Event;

                //判断是否为subscribe(订阅)
                if ($even == 'subscribe') {

                    // 订阅后，发送的文本消息
                    $textTpl = "<xml>
                            <ToUserName><![CDATA[%s]]></ToUserName>
                            <FromUserName><![CDATA[%s]]></FromUserName>
                            <CreateTime>%s</CreateTime>
                            <MsgType><![CDATA[%s]]></MsgType>
                            <Content><![CDATA[%s]]></Content>
                            <FuncFlag>0</FuncFlag>
                            </xml>";
                    $time = time();
                    $msgtype = 'text';
                    $content = "欢迎来到装逼阁";

                    $retStr = sprintf($textTpl, $fromusername, $tousername, $time, $msgtype, $content);
                    echo $retStr;
                }
            }
        }
    }

    //curl请求，获取返回的数据
    public function getData ($url, $method='GET', $arr='')
    {
        //初始化curl
        $ch = curl_init();

        //需要获取的URL地址
        curl_setopt($ch, CURLOPT_URL, $url);

        //将 curl_exec() 获取的信息以文件流的形式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        //禁用后cURL将终止从服务端进行验证。
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        //strtoupper($method)把传递过来的method转为大写
        if (strtoupper($method) == 'POST') {

            //启用时会发送一个常规的POST请求
            curl_setopt($ch, CURLOPT_POST, 1);

            //全部数据使用HTTP协议中的"POST"操作来发送
            curl_setopt($ch, CURLOPT_POSTFIELDS, $arr);
        }

        //执行cURL请求
        $ret = curl_exec($ch);

        //关闭资源
        curl_close($ch);

        return $ret;
    }

    //JSON数据转为数组
    public function jsonToArray ($json)
    {
        $arr = json_decode($json,1);

        return $arr;
    }


    public function getAccessToken ()
    {
        //开启session
        session_start();

        if (isset($_SESSION['access_token']) && (time() - $_SESSION['expire_time'] < 7000)) {

            //返回access_token
            return $_SESSION['access_token'];

        } else {

            //测试号信息
            $appid = "wx80745c16a979730f";
            $appsecret = "0a1a541a1bacd28fac0211b89de8fbca";

            //获取access_token
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$appsecret;

            //把获取access_token时返回的JSON数据转成数组，并拿到access_token的值赋给$access_token
            $access_token = $this->jsonToArray($this->getData($url))[access_token];

            //把获取到的access_token写入session
            $_SESSION['access_token'] = $access_token;

            //把请求时的时间也存进session
            $_SESSION['expire_time'] = time();

            //返回access_token
            return $access_token;
        }
    }

    //获取到城市的天气
    public function getWeather ($city)
    {
        //获取天气平台需要用到的key
        $appkey = '3d92eb3623d5cc1ec6c85f596cc58054';

        //获取天气接口
        $url = "http://v.juhe.cn/weather/index?format=2&cityname=".$city."&key=".$appkey;

        //拿到天气接口返回的数据并返回
        return $this->jsonToArray($this->getData($url));
    }

    //查询IP
    public function getMyip ($Myip)
    {

    	//获取IP地址
    	$url = "http://ip.taobao.com//service/getIpInfo.php?ip=".$Myip;

    	//返回数据
    	return $this->jsonToArray($this->getData($url));

    }

}
