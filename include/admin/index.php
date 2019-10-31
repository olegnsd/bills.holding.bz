<?php
if ($autorization) {
	switch ($_GET['search']) {
		case '8':
			include dirname(__FILE__) . '/statistics.php';
			exit;
			break;
	}
	
  switch ($_GET['action']) {
    case 'balance':
      include dirname(__FILE__) . '/balance.php';
      break;
    case 'edit':
      include dirname(__FILE__) . '/edit.php';
      break;
    case 'settings':
      include dirname(__FILE__) . '/settings.php';
      break;
    case 'apicall':
      include dirname(__FILE__) . '/apicall.php';
      break;
    default:
      include dirname(__FILE__) . '/default.php';
  }
  
} else { ?>
  <p>
    <div class="col-sm-4 col-sm-offset-4">
      <div class="row">
        <?= $message ?>
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-3 control-label">Логин:</label>
            <div class="col-sm-9">
              <input type="text" name="username" class="form-control" placeholder="Ваш логин" value="<?= $name ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-3 control-label">Пароль:</label>
            <div class="col-sm-9">
              <input type="password" name="password" class="form-control" placeholder="Укажите пароль" value="<?= $password ?>" required>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-3 col-sm-9 text-right">
              <input type="hidden" name="autorization" value="on" />
              <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-log-in"></span> Войти</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </p>
<?php } ?>
