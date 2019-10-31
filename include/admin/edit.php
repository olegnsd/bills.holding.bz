<?php
include_once CORE.'/sql.php';

$userId = (int) $_GET['id'];

if($_GET['ajax']){
$option = [];
  $params = [];
    $option['where'] = "name LIKE :search AND `id`!=".$userId;

    $params[':search'] = "%".$_GET[search]."%";
  

  $list = db::findAll('users', $option, $params);
if ($list) {?><br><?
    foreach ($list as $val) {
?><a href="javascript:void(0);" class="btn btn-xs btn-block btn-default" onclick="$('input[name=ref]').val('<?=$val[id];?>');"><?=$val[name];?> (<?=$val[service];?>)</a><?
}}else{?><p class="text-danger">Не найдено</p><?}
die();
}



 

$message = false;

if (!$users = db::find('users', ['where'=>'id='.$userId])) {
  echo '<div class="alert alert-danger"><strong>Ошибка:</strong> Клинет не найден :(</div>';
} else {
  $name = $users['name'];
  $phone = $users['phone'];
  $email = $users['email'];
  $robot = $users['robotMode'];
  $ref = $users['ref'];if($ref==0)$ref='';
  if (isset($_POST['on'])) {
    // форма отправлена
    $name = htmlspecialchars($_POST['username'], ENT_QUOTES);
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES);
    $robot = (int)$_POST['robot'];
    $ref = (int)$_POST['ref'];if($ref==$userId)$ref=0;
    $service = $users['service'];
    if (empty($name) or empty($service)) {
      $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Все поля обязательны для заполнения</div>';
    } else {
      $sql = "UPDATE users SET name=:name, hash=:hash, email=:email, phone=:phone, robotMode=:robot, ref=:ref WHERE id=".$userId;
      if (db::query($sql, [':name'=>$name, ':hash'=>md5($name.$service.'@sdfsi5'), ':email'=>$email, ':phone'=>$phone, ':robot'=>$robot, ':ref'=>$ref])){
        $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
      } else {
        $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обновить запись: '.db::$error.'</div>';
      }
    }
  }
?>
  <h2 class="text-right">Админка</h2>
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/admin/">Главная</a></li>
      <li class="active">Редактирование</li>
    </ol>
    <blockquote>
      Клиент: <strong><?=$users['name']?></strong> | Баланс: <?=($users['balance']/100)?> руб.
    </blockquote>
    <?= $message ?>
    <div class="col-sm-6">
      <div class="row">
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-3 control-label">Клиент:</label>
            <div class="col-sm-9">
              <input type="text" name="username" class="form-control" placeholder="Укажите наименование клиента" value="<?= $name ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Email:</label>
            <div class="col-sm-9">
              <input type="email" name="email" class="form-control" placeholder="Email клиента" value="<?= $email ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Телефон:</label>
            <div class="col-sm-9">
              <input type="text" name="phone" class="form-control phone" placeholder="Контактный телефон клиента" value="<?= $phone ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Режим взыскания:</label>
            <div class="col-sm-9">
<select name="robot" class="form-control">
<?if(($users['balance']>=0) | ($users['phone']=='')){?><option value="0">Отключено (нельзя включить при не отрицательном балансе или отсутствующем номере телефона)<?}else{?>
<option value="0"<?if($robot==0)echo(' SELECTED');?>>Отключено
<option value="1"<?if($robot==1)echo(' SELECTED');?>>Режим "должник"
<option value="2"<?if($robot==2)echo(' SELECTED');?>>Режим "платящий должник" (включается при начале погашения, уменьшает частоту и делает паузу после оплаты)
<?}?>
</select>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Реферер:</label>
            <div class="col-sm-9">
              <input type="text" name="ref" class="form-control" placeholder="ID реферера" value="<?= $ref ?>">
<br>
              <input type="search" class="form-control ajsearch" placeholder="(найти ID по имени)">
<a href="javascript:void(0);" onclick="$('.ajload').text('загрузка').load('?action=edit&id='+<?=(int)$_GET['id'];?>+'&ajax=1&search='+$('.ajsearch').val());return false;" class="btn btn-default btn-xs">Искать пользователя</a>
<div class="ajload"></div>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 text-right">
              <input type="hidden" name="on" value="on" />
              <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Изменить</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
<?php } 
$jsScript = '$(".phone").mask("+7 (999) 999-99-99");';
?>
