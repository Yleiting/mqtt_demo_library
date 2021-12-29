<?php
// 填写服务参数
// 1、client_id，clientID，从环信console【应用概览】->【应用详情】->【开发者ID】下 "client ID"获取
// 2、client_secret，clientSecret，从环信console【应用概览】->【应用详情】->【开发者ID】下"clientSecret"获取
// 3、rest_uri，RSET API地址，从环信console【MQTT】->【服务概览】->【服务配置】下"REST API地址"获取
// 4、app_id，从环信console【MQTT】->【服务概览】->【服务配置】下"AppId"获取
$config = [
    'rest_uri' => 'XXXXXX',
    'client_id' => 'XXXXXX',
    'client_secret' => 'XXXXXX',
    'app_id' => 'XXXXXX',
];

// 实时 token
 $accessToken = get_access_token();

// 固定值，有有效期
//$accessToken = 'YWMtiftbBF7sEeyeASnTGg_ZZCGtXR4YNTAxtZpP1MjdlZbv64ppqWZOEI663pDy48tKAgMAAAF9xoOlvAWP1ADm__IWx_b4TLJvCb9axcY6cNImjMXJcx1ty7UK-Ked2w';

// 发送消息
$message = [
    'type' => 'tts_dynamic',
    'msgid' => 'c1b5d5f46092d4c01a5f422ae2b9ad41188',
    'txt' => '测试测试'
];
var_dump(send(['861714050059769'], $message));

/**
 * @description: 获取 Token
 * @return {String}
 */
function get_access_token()
{
    global $config;
    $uri = $config['rest_uri'] . '/openapi/rm/app/token';
    $body = [
        'appClientId' => $config['client_id'],
        'appClientSecret' => $config['client_secret'],
    ];
    $headers = [
        'Content-Type' => 'application/json',
    ];
    $ret = json_decode(curl_request($uri, $body, $headers), true);
    return isset($ret['code']) && $ret['code'] == 200 ? $ret['body']['access_token'] : $ret;
}

/**
 * @description: 发送消息
 * @param {array} $topics 要发消息的主题数组
 * @param {mixed} $message 要发送的消息内容
 * @param {String} $deviceID deviceID 用户自定义
 * @return {array}
 */
function send($topics, $message, $deviceID = '12')
{
    global $config, $accessToken;
    $uri = $config['rest_uri'] . '/openapi/v1/rm/chat/publish';
    $body = [
        'topics' => $topics,
        'clientid' => "{$deviceID}@{$config['app_id']}",
        'payload' => base64_encode(iconv("UTF-8", "GBK", json_encode($message, JSON_UNESCAPED_UNICODE))),
        'encoding' => 'base64',
    ];
    
    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => $accessToken,
    ];
    $ret = json_decode(curl_request($uri, $body, $headers), true);
    return $ret;
}

/**
 * @description: 查看消息
 * @param {String} $messageId 指定的消息ID
 * @return {array}
 */
function show($messageId)
{
    global $config, $accessToken;
    $uri = $config['rest_uri'] . '/openapi/rm/message/message?messageId=' . $messageId;
    $headers = [
        'Content-Type' => 'application/json',
        'Authorization' => $accessToken,
    ];
    $ret = json_decode(curl_request($uri, null, $headers), true);
    if (isset($ret['code']) && $ret['code'] == 200) {
        $ret['body']['message'] = json_decode(iconv('GBK', 'UTF-8', base64_decode($ret['body']['message'])), true);
        return $ret['body'];
    }
    return $ret;
}

function curl_request($url, $data = null, $headers = null)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    // CURLOPT_HEADER => true,             // 将头文件的信息作为数据流输出
    // CURLOPT_NOBODY => false,            // true 时将不输出 BODY 部分。同时 Mehtod 变成了 HEAD。修改为 false 时不会变成 GET。
    // CURLOPT_CUSTOMREQUEST => $request->method,  // 请求方法
	if(!empty($data)){
		curl_setopt($ch, CURLOPT_POST, 1);
        if (is_array($data)) {
            $data = json_encode($data);
        }
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}
    if(!empty($headers)){
        curl_setopt($ch, CURLOPT_HTTPHEADER, buildHeaders($headers));
    }
	$output = curl_exec($ch);
    // $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    // $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	curl_close($ch);
	return $output;
}

function buildHeaders($headers)
{
    $headersArr = array();
    foreach ($headers as $key => $value) {
        array_push($headersArr, "{$key}: {$value}");
    }
    return $headersArr;
}