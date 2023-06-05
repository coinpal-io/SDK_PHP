<h1 align="center"> COINPAL PAY </h1>
<p align="center"> coinpal payment SDK for PHP</p>
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
    'debug'=>true,//debug mode
    'version'=>'2',//version
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


try{
    $data['requestId'] = getRequestId();//Request serial number, unique for each request
    $data['merchantName'] = 'Cionpal';//Cash register page merchant name
    $data['orderNo'] = orderNo();
    $data['orderCurrencyType'] = 'fiat';//Currency type crypto or fiat
    $data['orderCurrency'] = 'USD';//Order Currency
    $data['orderAmount'] = '10.5';//Order Amount
    $data['orderDescription'] = 'Iphone 14';//Order description will be used for a specific cashier, page display
    $data['payerIP'] = '192.168.0.1';//Payer device IP
    $data['notifyURL'] = 'https://www.order-test.cn/notify.php?order='.$data['orderNo'];//Merchant asynchronous notification address
    $data['redirectURL'] = 'https://www.order-test.cn';//After the user's payment is successful/expired, the front page callback address.
    $data['remark'] = 'Remark';//The extended field can be defined by the merchant. After the payment is successful, it will be returned as it is.
    $result =  $payment->setMerchantNo($config['merchantNo'])->setVersion($config['version'])->setApiKey($config['apiKey'])->create($data);
    echo "<pre>";print_r($result);echo "</pre>";
    //Save the gcid and payment link and send it to the front end to call the address
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //log error message
    echo $e->getMessage();

}
```

## Transaction inquiry
```
$payment = new \coinpal\Payment();
try{
    $gcid = $result['reference'];//Only returns if the transaction is successfully created
    $list =  $payment->setMerchantNo($config['merchantNo'])->query($gcid);
    print_r($list);
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //log error message
    echo $e->getMessage();
}
```
## asynchronous notification
```
try{
    //All status updates can be done here
    $params =  $payment->setMerchantNo($config['merchantNo'])->setApiKey($config['apiKey'])->notify();
    //Business logic to find the current order transaction status
    if($params['status'] == 'paid'){
        //Order paid successfully
        //update state operation

    }
    $payment->log('payment successful');
    echo 'success';
}catch ( \coinpal\PaymentException $e){
    $payment->log($e);
    //log error message
    echo $e->getMessage();
}
```


