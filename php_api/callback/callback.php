<?php
include_once "WXBizMsgCrypt.php";
header("Content-type: text/html; charset=utf-8");

global $RX_UserID;

function curlPost($url,$data=""){
    $ch = curl_init();
    $opt = array(
			CURLOPT_URL     => $url,
            CURLOPT_HEADER  => 0,
			CURLOPT_POST    => 1,
            CURLOPT_POSTFIELDS      => $data,
            CURLOPT_RETURNTRANSFER  => 1,
            CURLOPT_TIMEOUT         => 20
    );
    $ssl = substr($url,0,8) == "https://" ? TRUE : FALSE;
    if ($ssl){
        $opt[CURLOPT_SSL_VERIFYHOST] = 1;
        $opt[CURLOPT_SSL_VERIFYPEER] = FALSE;
    }
    curl_setopt_array($ch,$opt);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function sendmsg($result){
    //再次回复消息
    //主动给企业微信发消息
    $out=shell_exec("python3 /var/www/html/QA_Robot/FAQrobot.py $result");
    //$out=shell_exec("python3 /var/www/html/QA_Robot/test000.py $result");
    $out=str_replace(array("\r\n", "\r", "\n"), "", $out);

    $corpid="wwa5115c816b589aec";
    $corpsecret="gnY53Ug2ADwX9OjKfeI9yO3XB0k8vVlcsUK4pWvHiVk";
    $Url="https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=$corpid&corpsecret=$corpsecret";
    $res = curlPost($Url);
    $ACCESS_TOKEN=json_decode($res)->access_token;
    $Url="https://qyapi.weixin.qq.com/cgi-bin/message/send?access_token=$ACCESS_TOKEN";
    $msg=$out;

    $data="{\"touser\":\"$RX_UserID\",\"msgtype\":\"text\",\"agentid\":1000003,\"text\":{\"content\":\"$msg\"},\"safe\":0}";
    $res = curlPost($Url,$data);
    $errmsg=json_decode($res)->errmsg;
    if($errmsg==="ok"){
	    return "发送成功！";
    }else{
	    return "发送失败，".$errmsg;
    }
}

function getmessages(){
	$encodingAesKey = "EhlfyI2H0HAqbDTbFDBrUhuwXNRQtwlY0wFiQeELuA3";
    $token = "ClxRuztxYdd279ZyRAD66ffvBu";
    $corpId = "wwa5115c816b589aec";

	$sVerifyMsgSig = $_GET["msg_signature"];
	$sVerifyTimeStamp = $_GET["timestamp"];
	$sVerifyNonce = $_GET["nonce"];
	//①读取POST数据，并且返回加密后的XML格式文本。
	$postStr = file_get_contents("php://input");
    $sMsg = "";
    //②解密XML数据
	$wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
	$errCode = $wxcpt->DecryptMsg($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $postStr, $sMsg);
	//解密成功
    if ($errCode == 0) {
		//③将解密的XML数据$sMag返回成对象
		$postObj0 = simplexml_load_string($sMsg,'SimpleXMLElement', LIBXML_NOCDATA);
		$postObj1 = json_encode($postObj0);
    	$postObj = json_decode($postObj1);
		//返回消息格式  消息格式有text image voice location 等具体可查看官方文档，这里就演示两个text和image。
		$RX_TYPE=trim($postObj->MsgType);
		//成员UserID
		$RX_UserID=trim($postObj->FromUserName);
		//返回发送消息的企业id
		$RX_UserName=trim($postObj->ToUserName);
		//返回发送消息时间戳
		$RX_CreateTime=time();
		//如果是消息类型返回消息内容
		$RX_Content=trim($postObj->Content);
		//如果是图片类型保存 picurl
        $RX_PicUrl=trim($postObj->PicUrl);


		switch ($RX_TYPE)
    	{
        	case "text":    //文本消息
				$result = $RX_Content;
            	break;
        	default:
            	$result = "未知错误";
            	break;
        }
        //先返回一个空串消息，这个消息在回复中不显示
        $oout="请稍候......";

		//回复消息
		//需要发送的密文数据
		//全部转换为cotent类型
/*
处理后的得到的明文：
<xml><ToUserName><![CDATA[wx5823bf96d3bd56c7]]></ToUserName>
<FromUserName><![CDATA[mycreate]]></FromUserName>
<CreateTime>1409659813</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[hello]]></Content>
<MsgId>4561255354251345929</MsgId>
<AgentID>218</AgentID>
</xml>
*/
/*
假设企业需要回复用户的明文如下：
<xml>
<ToUserName><![CDATA[mycreate]]></ToUserName>
<FromUserName><![CDATA[wx5823bf96d3bd56c7]]></FromUserName>
<CreateTime>1348831860</CreateTime>
<MsgType><![CDATA[text]]></MsgType>
<Content><![CDATA[this is a test]]></Content>
<MsgId>1234567890123456</MsgId>
<AgentID>128</AgentID>
</xml>
*/
		$sRespData = "<xml>
		<ToUserName><![CDATA[$RX_UserID]]></ToUserName>
		<FromUserName><![CDATA[$RX_UserName]]></FromUserName>
		<CreateTime>$RX_CreateTime</CreateTime>
		<MsgType><![CDATA[$RX_TYPE]]></MsgType>
		<Content><![CDATA[$oout]]></Content>
		<MsgId>1234567890123456</MsgId>
		<AgentID>1000003</AgentID>
		</xml>";
		$sEncryptMsg = ""; //xml格式的密文
		//进行加密
		$errCode = $wxcpt->EncryptMsg($sRespData, $sVerifyTimeStamp, $sVerifyNonce, $sEncryptMsg);
		if ($errCode == 0) {
			print("done \n");
            echo $sEncryptMsg;
            return $result;
	    } else {
            print("ERR: " . $errCode . "\n\n");
        }
	}//endif
	else{
		exit(0);
    }
}

if (!isset($_GET['echostr'])) {
    $getresult=getmessages();
    //再次主动发送消息
    $sendmessage=sendmsg($getresult);
}else{
    $encodingAesKey = "EhlfyI2H0HAqbDTbFDBrUhuwXNRQtwlY0wFiQeELuA3";
    $token = "ClxRuztxYdd279ZyRAD66ffvBu";
    $corpId = "wwa5115c816b589aec";
	//接受验证数据
	$sVerifyMsgSig = $_GET['msg_signature'];
    $sVerifyTimeStamp = $_GET['timestamp'];
    $sVerifyNonce = $_GET['nonce'];
    $sVerifyEchoStr = $_GET['echostr'];

    // 需要返回的明文
    $sEchoStr = "";

    $wxcpt = new WXBizMsgCrypt($token, $encodingAesKey, $corpId);
    $errCode = $wxcpt->VerifyURL($sVerifyMsgSig, $sVerifyTimeStamp, $sVerifyNonce, $sVerifyEchoStr, $sEchoStr);
    if ($errCode == 0) {
        echo $sEchoStr; // 验证URL成功，将sEchoStr返回
    } else {
	    print("ERR: " . $errCode . "\n\n");
    }
}
?>