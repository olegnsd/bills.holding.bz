<?php
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
?>
  <p class="bg-danger" style="padding: 15px"><?= $error ?></p>
<?php } else { 
  /*$merchant_id = '39380';
  $secret_word = 'l4i2uik9';
  $order_id = $users['id'];
  $order_amount = '100';
  $sign = md5($merchant_id.':'.$order_amount.':'.$secret_word.':'.$order_id);
  $paymentUrl = 'http://www.free-kassa.ru/merchant/cash.php?m='.$merchant_id.'&oa='.$order_amount.'&o='.$order_id.'&s='.$sign.'&lang=ru&i=&em=';
  */
  $statistics = db::findAll('statistics', ["where"=>"userId=".$users['id'], 'order'=>'id DESC']);
?> 
  <h2 class="text-right">Личный кабинет</h2>

  <div class="row">
    <div class="col-sm-4">
      <table class="table table-striped">
        <tr>
          <td>Услуга:</td>
          <td><?= $users['service'] ?></td>
        </tr>
        <tr>
          <td>Клиент: </td>
          <td><?= $users['name'] ?></td>
        </tr>
        <tr>
          <td>Баланс: </td>
          <td><strong><?= ($users['balance']/100) ?> р.</strong> <button data-toggle="modal" data-target="#payment-form" class="btn btn-primary btn-xs">Пополнить</button></td>
        </tr>
      </table>
    </div>
  </div>
  <style>
    .increase { color: green;}
    .increase:before {
      content: '+';
    }
    .reduction { color: red;}
    .reduction:before {
      content: '-';
    }
  </style>
  <div class="row">
    <h2>Статистика</h2>
    <div class="col-sm-12">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>#</th>
            <th>Дата</th>
            <th>Описание</th>
            <th>Баланс</th>
          </tr>
        </thead>
        <tbody>
<?php 
  if ($statistics) { 
    foreach ($statistics as $key => $val) 
    {
?>
          <tr>
            <td><?= ($key+1) ?></td>
            <td><?= $val['addTime'] ?></td>
            <td><?= $val['description'] ?></td>
            <td class="<?= $val['code'] == 2 ? 'reduction' : 'increase' ?>"><?= ($val['amount']/100) ?> руб.</td>
          </tr> 
<?php 
    }
  } else { ?>
          <tr>
            <td colspan="4" class="text-center">Операций пока не было</td>
          </tr>
<?php } ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal fade" id="payment-form">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title">Пополнение баланса</h4>
        </div>
        <div class="modal-body">
          <form class="form-inline" role="form" method="GET" action="/payments/">
          <center>
            <div class="form-group">
              <input type="number" name="amount" class="form-control" placeholder="Укажите сумму">
            </div>
            <input type="hidden" name="hash" value="<?=$hash?>" />
            
            <select name="payment_type">
            <option value="1">Пополнение Рублями России (₽)</option>
         \      <option value="2">Пополнение бартерными рублями (Б₽)</option>
            </select>
            <br /><br />
            <button type="submit" class="btn btn-success">Подтвердить</button>
            </center>
          </form>
        </div>
      </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
  </div><!-- /.modal -->
<?php } ?>