<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \coinpal\Payment();
$payment = $payment->setMerchantNo($config['merchantNo'])->setVersion($config['version'])->setApiKey($config['apiKey']);
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
    $result =  $payment->create($data);
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    //预期会返回下面的内容
    //
    /*
     * {
        "version": "2",
        "requestId": "20XXXXX",
        "merchantNo": "10XXXXX",
        "orderNo": "30XXXXX",
        "reference": "CWSXXXXXXXXXX",
        "orderCurrency": "USD",
        "orderAmount": "10.5",
        "nextStep": "redirect",
        "nextStepContent": "https://pay.coinpal.io/cashier/wallet/list/XXXXXXXXXXXXXXXXXXXXXXX",
        "status": "created",
        "respCode": 200,
        "respMessage": "success",
        "remark": "Remark"
        }
    */
    //
    //
    //保存gcid 和支付链接
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();

}

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
