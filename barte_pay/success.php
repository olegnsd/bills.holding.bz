<?php
 include "Bartercoin.php";
        $merchant_id = 5;
        $password1 = "23250q";
        $password2 = "23250w";
        $bc = new Bartercoin($merchant_id, $password1, $password2);
        $result = [];
         $db = mysql_connect ("localhost","wizardgrp_bills","hKFf6BuRdA");
         mysql_select_db ("wizardgrp_bills",$db);
         mysql_query("SET NAMES utf8");
         mysql_query("SET CHARACTER SET utf8");
    $results=mysql_query("select * from pay_bcr WHERE status='0' ORDER BY `pay_bcr`.`id` DESC");
    while($row=mysql_fetch_array($results)){
        $data['order_id'] = $row[1];
        $json = $bc->checkOrder($data);
        if($json['state']=="1"){
            $order_id=$json['order_id'];
             $resulthatistim=mysql_query("select * from pay_bcr WHERE order_id='$order_id'");
             $rowsni=mysql_fetch_array($resulthatistim);
             $temp_summ=$rowsni['summ'];
             $temp_summ=$temp_summ*100;
             $temp_user_id=$rowsni['user_id'];
             $payment_id=$rowsni['id'];
              $time_add = date( '20y-m-d H:i:s', time() ); 
             {mysql_query("update users set balance=balance+$temp_summ where id='$temp_user_id'");}
             {mysql_query("update pay_bcr set status='1' where id='$payment_id'");}  
             $description="Пополнение через BCR";
             //$description=iconv("UTF-8", "windows-1251", $description);   
             $result = mysql_query ("INSERT INTO statistics (userId,addTime,code,description,amount,intid) VALUES('$temp_user_id','$time_add','1','$description','$temp_summ','0')");
        }

    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>bills.holding.bz</title>
  <link href="/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    html, body {height: 100%}
    .main {height: 100%; padding-bottom: 70px}
    .footer {background-color: #ccc;padding: 10px;margin-top: -60px; text-align: center; height: 60px;}
  </style>
</head>
<body>
<div class="main">
<div class="container">
  <div class="alert alert-success">
    <span class="glyphicon glyphicon-ok"></span>
    Оплата успешно выполнена
  </div>
</div>
</div>
<div class="footer">
  <a href="//www.free-kassa.ru/"><img src="//www.free-kassa.ru/img/fk_btn/18.png"></a>
</div>
</body>
</html>

