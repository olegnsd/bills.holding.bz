<?php

//namespace App\Payments;

class Bartercoin
{
    /**
     * URL BarterCoin Merchant
     * @var string
     */
    private $url = "https://bartercoin.holding.bz/do/";
	private $baseHref = null;

    /**
     * ID магазина
     * @var integer
     */
    private $merchant_id = null;

    /**
     * Секретный пароль для подписи запросов от магазина к мерчанту BarterCoin
     * @var string
     */
    private $password1 = null;

    /**
     * Секретный пароль для проверки подписи ответов от BarterCoin к магазина
     * @var string
     */
    private $password2 = null;

    /**
     * Алгоритм подписи запросов
     * Возможные значения: md5
     * @var string
     */
    private $algoritm = null;

    /**
     * Конструктор.
     *
     * Используется как:
     * <code>
     * $bc = new Bartercoin('merchant_id', 'password1', 'password2', 'algoritm');
     * </code>
     * @param integer $merchant_id ID магазина, выданный BarterCoin Merchant
     * @param string $password1 Первый секретный пароль
     * @param string $password2 Второй секретный пароль
     * @param string $algoritm Алгоритм подписи запросов (не обязательный параметр, по умолчанию принимает значение "md5")
     */
    public function __construct($merchant_id, $password1, $password2, $algoritm = "md5")
    {
        $this->merchant_id = $merchant_id;
        $this->password1 = $password1;
        $this->password2 = $password2;
        $this->algoritm = $algoritm;
        
        $domain = $_SERVER['SERVER_NAME'];
		$folder = "/";//начало и конец - "/"!
		$this->baseHref = "http://".$domain.$folder;
    }

    /**
     * Отправка запроса к BarterCoin Merchant на создание нового запроса на оплату заявки
     *
     * @param array $data Список параметров формы. Обязательно должен создержать параметры: order_id - ID заявки, order_sum - сумма заявки, order_description - Описание заявки, timestamp - метка времени в unix-формате
     * @return array Оnвет от сервиса BarterCoin Merchant
     */
    public function newOrder($data)
    {
        $sum=$data['order_sum'];
		$merchant_id = '41';// номер магазина в бартеркоин
		$secret_word = 'uajqKCKxEnSAFAUv';
		$order_id = $data['order_id'];
		$order_amount = $sum;
		$sign = md5($merchant_id.$secret_word.$order_id.(float)$order_amount);
		$paymentUrl = $this->url.'?pay&shop='.$merchant_id.'&id='.$order_id.'&sum='.$order_amount.'&comment=Пополнение '.(float)$order_amount.' BCR&secret='.$sign.'&return='.$this->baseHref.'client/'.$data['hash'];
		header('Location:'.$paymentUrl);
		die();
        
        //$data['merchant_id'] = $this->merchant_id;
        //$data['timestamp'] = date("Y-m-d H:i:s");

        //$crcStr = $this->merchant_id . ":" . $data['order_id'] . ":" . $data['order_sum'] . ":" . $data['order_description'] . ":" . $data['timestamp'] . ":" . $this->password1;

        //$shopValues = array_filter($data, function ($key) {
            //return mb_strpos($key, "shop_") === 0;
        //}, ARRAY_FILTER_USE_KEY);

        //ksort($shopValues);

        //if ($shopValues !== [])
            //foreach ($shopValues as $value)
                //$crcStr .= ":" . $value;

        //switch ($this->algoritm) {
            //case "md5":
                //$data['crc'] = md5($crcStr);
                //break;
            //default:
                //throw new \Exception('Неизвестный метод хеширования');
        //}


        //return $this->_sendRequest($this->url . "new", $data);
        
      
       //return json_encode(["success" => true]);

    }

    /**
     * Проверка оплаты платежа
     *
     * @param array $data Список параметров формы. Обязательно должен создержать параметры: order_id - ID заявки
     * @return array Оnвет от сервиса BarterCoin Merchant
     */
    public function checkOrder($data)
    {
        $data['merchant_id'] = $this->merchant_id;

        $crcStr = $this->merchant_id . ":" . $data['order_id'] . ":" . $this->password1;

        switch ($this->algoritm) {
            case "md5":
                $data['crc'] = md5($crcStr);
                break;
            default:
                throw new \Exception('Неизвестный метод хеширования');
        }

        $result = $this->_sendRequest($this->url . "check", $data);
        if ($result['success']) {
            $data['merchant_id'] = $this->merchant_id;
            $data['timestamp'] = date("Y-m-d H:i:s");

            $crcStr = $this->merchant_id . ":" . $data['order_id'] . ":" . $this->password2;

            switch ($this->algoritm) {
                case "md5":
                    $data['crc'] = md5($crcStr);
                    break;
                default:
                    throw new \Exception('Неизвестный метод хеширования');
            }

            $result['checksign'] = $data['crc'] === $result['crc'];
        }

        return $result;
    }

    /**
     * Проверка подписи на проверку параметров при оплате
     *
     * @param array $data Список параметров формы. Обязательно должен создержать параметры: order_id - ID заявки
     * @return array Оnвет от сервиса BarterCoin Merchant
     */
    public function checkPaymentOrderSign($data)
    {
        $crcStr = $this->merchant_id . ":" . $data['transaction_id'] . ":" . $data['order_id'] . ":" . $data['commission_type'] . ":" . $data['sum'] . ":" . $data['commission'] . ":" . $data['sum_pay'] . ":" . $data['description'] . ":" . $data['timestamp'] . ":" . $this->password2;

        $shopValues = array_filter($data, function($key) {
            return mb_strpos($key, "shop_") === 0;
        }, ARRAY_FILTER_USE_KEY);

        ksort($shopValues);

        if ($shopValues !== [])
            foreach ($shopValues as $value)
                $crcStr .= ":" . $value;

        switch ($this->algoritm) {
            case "md5":
                $crc = md5($crcStr);
                break;
            default:
                throw new \Exception('Неизвестный метод хеширования');
        }

        return $crc === $data['crc'];
	}


    private function _sendRequest($url, $data)
    {
        $post = [
            CURLOPT_POST => 1,
            CURLOPT_HEADER => 0,
            CURLOPT_URL => $url,
            CURLOPT_FRESH_CONNECT => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FORBID_REUSE => 1,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_POSTFIELDS => http_build_query($data),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $post);

        if (!$result = curl_exec($ch))
            throw new \Exception('Сервер мерчанта не вернул ответ');
        elseif (curl_getinfo($ch, CURLINFO_HTTP_CODE) !== 200) {
            throw new \Exception('Сервер мерчанта не отвечает');
        }

        curl_close($ch);

        $result = json_decode($result, true);

        return $result;
          echo json_encode(["success" => true]);
          exit();
    }
    

}
