<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \coinpal\Payment();
try {
    $gcid = $result['reference']; // Return after successfully creating transaction
    $list = $payment->setMerchantNo($config['merchantNo'])->query($gcid);
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
} catch (\glocash\PaymentException $e) {
    $payment->log($e);
    // Record error information
    echo $e->getMessage();
}
