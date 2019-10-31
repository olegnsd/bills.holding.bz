<?php


$reest['method']='push_msg';
$request['api_v']='1.1';
$request['email']='bills-militcorp';
$request['password']='11nm3zfp';
$request['phone']='79013395277';
$request['text']='Ваш баланс в EasyWork24 на текущий момент составляет 100000 руб. Предлагаем пополнить его по адресу http://bills.holding.bz/client/fef4298621b0be5ebfc8fb474cde7b88';
$request['sender_name']='ksri';
$request['format']='json';


$curl = curl_init();

	curl_setopt($curl, CURLOPT_URL, "http://api.goip.holding.bz/");//	curl_setopt($curl, CURLOPT_URL, "http://api2.ssms.su/");
	curl_setopt($curl, CURLINFO_HEADER_OUT, true);
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	if(!is_null($cookie)){	
		curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	}
	
	$data = curl_exec($curl);//die(curl_getinfo($curl, CURLINFO_HEADER_OUT ).$data);

	$info = curl_getinfo($ch);
	
	curl_close($curl);

	echo "<pre>";
	var_dump($info);
	var_dump($data);
	
	die();

	
?>