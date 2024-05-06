<?php
$a = ['aaa' => 11];

echo json_encode($a, true);
die();
if (1==1) {
    $config = [
        'debug' => true, // Debug mode
        'version' => '2', // Interface version number
        'merchantName' => 'CoinPal', // Merchant name displayed on the cash register page
//        'base_url' => 'https://pay.coinpal.io', // CoinPal payment url
//        'merchantNo' => '100000000',
//        'apiKey' => 'a67330b7da3e5f2e41cefbd0a984f22aac88c37a8f1222f567a93decdf0dcef2',
        'base_url'=>'https://pay-dev.coinpal.io',
        'merchantNo'=>'10290001',
        'apiKey'=>'bc92f67dcd9e2300f64c9716fef5d35f7f27e965e309c8b0f75dca86c4876967',

        'db_host' => 'localhost',
        'db_name' => 'test',
        'db_user' => 'root',
        'db_pass' => 'root',
    ];
} else {
    $config = [
        'debug' => true, // Debug mode
        'version' => '2', // Interface version number
        'merchantName' => 'CoinPal', // Merchant name displayed on the cash register page
        'base_url' => 'https://pay.coinpal.io', // CoinPal payment url
        'merchantNo' => '100000000',
        'apiKey' => 'a67330b7da3e5f2e41cefbd0a984f22aac88c37a8f1222f567a93decdf0dcef2',
        'db_host' => 'localhost',
        'db_name' => 'test',
        'db_user' => 'root',
        'db_pass' => 'root11',
    ];
}