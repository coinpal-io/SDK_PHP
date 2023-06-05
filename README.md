<h1 align="center"> COINPAL PAY </h1>
<p align="center"> coinpal payment SDK for PHP</p>
<h3 align="center"> <a target="_blank" href="https://gitee.com/coinpal/docs">文档地址</a> </h3>

## 安装
```php
$ composer require coinpal/coinpal-php
```
## 配置
配置信息 以及实例化
```php
$config = [
    'debug'=>true,//调试模式
    'version'=>'2',//版本号
    'merchantNo'=>'商户编号',
    'apiKey'=>'商户秘钥',
];
$payment = new \coinpal\Payment();
```
## 发起交易
```php

    function getRequestId(){
        return 'Q'.date('YmdHis').uniqid();
    }

    function orderNo() {
        return 'D'.sprintf( '%04x%04x%04x%04x',
                mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
                mt_rand( 0, 0xffff ),
                mt_rand( 0, 0x0fff ) | 0x4000
            );
    }


try{
    $data['requestId'] = getRequestId();//请求流水号，每次请求需唯一
    $data['merchantName'] = 'Cionpal';//收银台页面商户名
    $data['orderNo'] = orderNo();
    $data['orderCurrencyType'] = 'fiat';//币种类型 crypto 或者 fiat
    $data['orderCurrency'] = 'USD';//订单币种
    $data['orderAmount'] = '10.5';//订单金额
    $data['orderDescription'] = 'Iphone 14';//订单描述 将用于特定收银台，页面展示
    $data['payerIP'] = '192.168.0.1';//付款人设备IP
    $data['notifyURL'] = 'https://www.order-test.cn/notify.php?order='.$data['orderNo'];//商户异步通知地址
    $data['redirectURL'] = 'https://www.order-test.cn';//用户支付成功/支付过期后，前台页面回调地址。
    $data['remark'] = 'Remark';//扩展字段，商户可自行定义。支付成功后，会原样返回。
    $result =  $payment->setMerchantNo($config['merchantNo'])->setVersion($config['version'])->setApiKey($config['apiKey'])->create($data);
    
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    //保存gcid 和支付链接 并且发送到前端调用该地址
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();

}
```

## 交易查询
```
$payment = new \coinpal\Payment();
try{
    $gcid = $result['reference'];//创建交易成功只有返回
    $list =  $payment->setMerchantNo($config['merchantNo'])->query($gcid);
    print_r($list);
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```
## 异步通知
```
try{
    //所有的状态更新都可以在这里操做
    $params =  $payment->setMerchantNo($config['merchantNo'])->setApiKey($config['apiKey'])->notify();
    //业务逻辑查找当前订单交易情况
    if($params['status'] == 'paid'){
        //订单支付成功
        //更新状态操做

    }
    $payment->log('支付成功');
    echo 'success';
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
```


