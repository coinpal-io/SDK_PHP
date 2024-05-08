<?php
    error_reporting(0);
    header('Content-Type: application/json');
    $res = ['error' => ''];
    $host = $_POST['host'];
    $database = $_POST['database'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $merchantNo = $_POST['merchantNo'];
    $apiKey = $_POST['apiKey'];

    if (empty($host) || empty($username)) {
        $res['error'] = 'Request data empty.';
        echo json_encode($res, true);
        return;
    }

    $mysqlConnect = mysqli_connect($host, $username, $password, $database);

    if (!$mysqlConnect) {
        $res['error'] = 'Database connection failed. Please check the configuration information and try again.';
        echo json_encode($res, true);
        return;
    }


// 将配置信息写入 PHP 文件
    $configContent = <<<EOD
<?php
\$config = [
    'debug' => true, // Debug mode
    'version' => '2', // Interface version number
    'merchantName' => 'CoinPal', // Merchant name displayed on the cash register page
    'initTag' => true, // Initialization Tag
    'base_url' => 'https://pay.coinpal.io', // CoinPal payment url
    'merchantNo' => '{$merchantNo}',
    'apiKey' => '{$apiKey}',
    'db_host' => '{$host}',
    'db_name' => '{$database}',
    'db_user' => '{$username}',
    'db_pass' => '{$password}',
];

EOD;
    $configFile = 'config.php';
    file_put_contents($configFile, $configContent);
    echo json_encode($res, true);
    return;



