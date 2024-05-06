<?php
include_once "../vendor/autoload.php";
include_once "./config.php";
$payment = new \coinpal\Payment();
try {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    $result = $payment->setMerchantNo($config['merchantNo'])->setApiKey($config['apiKey'])->notify();

//    $jsonData = '{
//  "version": "2",
//  "requestId": "COINPAL_NO1714453705766",
//  "merchantNo": "18",
//  "orderNo": "Dc79ca858529d4a5f",
//  "reference": "CWS6A15WZAEAPC17",
//  "orderCurrency": "USD",
//  "orderAmount": "2.31",
//  "paidOrderAmount": "0.00000000",
//  "selectedWallet": 9999,
//  "dueCurrency": "TRX",
//  "dueAmount": "19.367593",
//  "network": "TRC20",
//  "paidCurrency": "TRX",
//  "paidAmount": "19.367593",
//  "paidUsdt": 0,
//  "paidAddress": "TRMLw7wzdbRt9RmxrtGZ8shqaSVohFtPKm",
//  "confirmedTime": "",
//  "status": "paid",
//  "remark": "T24043005082525-1785174346290421762",
//  "unresolvedLabel": null,
//  "sign": "07e18933de46d2c2a7afad0385ac10d1d32f3c291308d068367fdf1f224d397e"
//}';
//    $result = json_decode($jsonData, true);

    $database = new \coinpal\Database();
    // Here is just a way of thinking, specific operational logic, and adjustments based on the project
    if ($result['status'] == 'paid') {
        $updated = date('Y-m-d H:i:s',time());
        $sql = "update coinpal_payments set `status`='{$result['status']}',`paidAmount`='{$result['paidAmount']}',`paidCurrency`='{$result['paidCurrency']}', paidUsdt='{$result['paidUsdt']}', updated='{$updated}', confirmedTime='{$result['confirmedTime']}' where orderNo = '{$result['orderNo']}'";
        $database->execSql($sql);
        $payment->log('payment response data: ' . json_encode($result));
    }
    $sql = $database->generateInsertSql('coinpal_payments_history', $result);
    $database->execSql($sql);
    $payment->log('notify data: ' . json_encode($result));
    echo 'success';
} catch (\coinpal\PaymentException $e) {
    $payment->log($e);
    // Record error information
    echo $e->getMessage();
}
