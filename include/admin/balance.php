<?php
$userId = (int) $_GET['id'];
$code = 2;
$amount = 0;
$description = '';
 
include_once CORE.'/sql.php';
$message = false;

if (!$users = db::find('users', ['where'=>'id='.$userId])) {
  echo '<div class="alert alert-danger"><strong>Ошибка:</strong> Клинет не найден :(</div>';
} else {
    if (isset($_POST['robot'])) {
 $sql = "UPDATE users SET robotMode='1' WHERE id=".$users['id'];
      db::query($sql);
$users['robotMode']=1;
}
  if (isset($_POST['on'])) {
    // форма отправлена
    $code = (int) $_POST['code'];
    $amount = ((int) $_POST['amount'] * 100);
    $description = htmlspecialchars ($_POST['description'], ENT_QUOTES);
    if (empty($code) or $code>3 or $code<0 or empty($amount) or empty($description)) {
      $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Все поля обязательны для заполнения</div>';
    } else {require_once(CORE."/ssms_su.php");


      //добавляем статистику
      $sql = "INSERT INTO statistics
        (userId, code, description, amount, intid)
        VALUES (".$userId.", ".$code.", '".$description."', ".$amount.", 0)";
      db::query($sql);
      if ($code == 1 or $code == 3) {
        if($code==1){
if ($refuser = db::find('users', ['where'=>'id='.$users['ref']])){
$sql0 = "INSERT INTO statistics
        (userId, code, description, amount, intid)
        VALUES (".$users['ref'].", 3, '20% от пополения баланса №".$userId."', ".($amount*0.2).", 0)";
      db::query($sql0);

        $sql0 = "UPDATE users SET balance=`balance`+".($amount*0.2)." WHERE id=".$users['ref'];
      db::query($sql0);
}}
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
if($users['phone'])$r = smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$users['phone']), "Ваш баланс ".$users['service']." пополнен на ".(($amount)/100)." руб. Баланс: ".(($users['balance'])/100)." руб.", array("sender_name"=>'ksri'));
if($refuser['phone'])$r =smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$refuser['phone']), "Ваш баланс ".$refuser['service']." пополнен на ".(($amount*0.2)/100)." руб. (партнёрские начисления) Баланс: ".(($refuser['balance']+($amount*0.2))/100)." руб. http://bills.holding.bz/client/".$refuser['hash']."", array("sender_name"=>'ksri'));

      } else {
        $sql = "UPDATE users SET balance=`balance`-".$amount." WHERE id=".$userId;
        $users['balance'] -= $amount;
      db::query($sql);
if($users['phone'])$r = smsapi_push_msg_nologin("bills-militcorp", '11nm3zfp', str_replace(['+', '-', '(', ')', ' '], '',$users['phone']), "С вашего баланса ".$users['service']." снято ".(($amount)/100)." руб. Баланс: ".(($users['balance'])/100)." руб. http://bills.holding.bz/client/".$users['hash']."", array("sender_name"=>'ksri'));
      }
      $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Баланс клиента «'.$users['name'].'» изменен</div>';
    }
  }
?>
  <h2 class="text-right">Админка</h2>
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/admin/">Главная</a></li>
      <li class="active">Операции с балансом</li>
    </ol>
    <blockquote>
      Клиент: <strong><?=$users['name']?></strong> | Баланс: <?=($users['balance']/100)?> руб. <?if(($users['balance']<0) & ($users['phone'])){?>| <?if($users['robotMode']){?>Режим взыскания: <?if($users['robotMode']==1)echo('Должник'); else echo('Платящий должник');?> (<a href="?action=edit&id=<?=(int)$_GET['id'];?>">Настройки</a>)<?}else{?><form method=post style="display:inline;"><input type="hidden" name="robot" value="1"><button type="submit" class="btn btn-default btn-xs">Включить режим взыскания</button></form><?}?><?}?>
    </blockquote>
    <?= $message ?>
    <div class="col-sm-6">
      <div class="row">
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-3 control-label">Действие: </label>
            <div class="col-sm-9">
              <select class="form-control" name="code">
                <option value="2">Списание</option>
                <option value="1"<?=$code==1 ? ' selected' : ''?>>Пополнение Рублями России</option>
                <option value="3"<?=$code==3 ? ' selected' : ''?>>Бонусное пополнение</option>
              </select> 
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Сумма:</label>
            <div class="col-sm-9">
              <input type="number" name="amount" class="form-control" placeholder="Укажите сумму" value="<?= ($amount/100) ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Описание:</label>
            <div class="col-sm-9">
              <input type="text" name="description" maxlength="100" class="form-control" placeholder="Комментарий для клиента" value="<?=$description?>" required>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 text-right">
              <input type="hidden" name="id" value="<?= $userId ?>" />
              <input type="hidden" name="on" value="on" />
              <button type="submit" class="btn btn-success">Выполнить</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php } ?>
