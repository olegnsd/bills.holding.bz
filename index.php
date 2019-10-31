<?php
  $error = 'Не известно как обработать эту страницу';
  $jsScript = false;
  define ('CORE', dirname(__FILE__) . '/include');
  $urlArray = explode ('/', $_SERVER['REQUEST_URI']);
  if ($urlArray[1] == 'admin') {
    if(!session_id())session_start();
  
    $autorization = false;
    $name = false;
    $password = false;
    $message = false;
    
    if (isset($_SESSION['autorization'])) {
      $autorization = true;
    } elseif (isset($_POST['autorization'])) {
      $name = htmlspecialchars ($_POST['username'], ENT_QUOTES);
      $password = htmlspecialchars ($_POST['password'], ENT_QUOTES);
      if ($name == 'admin' and $password == 'tGxsUn') {//'vhTRie'
        $_SESSION['autorization'] = 'on';
        $autorization = true;
      } else {
        $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> данные не соответствуют</div>';
      }
    }
    
    if ( !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' & !$_GET[ajax] )
    {
        //асинхронный запрос
        if ($message) {
            exit(json_encode(['status'=>403, 'message'=>'authorization error'], JSON_UNESCAPED_UNICODE));
        }
        include_once CORE.'/sql.php';
        switch ($_POST['action']) {
            case 'sendEmail':
                $id = (int) $_POST['id'];
                if ($id) {
                    //получаем информацию о клиенте
                    if ($users = db::find('users', ['where'=>'id=:id'], [':id'=>$id])){
                        $nowTime = time();
                        if ($users['sendEmailTime'] > $nowTime) {
                            $result = ['status'=>400, 'message'=>'Почтовое уведомление уже было выслано за последние 24 часа'];
                            break;
                        }
                        $message = $_POST['text'];
                        /*
                        //получаем шаблон почтового сообщения
                        $message = db::find('settings', ['where'=>'id=1'])['message'];
                        $message = str_replace(
                            [
                                '{link}', 
                                '{name}', 
                                '{balance}',
                                '{service}',
                            ], 
                            [
                                'http://bills.holding.bz/client/'.$users['hash'], 
                                $users['name'], 
                                ($users['balance']/100),
                                $users['service'],
                            ], 
                            $message
                        );
                        */
                        $Name = "ПАО Милитари Холдинг"; //senders name
                        $email = "info@holding.bz"; //senders e-mail adress
                        $recipient = $users['email']; //recipient
                        $mail_body = $message; //mail body
                        $subject = "Уведомление об оплате"; //subject
                        $header = "From: ". $Name . " <" . $email . ">\r\n"; //optional headerfields
                        
                        if(mail($recipient, $subject, $mail_body, $header)){
                            $result = ['status'=>200, 'message'=>'Почтовое уведомление успешно отправлено', 'id'=>$id, 'type'=>'sendEmail'];
                            db::query("UPDATE users SET sendEmailTime=".($nowTime+86400)." WHERE id=".$id);
                        } else {
                            $result = ['status'=>400, 'message'=>'Не удалось отправить почтовое уведомление, посторите попытку еще раз'];
                        }
                    } else {
                        $result = ['status'=>400, 'message'=>'Пользователь не найден :('];
                    }
                } else {
                    $result = ['status'=>400, 'message'=>'Не переданы обязательные параметры'];
                }
                break;
            case 'sendSMS':
                $id = (int) $_POST['id'];
                if ($id) {
                    //получаем информацию о клиенте
                    if ($users = db::find('users', ['where'=>'id=:id'], [':id'=>$id])){
                        $nowTime = time();
                        if ($users['sendPhoneTime'] > $nowTime) {
                            $result = ['status'=>400, 'message'=>'Почтовое уведомление уже было выслано за последние 24 часа'];
                            break;
                        }
                        $message = $_POST['text'];
                        /*
                        //получаем шаблон почтового сообщения
                        $message = db::find('settings', ['where'=>'id=2'])['message'];
                        $message = str_replace(
                            [
                                '{link}', 
                                '{name}', 
                                '{balance}',
                                '{service}'
                            ], 
                            [
                                'http://bills.holding.bz/client/'.$users['hash'], 
                                $users['name'], 
                                ($users['balance']/100),
                                $users['service'],
                            ], 
                            $message
                        );
                        */
require_once(CORE."/ssms_su.php");

$r = smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$users['phone']), $message, array("sender_name"=>'ksri'));
                        /*require_once(CORE."/smsPHPClass/transport.php");
                        $api = new Transport();
                        
                        $params = array(
                            'text' => $message,
                            'source' => 'ПАО Конструктор Империй'

                        );

                        $phones = str_replace(['+', '-', '(', ')', ' '], '',$users['phone']);
                        $send = $api->send($params,$phones);*/
                        
                        if($r[1]){
                            $result = ['status'=>200, 'message'=>'СМС уведомление успешно отправлено', 'id'=>$id, 'type'=>'sendSMS'];
                            db::query("UPDATE users SET sendPhoneTime=".($nowTime+86400)." WHERE id=".$id);
                        } else {
                            $result = ['status'=>400, 'message'=>'Произошла ошибка при отправке', 'code'=>$r[1]];
                        }
                    } else {
                        $result = ['status'=>400, 'message'=>'Пользователь не найден :('];
                    }
                } else {
                    $result = ['status'=>400, 'message'=>'Не переданы обязательные параметры'];
                }
                break;
            default:
                $result = ['status'=>400, 'message'=>'bad request'];
        }
        exit(json_encode($result, JSON_UNESCAPED_UNICODE));
    }
    
  } elseif ($urlArray[1] == 'payments') {
    $amount = (int) $_GET['amount'];
    $hash = $_GET['hash'];
    $payment_type = $_GET['payment_type'];
    if (!empty($hash) and strlen($hash) == 32 and $amount > 0) {
      include_once CORE.'/sql.php';
      if (!$users = db::find('users', ['where'=>'hash=:hash'], [':hash'=>$hash])) {
        $error = 'Клинет не найден :(';
      } else {
        //формируем ссылку и делаем редирект
        if($payment_type=="1"){
            $merchant_id = '72794';
            $secret_word = 'r5w2th3n';
            $order_id = $users['id'];
            $order_amount = $amount;
            $sign = md5($merchant_id.':'.$order_amount.':'.$secret_word.':'.$order_id);
            $paymentUrl = 'http://www.free-kassa.ru/merchant/cash.php?m='.$merchant_id.'&oa='.$order_amount.'&o='.$order_id.'&s='.$sign.'&lang=ru&i=&em=';
            header('Location:'.$paymentUrl);
            exit;
        }
        
        if($payment_type=="2"){
            include_once CORE.'/Bartercoin.php';
            $n = $users['id'];//rand(11111, 99999);
			$merchant_id = 5; /* ID магазина в системе мерчанта, выдается мерчантом при регистрации */
			$password1 = "23250q"; /* пароль для подписи запросов от магазина к мерчанту */
			$password2 = "23250w"; /* пароль для проверки подписи запросов от мерчанта к магазину */
			$bc = new \Bartercoin($merchant_id, $password1, $password2);
			$data['order_id'] = $n;
			$data['order_sum'] = $amount;
			$data['order_description'] = "Оплата заказа №$n";
			$data['hash'] = $hash;
			$json = $bc->newOrder($data);
            
            //include_once CORE.'/Bartercoin.php';
            //$n= rand(11111, 99999);
                //$merchant_id = 5; /* ID магазина в системе мерчанта, выдается мерчантом при регистрации */
                //$password1 = "23250q"; /* пароль для подписи запросов от магазина к мерчанту */
                //$password2 = "23250w"; /* пароль для проверки подписи запросов от мерчанта к магазину */
                //$bc = new \Bartercoin($merchant_id, $password1, $password2);
                //$data['order_id'] = $n;
                //$data['order_sum'] = $amount;
                //$data['order_description'] = "Оплата заказа №$n";
                //$json = $bc->newOrder($data);
                
                //if (empty($json['url']))
                    //{
                        //print_r($json);
                        //$a= $json['message'];
                        //echo $a[0]."<br>";
                      //exit("Error payment url");
                    //}else{
                        //$url=$json['url'];
                        //$time_add = date( 'd.m.20y H:i:s', time() ); 
                        //$db = mysql_connect ("localhost","wizardgrp_bills","hKFf6BuRdA");
                        //mysql_select_db ("wizardgrp_bills",$db);
                        //$users = db::find('users', ['where'=>'hash=:hash'], [':hash'=>$hash]);
                        //$user_id = $users['id'];
                        //$result = mysql_query ("INSERT INTO pay_bcr (order_id,user_id,summ,times,status) VALUES('$n','$user_id','$amount','$time_add','0')");
                        //header('Location:'.$url);
                        //exit;
                    //}
        }
        
      }
    }
    else {
      $error = 'Не переданы обязательные параметры';
    }
  } elseif ($urlArray[1] == 'json') {
    $error = false;
    $hash = trim($urlArray[2]);
    //$error = '<pre>'.print_r($urlArray, true).'</pre>';
    if (!empty($hash) and strlen($hash) == 32) {
      include_once CORE.'/sql.php';
      if (!$users = db::find('users', ['where'=>'hash=:hash'], [':hash'=>$hash])) {
        $error = 'Клинет не найден :(';
      }
    }
    else {
      $error = 'Не переданы обязательные параметры';
    }
    if ($error) {
        exit(json_encode(['error'=>$error], JSON_UNESCAPED_UNICODE));
    } else {
        $statistics = db::findAll('statistics', ["select"=>"addTime, code, description, amount", "where"=>"userId=".$users['id'], 'order'=>'id DESC']);
        exit(json_encode($statistics, JSON_UNESCAPED_UNICODE));
    }
  }
if(!$_GET[ajax]){?><!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>bills.holding.bz</title>
  <link href="/css/_bootstrap.min.css" rel="stylesheet" />
  <style>
    html, body {height: 100%}
    .main {min-height: 100%; padding-bottom: 70px}
    .footer {background-color: #ccc;padding: 10px;margin-top: -60px; text-align: center; height: 60px;}
  </style>
</head>
<body>
<div class="main">
<div class="container">
<?php  } 
  switch ($urlArray[1]) {
    case 'admin';
      include CORE . '/admin/index.php';
      break;
    case 'client':
      include CORE . '/client/index.php';
      break;
    default:
      echo '<p>'.$error.'</p>';
  }if(!$_GET[ajax]){
?>
</div>
</div>
<div class="footer">
  <a href="//www.free-kassa.ru/">
     <img src="//www.free-kassa.ru/img/fk_btn/13.png">
  </a>
  <a href="//www.holding.bz/"><img src="//www.holding.bz/stamp.gif"></a>

</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/jquery.maskedinput.min.js"></script>
<script>
  $(document).ready(function () { <?= $jsScript ?> })
</script>
</body>
</html><?}?>
