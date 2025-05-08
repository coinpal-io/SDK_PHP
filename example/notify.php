<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \coinpal\Payment();
try {
    echo "<pre>"; print_r($_POST); echo "</pre>";
    $params = $payment->setMerchantNo($config['merchantNo'])->setApiKey($config['apiKey'])->notify();
    // Process the corresponding logic based on the state
    if ($params['status'] == 'paid' && $params['paidOrderAmount'] >= $params['orderAmount']) {
        // Order payment successful, update status operation instance
    }
    $payment->log('notify data: ' . json_encode($params));
    echo 'success';
} catch (\coinpal\PaymentException $e) {
    $payment->log($e);
    // Record error information
    echo $e->getMessage();
}
