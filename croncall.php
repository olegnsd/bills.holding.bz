<?php
ini_set('display_errors', 0);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL); 2018-06-22 13:52:39 id=287

if ($_POST['hash'] != '2f69e9e841dc') {
    die();
}

include_once 'include/sql.php';

$res = db::find('tasks_user_api_calls');

$h=date('H');
if($h < $res['timefrom'])die();
if($h >= $res['timeto'])die();


//сохранить изменения в базу, если задание успешно выполнено
if($_POST['success'] == 'success'){
	$sql = "UPDATE users SET robotLast=CURRENT_TIMESTAMP, lastCollector='2' WHERE id=" . $_POST['user_id'];
	//$dateadd = $_POST['dateadd'];
    //$sql = "UPDATE users SET robotLast='$dateadd' , lastCollector=2 WHERE id=" . $_POST['user_id'];
	db::query($sql);
	//echo $_POST['success'];
}

$option['where'] = 'robotMode>0';
$params          = [];
$list            = db::findAll('users', $option, $params);

if ($list) {
    $foptmp = fopen('./temp/csv/synthesized-phones' . '.csv', "w");
    //добавление в начало задания случайных телефонов
    for($i=0; $i<=2; $i++){
        $phone = "7" . strval(mt_rand(1000000000, 9999999999));
        fwrite($foptmp, ';' . $res['prefix'] . $phone . ';' . PHP_EOL);
    }
    foreach ($list as $val) {
        $statistics = db::find('statistics', ["where" => "userId=" . $val['id'] . " AND code!=2", 'order' => 'id DESC']);
        $lasttime   = strtotime($statistics['addTime']);//последнее пополнение счета юзером
        $lastrobot  = strtotime($val['robotLast']);//последнее смс(звонок) по взысканию
        if ($val['robotMode'] == 1 && $val['lastCollector'] == 1) {
            if ($lastrobot < (time() - 60 * 60 * 24 * 2)) {
                $user_phone = clear_fone($val['phone']);
                $user_id = (int) $val['id'];
                $send = true;
            }
        }
        if ($val['robotMode'] == 2 && $val['lastCollector'] == 1) {
            if ($lasttime < (time() - 60 * 60 * 24 * 7)) {
                if ($lastrobot < (time() - 60 * 60 * 24 * 4)) {
                    $user_phone = clear_fone($val['phone']);
                    $user_id = (int) $val['id'];
                    $send = true;
                }
            }
        }
        if($send){
            if(!$curl = curl_init()){
               die(); 
            }
            `echo "user_phone: $user_phone" >>/tmp/qaz`;
            fwrite($foptmp, ';' . $res['prefix'] . $user_phone . ';' . PHP_EOL);
            $sms = "Ваш баланс в {service}: {balance} руб. Погасите хотя-бы частично: {link}";
//            $sms = $settings['message'];
            $sms = str_replace('{service}', $val['service'], $sms);
            $sms = str_replace('{balance}', $val['balance'] / 100, $sms);
            $sms = str_replace('{link}', "https://bills.holding.bz/client/" . $val['hash'], $sms);

            echo ($sms);
            //случайный клиент
            $client_id = strval(mt_rand(1, 320));
            $comment = "Взыскание: " . $user_phone . " долг: " . $val['balance'] / 100 . " руб.";
            $cfile_wav = new CURLFile('./temp/audio/'.$res['file_name'],'audio/x-wav','10wav');
            $cfile_csv = new CURLFile('./temp/csv/synthesized-phones' . '.csv','mybase1');
            $query = array(
                'comment' => $comment,
                'caller' => $res['caller'], //'19G8'
                'client_id' => $client_id, //'67',
                'timefrom' => $res['timefrom'],
                'timeto' => $res['timeto'],
                'prior' => $res['prior'],
                'sleep' => '75',
                'typebase' => 'file',
                'sms_enable' => '1',//$res['sms_enable'],
                'sms_text' => $sms,//$sms_text,
                'email_enable' => '0',
                'email_text' => '',
                'file' => $cfile_csv,
                'range1' => '9260000000',
                'range2' => '9269999999',
                'sound' => $cfile_wav,
                'email_notify' => '',
                'url_notify' => '',
                'user_id' => $user_id,
            );

            curl_setopt($curl, CURLOPT_URL, 'https://call.holding.bz/task/save/'.$res['api_key']);//32b748942f69e9e841dc812be6b1e578
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $query); 
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: multipart/form-data',
            ));

            $out = curl_exec($curl);

            if(curl_errno($curl)){
                $msg = curl_error($curl);
            }else{
                $msg = "File upload successfully";
            }

            curl_close($curl);

            $out_arr = json_decode($out);
            if($out_arr[0] == 'success'){
                //$sql = "UPDATE users SET robotLast=CURRENT_TIMESTAMP, lastCollector=2 WHERE id=" . $user_id;
                //db::query($sql);
                echo " out: $out";
            }

            $return = array('msg' => $msg);

            echo json_encode($return);
            die();
            
        } 
    }
    fclose($foptmp);
}

function clear_fone($person_phone){
    $phone = preg_replace("/\D{1,}/", "", $person_phone);
    return $phone;
}
