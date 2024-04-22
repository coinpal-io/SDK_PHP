<h1 align="center"> CoinPal Payment </h1>
<p align="center"> CoinPal Payment SDK for PHP</p>
<h3 align="center"> <a target="_blank" href="https://gitee.com/coinpal/docs">document address</a> </h3>

## Install

After downloading SDK_PHP, enter the SDK_PHP directory and execute the following command

```php
$ composer update
```
## configuration
Configuration information and instantiation
```php
$config = [
    'debug'=>true,// debug mode
    'version'=>'2',// Interface version number
    'merchantName'=>'CoinPal',// Merchant name displayed on the cash register page
    'merchantNo'=>'Merchant ID',
    'apiKey'=>'merchant key',
];
$payment = new \coinpal\Payment();
```
## Initiate a transaction
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


    try {
        $payment = $payment->setMerchantNo($config['merchantNo'])->setVersion($config['version'])->setApiKey($config['apiKey'])->setMerchantName($config['merchantName'])->setBaseUrl($config['base_url']);
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
            $payment->log('payment request error: ' . json_encode($result));
            return;
        }
        $payment->log('payment response data: ' . json_encode($result));
        return;
    } catch (\coinpal\PaymentException $e) {
        $payment->log($e);
        // Record error information
        echo $e->getMessage();
    
    }

```

## Transaction inquiry
```
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
```
## asynchronous notification
```
try {
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    $params = $payment->setMerchantNo($config['merchantNo'])->setApiKey($config['apiKey'])->notify();
    // Process the corresponding logic based on the state
    if ($params['status'] == 'paid') {
        // Order payment successful, update status operation instance
        /*
            $host = 'localhost';
            $dbname = 'test';
            $username = 'root';
            $password = 'root';
            $dsn = "mysql:host=$host;dbname=$dbname";
            $pdo = new PDO($dsn, $username, $password);
            $sql = "update `order` set `status` = '{$params['status']}' where order_no = '{$params['orderNo']}'";
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        */
    }
    $payment->log('notify data: ' . json_encode($params));
    echo 'success';
} catch (\coinpal\PaymentException $e) {
    $payment->log($e);
    // Record error information
    echo $e->getMessage();
}
```
