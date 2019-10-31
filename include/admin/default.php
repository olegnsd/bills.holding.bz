<?php 
  include_once CORE.'/sql.php';
  
  $name = false;
  $serviceList = [
    'EasyWork24' => 0,
    'Миелофон' => 0,
    'Конструткор Медиа' => 0,
    'Хостинг KHost' => 0,
    'Инвестиции в KSRI' => 0,
    'Прочие доходы' => 0,
    'Строй-Авеню' => 0,
  ];
  $tabsList = ['Все', 'EasyWork24', 'Миелофон', 'Конструткор Медиа', 'Хостинг KHost', 'Инвестиции в KSRI', 'Прочие доходы', 'Строй-Авеню', 'Последнии операции'];
  $search = (int) $_GET['search'];
  $message = false;
  if (isset($_POST['on'])) {
    // форма отправлена
    $name = htmlspecialchars($_POST['username'], ENT_QUOTES);
    $service = $_POST['userservice'];
    $serviceList[$service] = 1;
    if (empty($name) or empty($service)) {
      $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Все поля обязательны для заполнения</div>';
    } else {
      $sql = "INSERT INTO users (hash, name, service) VALUES ('".md5($name.$service.'@sdfsi5')."', :name, :service)";
      if (db::query($sql, [':name'=>$name, ':service'=>$service])){
        $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно добавлена</div>';
      } else {
        $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось добавить запись: '.db::$error.'</div>';
      }
    }
  }
  
  if (isset($_GET['action']) and $_GET['action'] == 'delete') {
    if($_GET['cod'] == "3455"){
      $userId = (int) $_GET['id'];
      if ($userId) {
        $sql = "DELETE FROM users WHERE id=".$userId;
        if (db::query($sql)){
        $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно удалена</div>';
        } else {
        $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось удалить запись: '.db::$error.'</div>';
        }
      }
    }else {
      $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось удалить запись: неверный код</div>';
    }
  }
  
  $option = [];
  $params = [];
  $revenueOption = ['select'=>'sum(t1.amount) as summ', 'where'=>'t1.code=1'];
  if ($search) {
    $option['where'] = "service=:service";
    $revenueOption['join'] .= 'left join users t2 ON t2.id=t1.userId';
    $revenueOption['where'] .= ' AND t2.service=:service';
    $params[':service'] = $tabsList[$search];
  }
  
  if (isset($_GET['sort'])) {
    $option['order'] = 'balance ' . ($_GET['sort'] == 1 ? 'ASC' : 'DESC');
  }

  $list = db::findAll('users', $option, $params);
  $option['select'] = 'sum(balance) as summ';
  $option['where'] .= (isset($option['where']) ? ' AND ' : '') . 'balance<0';
  
  $debt = db::find('users', $option, $params)['summ'];
  
  $revenue = db::find('statistics', $revenueOption, $params)['summ'];
  $nowTime = time();
  $settings = db::findAll('settings', false, [], 'id');
?>
  <h2 class="text-right">Админка <a href="?action=settings" type="button" class="btn btn-default">Настройки</a></h2>
  <!-- Modal -->
	<div class="modal" id="myModal_del" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog" role="document">
		<div class="modal-content">
		  <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			<h4 class="modal-title" id="myModalLabel">Удаление</h4>
		  </div>
		  <div class="modal-body">
			<label class="alert-link" id="del_info"></label>
			<form name="del" id="form_del" action="GET">
				<input type=hidden name='action' value='delete'>
				<input type=hidden name='id' value=''>
				<input type=text name='cod' value='' placeholder="код">
			</form>
		  </div>
		  <div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Отменить</button>
			<button type="button" id='send_butt' del_id="" name="send_butt" class="btn btn-danger" >Подтвердить</button>
		  </div>
		</div>
	  </div>
	</div>

  <div class="row">  
    <?= $message ?>
    <div class="col-sm-6">
      <div class="row">
        <form class="form-horizontal" role="form" method="POST" action=".">
          <div class="form-group">
            <label class="col-sm-3 control-label">Клиент:</label>
            <div class="col-sm-9">
              <input type="text" name="username" class="form-control" placeholder="Укажите наименование клиента" value="<?= $name ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Вид услуги: </label>
            <div class="col-sm-9">
              <select class="form-control" name="userservice">
                <option value="">Укажите услугу</option>
                <?php foreach ($serviceList as $key=>$value) { ?>
                <option value="<?= $key ?>"<?= $value ? ' selected' : '' ?>><?= $key ?></option>
                <?php } ?>
              </select> 
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 text-right">
              <input type="hidden" name="on" value="on" />
              <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Добавить</button>
            </div>
          </div>
        </form>
      </div>
    </div>
    <div class="col-sm-4 col-sm-offset-2">
      <table class="table table-striped">
        <tr>
          <td>Выручка:</td>
          <td><strong><?= number_format(($revenue/100), 0, ',', ' '); ?> руб.</strong></td>
        </tr>
        <tr>
          <td>Дебиторская задолженность:</td>
          <td><strong><?= number_format(($debt/100), 0, ',', ' '); ?> руб.</strong></td>
        </tr>
      </table>
    </div>
  </div>
  <div class="row">
  
    <ul class="nav nav-tabs">
<?php foreach ($tabsList as $key=>$val) { ?>
      <li<?= $search==$key ? ' class="active"':''?>><a href="?search=<?=$key?>"><?= $val ?></a></li>
<?php } ?>
    </ul>
 
    <table class="table table-striped">
      <thead>
        <tr>
          <th>#</th>
          <th>Имя</th>
          <th>Услуга</th>
          <th><a href="?search=<?= (int) $_GET['search']?>&sort=<?= ( (empty($_GET['sort']) or $_GET['sort']==2) ? 1 : 2 ) ?>">Баланс</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
<?php 
  if ($list) {
    foreach ($list as $val) { 
?>
        <tr>
          <td><?= $val['id'] ?><a name="id<?= $val['id'] ?>"></a></td>
          <td><?= $val['name'] ?><br><a class="hashLink" href="/client/<?= $val['hash'] ?>" target="_blank"><code><?= $val['hash'] ?></code></a><?if($val['ref']>0){?><br>Реферал: <a href="#id<?= $val['ref'] ?>">#<?= $val['ref'] ?></a><?}?></td>
          <td><?= $val['service'] ?></td>
          <td class="text-center">
            <?= ($val['balance']/100) ?> руб. <?if($val['balance']<0){?><?if($val['robotMode']){?><?if($val['robotMode']==1)echo('| Должник'); else echo('| Платящий должник');?><?}else{?><?}?><?}?>
            <p data-id="<?= $val['id'] ?>" data-name="<?= $val['name'] ?>" data-balance="<?= ($val['balance']/100) ?>" data-service="<?= $val['service'] ?>">
            <?php 
                if ($val['balance'] <= 0) {
                    if ($val['email'] and $val['sendEmailTime'] < $nowTime) {
                        echo '<button type="button" class="btn btn-primary btn-xs sendEmail id'.$val['id'].'">E-mail</button> ';
                    }
                    if ($val['phone'] and $val['sendPhoneTime'] < $nowTime) {
                        echo '<button type="button" class="btn btn-info btn-xs sendSMS id'.$val['id'].'">СМС</button>';
                    }
                }
            ?>
            </p>
          </td>
          <td>
            <a href="?action=balance&id=<?=$val['id']?>" type="button" class="btn btn-default">Баланс</a>
            <a href="?action=edit&id=<?=$val['id']?>" type="button" class="btn btn-default"><span class="glyphicon glyphicon-edit"></span></a>
            <button del_id="<?=$val['id']?>" name_del="<?= $val['name'] ?>" name="butt_issuse" class="btn btn-default" data-toggle="modal" data-target="#myModal_del"><span class="glyphicon glyphicon-remove"></span></button>
          </td>
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
  
<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel">Уведомление</h4>
      </div>
      <div class="modal-body text-center" data-id="" data-action="">
        ...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
        <button type="button" class="btn btn-primary sendForm">Отправить</button>
      </div>
    </div>
  </div>
</div>
<div style="display:none">
    <textarea class="templateEmail"><?= $settings[1]['message']?></textarea>
    <textarea class="templateSMS"><?= $settings[2]['message']?></textarea>
</div>
  
<?php
    $jsScript = '
    $(".sendEmail").click(function(){
        text = $(".templateEmail").val().replace (/{name}/g, $(this).parent().attr("data-name"))
            .replace (/{link}/g, "http://bills.holding.bz"+$(this).closest("tr").find(".hashLink").attr("href"))
            .replace (/{balance}/g, $(this).parent().attr("data-balance"))
            .replace (/{service}/g, $(this).parent().attr("data-service"));
        $("#myModal .modal-title").text("E-mail уведомление");
        $("#myModal .modal-body").attr("data-id", $(this).parent().attr("data-id")).attr("data-action", "sendEmail");
        //$("#myModal .modal-body").html("Вы действительно хотите отправить <b>E-mail</b> клинету <b>«"+ $(this).parent().attr("data-name")+"»</b>?");
        $("#myModal .modal-body").html("<textarea class=\"form-control\" rows=\"5\">"+text+"</textarea>");
        $("#myModal .modal-footer").show();
        $("#myModal").modal("show");
    })
    $(".sendSMS").click(function(){
        $("#myModal .modal-title").text("СМС уведомление");
        text = $(".templateSMS").val().replace (/{name}/g, $(this).parent().attr("data-name"))
            .replace (/{link}/g, "http://bills.holding.bz"+$(this).closest("tr").find(".hashLink").attr("href"))
            .replace (/{balance}/g, $(this).parent().attr("data-balance"))
            .replace (/{service}/g, $(this).parent().attr("data-service"));
        $("#myModal .modal-body").attr("data-id", $(this).parent().attr("data-id")).attr("data-action", "sendSMS");
        //$("#myModal .modal-body").html("Вы действительно хотите отправить <b>СМС</b> клинету <b>«"+ $(this).parent().attr("data-name")+"»</b>?");
        $("#myModal .modal-body").html("<textarea class=\"form-control\" rows=\"5\">"+text+"</textarea>");
        $("#myModal .modal-footer").show();
        $("#myModal").modal("show");
    })
    $("#myModal .sendForm").click(function(){
        console.log($("#myModal .modal-body").attr("data-id"), $("#myModal .modal-body").attr("data-action"));
        text = $("#myModal .modal-body textarea").val();
        $("#myModal .modal-body").html("Подождите, Ваш запрос обрабатывается");
        $("#myModal .modal-footer").hide();
        $.post("#", {action: $("#myModal .modal-body").attr("data-action"), id: $("#myModal .modal-body").attr("data-id"), text: text}, function(data){
            if(data.status == 200) {
                $("."+data.type+".id"+data.id).remove();
            }
            $("#myModal .modal-body").html(data.message);
        }, "json").fail(function(){$("#myModal .modal-body").html("Ошибка, не удалось связаться с сервером, повторите попыптку еще раз");});
    });
    $("button[name=butt_issuse]").each(function() {
        $(this).on("click", function () {
            del_id = $(this).attr("del_id");
            name = $(this).attr("name_del");
            $("#del_info").html("Удалить: "+name+"?");
			$("form[name=del] input[name=id]").val(del_id);
        });
    });
    $("#send_butt").on("click", function () {
        $("#myModal_del").modal("hide");
        $("#form_del").submit();
    });
    ';
?>
