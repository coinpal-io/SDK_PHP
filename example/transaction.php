<?php
include_once "../vendor/autoload.php";
include_once "./config.php";

$payment = new \coinpal\Payment();

try {
//    global $config;
    $data['requestId'] = getRequestId(); // Unique serial number for each request.
    $data['orderNo'] = orderNo(); // Merchant order number.
    $data['orderCurrencyType'] = 'fiat'; // Currency type: "fiat" (legal currency) or "crypto" (digital currency).
    $data['orderCurrency'] = 'USD'; // Order currency.
    $data['orderAmount'] = '10.5'; // Order amount.
    $data['notifyURL'] = 'https://www.coinpal.io/notification?order=' . $data['orderNo']; // Merchant's asynchronous notification URL.
    $data['redirectURL'] = 'https://www.coinpal.io/redirect?order=' . $data['orderNo']; // Callback address of the front page after successful/expired payment by the user.
    $data['payerIP'] = '192.168.0.1'; // Payer's device IP.
    $data['orderDescription'] = 'Iphone 14'; // Order description displayed on the cash register page.
    $data['remark'] = 'Remark'; // Extended field that can be defined by merchants. Will be returned as it is after the payment is successful.
    $result = $payment->create($data);
    echo "<pre>";
    print_r($data);
    echo "</pre>";
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    // The expected return of the following content
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
    if (empty($result['nextStepContent'])) {
        // After you request the interface, there is no response data, which may be not supported by the local IP. You need to place the request on the server. Note that the server in Chinese Mainland does not support the request.
        $payment->log('payment request error: ' . json_encode($result));
        return;
    }

    // The coinpal_payments table and coinpal_payment_history table here only provide an implementation idea. The specific table structure can be adjusted according to your project
    $database = new \coinpal\Database();
    $sql = $database->generateInsertSql('coinpal_payments', $result);
    $database->execSql($sql);
    $sql = $database->generateInsertSql('coinpal_payments_history', $result);
    $database->execSql($sql);
    $payment->log('payment response data: ' . json_encode($result));
    die();
    header('Location:'.$result['nextStepContent']);
    return;
} catch (\coinpal\PaymentException $e) {
    $payment->log($e);
    // Record error information
    echo $e->getMessage();
}

function getRequestId()
{
    return 'Q' . date('YmdHis') . uniqid();
}

function orderNo()
{
    return 'D' . sprintf('%04x%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000
        );
}
