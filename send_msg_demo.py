import requests
import time
import json
import base64


# 填写服务参数
# 1、app_client_id，clientID，从环信console【应用概览】->【应用详情】->【开发者ID】下 "client ID"获取
# 2、app_client_secret，clientSecret，从环信console【应用概览】->【应用详情】->【开发者ID】下"clientSecret"获取
# 3、api_url_base，RSET API地址，从环信console【MQTT】->【服务概览】->【服务配置】下"REST API地址"获取
# 4、app_id，从环信console【MQTT】->【服务概览】->【服务配置】下"AppId"获取
app_client_id = ' XXXXX'

app_client_secret = 'XXXX'

api_url_base = 'XXXXX' 

app_id = 'XXXXXX'


# 播报文字
speak_text = '欢迎使用环信mqtt'


# 获取应用token
api_url_app_token = api_url_base + '/openapi/rm/app/token'
def get_app_token():
    data = {
        'appClientId':app_client_id,
        'appClientSecret':app_client_secret
    }
    
    header = {'Content-Type': 'application/json'}

    re = requests.post(api_url_app_token, headers=header, data=json.dumps(data))
    return (json.loads(re.text)['body']['access_token'])


# 发送mqtt消息
api_url_publish = api_url_base + '/openapi/v1/rm/chat/publish' 
def send_msg(app_token, txt):

    # 智能音箱的 msgid 每次都不一样才会播报声音
    # 这里用毫秒时间戳当作 msgid
    time_millis = int(round(time.time() * 1000))

    dat ={
    'type':'tts_dynamic',
    'msgid': time_millis, 
    'txt':txt , 
    }
    
    json_text = json.dumps(dat, ensure_ascii=False)
    json_h = json_text.encode(encoding="gbk") 
    base64_bytes = base64.b64encode(json_h)
    base64_utf8 = str(base64_bytes,'utf-8')

    #topics，要发送的主题
    #clientid,当前客户端ID，格式为“xxxx@appid”
    data = {
        'topics':['861714050059769'],
        'clientid':'12@ff6sc0',
        'payload':base64_utf8,
        "encoding":'base64',
        'qos':1,
        'retain':0,
        'expire':86400
    }

    header = {
        'Content-Type': 'application/json',
        'Authorization': app_token
    }

    re = requests.post(api_url_publish, headers=header, data=json.dumps(data))
    return (json.loads(re.text))


print('正在获取应用token...')
app_token = get_app_token()
print('获取应用token成功')

print(send_msg(app_token, speak_text))
print('发送消息成功')


