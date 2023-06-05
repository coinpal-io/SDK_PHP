<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \coinpal\Payment();
try{
    $gcid = $result['reference'];//创建交易成功只有返回
    $list =  $payment->setMerchantNo($config['merchantNo'])->query($gcid);
    print_r($list);
}catch ( \glocash\PaymentException $e){
    $payment->log($e);
    //记录错误信息
    echo $e->getMessage();
}
