<?php

namespace coinpal;

class Payment
{
    protected $debug = true;
    private $merchantNo = "";
    private $apiKey = "";
    private $version = "";
    private $merchantName = "";
    const LIVE_URL = 'https://pay.coinpal.io';
    const PAYMENT_URL = '/gateway/pay/checkout';
    const QUERY_URL = '/gateway/pay/query';
    protected $base_url = "";


    /**
     * @param $base_url
     * @return $this
     */
    public function setBaseUrl($base_url): Payment
    {
        //预留设置url入口 防止地址变化
        $this->base_url = $base_url;
        return $this;
    }


    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     * @return Payment
     */
    public function setDebug(bool $debug): Payment
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * @param string $merchantNo
     * @return Payment
     */
    public function setMerchantNo(string $merchantNo): Payment
    {
        $this->merchantNo = $merchantNo;
        return $this;
    }

    /**
     * @param string $merchantName
     * @return Payment
     */
    public function setMerchantName(string $merchantName): Payment
    {
        $this->merchantName = $merchantName;
        return $this;
    }


    /**
     * @param string $version
     * @return Payment
     */
    public function setVersion(string $version): Payment
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string $apiKey
     * @return Payment
     */
    public function setApiKey(string $apiKey): Payment
    {
        $this->apiKey = $apiKey;
        return $this;
    }

    private function getURl($url): string
    {
        $baseUrl = self::LIVE_URL;
        $baseUrl = empty($this->base_url) ? $baseUrl : $this->base_url;
        return $baseUrl . $url;
    }

    private function checkMerchantConfig()
    {
        if (empty($this->merchantNo) || empty($this->apiKey) || empty($this->version)) {
            throw new PaymentException(PaymentException::SIGN_NULL, PaymentException::CODE_UNAUTHORIZED);
        }
    }


    public function create($data)
    {
        $this->log('----------------create transaction--------------------------');
        $this->checkMerchantConfig();
        $url = $this->getURl(self::PAYMENT_URL);
        $data['version'] = $this->version;
        $data['merchantNo'] = $this->merchantNo;
        if (!empty($this->merchantName)) {
            $data['merchantName'] = $this->merchantName;
        }
        if (!isset($data['orderNo']) || empty($data['orderNo'])) {
            throw new PaymentException(PaymentException::ORDER_NO_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        if (!isset($data['orderCurrencyType']) || empty($data['orderCurrencyType'])) {
            throw new PaymentException(PaymentException::ORDER_CURRENCY_TYPE_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        if (!isset($data['orderAmount']) || empty($data['orderAmount'])) {
            throw new PaymentException(PaymentException::ORDER_AMOUNT_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        if (!isset($data['orderCurrency']) || empty($data['orderCurrency'])) {
            throw new PaymentException(PaymentException::ORDER_CURRENCY_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        if (!isset($data['payerIP']) || empty($data['payerIP'])) {
            throw new PaymentException(PaymentException::PAYER_IP_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        if (!isset($data['notifyURL']) || empty($data['notifyURL'])) {
            throw new PaymentException(PaymentException::NOTIFY_URL_MUST, PaymentException::CODE_BAD_REQUEST);
        }
        $data['sign'] = $this->makeSign($data);
        $this->log('request url: ' . $url);
        $this->log('request data: ' . json_encode($data));
        $result = $this->curl_request($url, $data);
        $this->log('response data: ' . $result);
        $result = json_decode($result, true);
        if (isset($result['REQ_ERROR']) && empty(!$result['REQ_ERROR'])) {
            throw new PaymentException($result['REQ_ERROR'], PaymentException::CODE_REQUEST_FAILED);
        }
        return $result;
    }

    public function query(string $gcid)
    {
        $this->log('----------------query--------------------------');
        $data['gcid'] = $gcid;

        if (!isset($data['gcid']) || empty($data['gcid'])) {
            throw new PaymentException(PaymentException::GCID_MUST, PaymentException::CODE_BAD_REQUEST);
        }

        $url = $this->getURl(self::QUERY_URL);
        $result = $this->curl_request($url, $data);
        $this->log('response data: ' . $result);
        if (empty($result)) {
            return [];
        }
        return $result;
    }


    public function notify()
    {
        $this->log('----------------notify--------------------------');
        $params = $_POST;
        $this->verifySign($params);
        $this->log('notify: ' . json_encode($params));
        $status = $params['status'] ?? '';
        $gcid = $params['reference'] ?? '';
        if (empty($status) || empty($gcid)) {
            throw new PaymentException(PaymentException::PARAM_ERROR, PaymentException::CODE_SERVER_ERRORS);
        }
        return $params;
    }

    public function verifySign($params)
    {
        if (empty($params['sign'])) {
            throw new PaymentException(PaymentException::SIGN_ERROR, PaymentException::CODE_UNAUTHORIZED);
        }
        $sign = $this->makeSign($params);
        if ($sign != $params['sign']) {
            throw new PaymentException(PaymentException::SIGN_ERROR, PaymentException::CODE_UNAUTHORIZED);
        }
        return true;
    }

    protected function makeSign($data)
    {
        $signString = $this->apiKey . $data['requestId'] . $data['merchantNo'] . $data['orderNo'] . $data['orderAmount'] . $data['orderCurrency'];
        $this->log('sign string：' . $signString);
        return hash('sha256', $signString);
    }


    protected function curl_request($url, $data = null, $method = 'post', $https = true)
    {
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($https === true) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            }
            if ($method === 'post') {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            $result = curl_exec($ch);
            if ($result === false) {
                return curl_error($ch);
            }
            curl_close($ch);
            return $result;
        } catch (\Exception $e) {
            throw new PaymentException($e->getMessage(), PaymentException::CODE_SERVER_ERRORS);
        }
    }

    public function log($data)
    {
        if (!self::isDebug()) {
            return true;
        }
        $data = is_array($data) ? var_export($data, true) : $data;
        file_put_contents('./coinpal.log', $data . PHP_EOL, FILE_APPEND);
    }
}

class PaymentException extends \Exception
{

    /**
     * 正常返回
     */
    CONST CODE_SUCCESS = 200;

    /**
     * 参数缺失
     */
    CONST CODE_BAD_REQUEST = 400;

    /**
     * 验证失败
     */
    CONST CODE_UNAUTHORIZED = 401;

    /**
     * 请求业务类型错误
     */
    CONST CODE_REQUEST_FAILED = 402;

    /**
     * 接口限制
     */
    CONST CODE_FORBIDDEN = 403;

    /**
     * 资源不存在
     */
    CONST CODE_NOT_FOUND = 404;

    /**
     * 系统内部错误
     */
    CONST CODE_SERVER_ERRORS = 500;

    const SIGN_NULL = 'merchant no or apiKey is null';
    const ORDER_NO_MUST = 'order no must';
    const ORDER_CURRENCY_TYPE_MUST = 'order currency type must';
    const ORDER_AMOUNT_MUST = 'order amount is must';
    const ORDER_CURRENCY_MUST = 'order currency is must';
    const PAYER_IP_MUST = 'payer IP is must';
    const NOTIFY_URL_MUST = 'notify url is must';
    const GCID_MUST = 'gcid is must';
    const AMOUNT_GT0 = 'amount mast greater than 0';
    const SIGN_ERROR = 'sign is failed';
    const PARAM_ERROR = 'param is failed';
}
