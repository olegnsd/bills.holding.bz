<?

$h=date('H');

if($h<10)die();
if($h>=19)die();
if(!$_GET['cron'])die();
echo(' 2 ');
include_once 'include/sql.php';
  $settings = db::find('settings', ['where'=>'id=4']);
//print_r($settings);
  $option['where'] = 'robotMode>0';
  $params = [];
$list = db::findAll('users', $option, $params);
if ($list) {
    foreach ($list as $val) { 
//echo('<hr>');print_r($val);
$statistics = db::find('statistics', ["where"=>"userId=".$val['id']." AND code!=2", 'order'=>'id DESC']);
$lasttime=strtotime($statistics['addTime']);
$lastrobot=strtotime($val['robotLast']);
if($val['robotMode']==1 && $val['lastCollector'] == 2){
//if($lasttime<(time()-60*60*24*2)){
if($lastrobot<(time()-60*60*24*2)){
$send=true;
}
//}
}
if($val['robotMode']==2 && $val['lastCollector'] == 2){
if($lasttime<(time()-60*60*24*7)){
if($lastrobot<(time()-60*60*24*4)){
$send=true;
}
}
}

if($send){

require_once("include/ssms_su.php");
$sms=$settings['message'];
$sms=str_replace('{service}',$val['service'],$sms);
$sms=str_replace('{balance}',$val['balance']/100,$sms);
$sms=str_replace('{link}',"http://bills.holding.bz/client/".$val['hash'],$sms);

echo($sms);
$sql = "UPDATE users SET robotLast=CURRENT_TIMESTAMP, lastCollector=1 WHERE id=".(int)$val['id'];
db::query($sql);

if($val['phone']){$r = smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$val['phone']), $sms, array("sender_name"=>'ksri'));
die();}
}
?><?
}
}
?>
