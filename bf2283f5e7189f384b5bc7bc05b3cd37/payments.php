<?php

$merchant_id = '72794';
$merchant_secret = 'ff=255';

function getIP() {
if(isset($_SERVER['HTTP_X_REAL_IP'])) return $_SERVER['HTTP_X_REAL_IP'];
   return $_SERVER['REMOTE_ADDR'];
}
/*if (!in_array(getIP(), array('136.243.38.147', '136.243.38.149', '136.243.38.150', '136.243.38.151', '136.243.38.189'))) {
    die("hacking attempt!");
}*/

$sign = md5($merchant_id.':'.$_REQUEST['AMOUNT'].':'.$merchant_secret.':'.$_REQUEST['MERCHANT_ORDER_ID']);

if ($sign != $_REQUEST['SIGN']) {
    die('wrong sign');
}

include_once '../include/sql.php';

$userId = (int) $_REQUEST['MERCHANT_ORDER_ID'];
$amount = round ($_REQUEST['AMOUNT'] * 100);
$intid = (int) $_REQUEST['intid'];

if (!$users = db::find('users', ['where'=>'id='.$userId])) {
  die('Клинет не найден :(');
}
?>YES<?
header('Connection: close');
header("Content-Length: 3" );
header("Content-Encoding: none");
header("Accept-Ranges: bytes");
ob_end_flush();
ob_flush();
flush();

if ($refuser = db::find('users', ['where'=>'id='.$users['ref']])){
$sql0 = "INSERT INTO statistics
        (userId, code, description, amount, intid)
        VALUES (".$users['ref'].", 3, '20% от пополения баланса №".$userId."', ".($amount*0.2).", 0)";
      db::query($sql0);

        $sql0 = "UPDATE users SET balance=`balance`+".($amount*0.2)." WHERE id=".$users['ref'];
      db::query($sql0);

}

//добавляем статистику
$sql = "INSERT INTO statistics
	(userId, code, description, amount, intid)
	VALUES (".$userId.", 1, 'Пополнение баланса с помощью FREE-KASSA', ".$amount.", ".$intid.")";
db::query($sql);

$sql = "UPDATE users SET balance=`balance`+".$amount." WHERE id=".$userId;
db::query($sql);

if(($users['balance']<0)&($users['robotMode']==1)){
$sql = "UPDATE users SET robotMode=2 WHERE id=".$userId;
        $users['robotMode'] = 2;
      db::query($sql);
}
if(($users['balance']>=0)&($users['robotMode']>0)){
$sql = "UPDATE users SET robotMode=0 WHERE id=".$userId;
        $users['robotMode'] = 0;
      db::query($sql);
}

require_once("../include/ssms_su.php");
if($refuser['phone'])$r =smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$refuser['phone']), "Ваш баланс ".$refuser['service']." пополнен на ".(($amount*0.2)/100)." руб. (партнёрские начисления) Баланс: ".(($refuser['balance']+($amount*0.2))/100)." руб. http://bills.holding.bz/client/".$refuser['hash']."", array("sender_name"=>'ksri'));

if($users['phone'])$r = smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$users['phone']), "Ваш баланс ".$users['service']." пополнен на ".(($amount)/100)." руб. Баланс: ".(($users['balance']+($amount))/100)." руб. http://bills.holding.bz/client/".$users['hash']."", array("sender_name"=>'ksri'));

die ('YES');

?>
