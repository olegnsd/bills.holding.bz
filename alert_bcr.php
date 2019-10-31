<?
ini_set('display_errors', 0);

define ('CORE', dirname(__FILE__) . '/include');
include_once CORE.'/sql.php';

$merchant_id = '41';
$merchant_secret = 'ALvfwe645ebG2Jhs';

$sign = md5($merchant_id.$merchant_secret.$_REQUEST['id'].$_REQUEST['sum']);

if ($sign != $_REQUEST['secret']) {
    die('wrong sign');
}

$userId = (int) $_POST['id'];
$code = 4;
$amount = 0;
$description = '';
 
$message = false;

if (!$users = db::find('users', ['where'=>'id='.$userId])) {
  echo '<div class="alert alert-danger"><strong>Ошибка:</strong> Клинет не найден :(</div>';
} else {
    if (isset($_POST['robot'])) {
		$sql = "UPDATE users SET robotMode='1' WHERE id=".$users['id'];
		db::query($sql);
		$users['robotMode']=1;
	}
    $amount = ((int) $_POST['sum'] * 100);
    $description = htmlspecialchars ($_POST['comment'], ENT_QUOTES);
	
    if (empty($amount) or empty($description)) {
      $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Все поля обязательны для заполнения</div>';
    } else {
		require_once(CORE."/ssms_su.php");

		//добавляем статистику
		$sql = "INSERT INTO statistics
		(userId, code, description, amount, intid)
		VALUES (".$userId.", ".$code.", '".$description."', ".$amount.", 0)";
		db::query($sql);

		$sql = "UPDATE users SET balance=`balance`+".$amount." WHERE id=".$userId;
		$users['balance'] += $amount;
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
		if($users['phone'])$r = smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$users['phone']), "Ваш баланс ".$users['service']." пополнен на ".(($amount)/100)." BCR. Баланс: ".(($users['balance'])/100)." руб.", array("sender_name"=>'ksri'));
		if($refuser['phone'])$r =smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$refuser['phone']), "Ваш баланс ".$refuser['service']." пополнен на ".(($amount*0.2)/100)." BCR. (партнёрские начисления) Баланс: ".(($refuser['balance']+($amount*0.2))/100)." руб. http://bills.holding.bz/client/".$refuser['hash']."", array("sender_name"=>'ksri'));

		$message = '<div class="alert alert-success"><strong>Выполнено:</strong> Баланс клиента «'.$users['name'].'» изменен</div>';
		die('YES');
    }

} 

?>Ошибка
