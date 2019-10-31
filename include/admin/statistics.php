<?php
 
include_once CORE.'/sql.php';
$message = false;

$option['select'] = 's.*, u.id AS user_id, u.name, u.hash, u.ref, u.service';
$option['where'] = 'u.id=s.userId';
$option['order'] = 'id DESC';
$option['limit'] = '1000';
$params = [];
$list = db::query('SELECT s.*, u.id AS user_id, u.name, u.hash, u.ref, u.service FROM statistics AS s, users AS u WHERE u.id=s.userId ORDER BY s.id DESC LIMIT 1000', $params);
$operation = [1=>'Пополнение', 2=>'Списание', 3=>'Бонусное пополнение'];
?>
  <h2 class="text-right">Админка</h2>
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/admin/">Главная</a></li>
      <li class="active">Последние операции</li>
    </ol>
    <?= $message ?>
      <table class="table table-striped">
      <thead>
        <tr>
            <th>#</th>
            <th>userId</th>
            <th>Имя</th>
            <th>Услуга</th>
            <th>time</th>
            <th>Операция</th>
            <th>Сумма</th>
            <th>Описание</th>
            <th>FreeKassa</th>
        </tr>
      </thead>
      <tbody>
<?php 
  if ($list) {
    foreach ($list as $val) { 
?>
        <tr>
            <td><?= $val['id'] ?></td>
            <td><?= $val['userId'] ?></td>
            <td><?= $val['name'] ?><br><a class="hashLink" href="/client/<?= $val['hash'] ?>" target="_blank"><code><?= $val['hash'] ?></code></a><?if($val['ref']>0){?><br>Реферал: <a href="#id<?= $val['ref'] ?>">#<?= $val['ref'] ?></a><?}?>
            </td>
            <td><?= $val['service'] ?></td>
            <td><?= $val['addTime'] ?></td>
            <td><?= $operation[$val['code']] ?></td>
            <td><?= $val['amount']/100 ?></td>
            <td><?= $val['description'] ?></td>
            <td><?= $val['intid'] ?></td>
        </tr>
<?php 
    }
  } else 
  {
?>
        <tr>
          <td colspan="5" class="text-center">Записей не найдено</td>
        </tr>
<?php } ?>
      </tbody>
    </table>
  </div>
