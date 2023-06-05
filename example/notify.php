<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \coinpal\Payment();
try{
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
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
