<?php
$userId = (int) $_GET['id'];
 
include_once CORE.'/sql.php';
$message = false;

$settings = db::findAll('settings', false, [], 'id');
$email = $settings[1]['message'];
$phone = $settings[2]['message'];
$email2 = $settings[3]['message'];
$phone2 = $settings[4]['message'];
 
if (isset($_POST['on'])) {
    
    switch ($_POST['on']) {
        case 'emailOn':
            $email = htmlspecialchars ($_POST['email'], ENT_QUOTES);
            $sql = "UPDATE settings SET message=:message WHERE id=1";
            if (db::query($sql, [':message'=>$email])){
                $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
            } else {
                $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обновить запись: '.db::$error.'</div>';
            }
            break;
        case 'phoneOn':
            $phone = htmlspecialchars ($_POST['phone'], ENT_QUOTES);
            $sql = "UPDATE settings SET message=:message WHERE id=2";
            if (db::query($sql, [':message'=>$phone])){
                $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
            } else {
                $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обновить запись: '.db::$error.'</div>';
            }

        case 'emailOn1':
            $email2 = htmlspecialchars ($_POST['email'], ENT_QUOTES);
            $sql = "UPDATE settings SET message=:message WHERE id=3";
            if (db::query($sql, [':message'=>$email2])){
                $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
            } else {
                $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обновить запись: '.db::$error.'</div>';
            }
            break;
        case 'phoneOn1':
            $phone2 = htmlspecialchars ($_POST['phone'], ENT_QUOTES);
            $sql = "UPDATE settings SET message=:message WHERE id=4";
            if (db::query($sql, [':message'=>$phone2])){
                $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
            } else {
                $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обновить запись: '.db::$error.'</div>';
            }

            break;
        default:
            $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Неизвестный запрос</div>';
    }
    // форма отправлена
    /*
    $phone = htmlspecialchars($_POST['phone'], ENT_QUOTES);
    $email = htmlspecialchars($_POST['email'], ENT_QUOTES);
    $service = $users['service'];
    if (empty($name) or empty($service)) {
      $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Все поля обязательны для заполнения</div>';
    } else {
      $sql = "UPDATE users SET name=:name, hash=:hash, email=:email, phone=:phone WHERE id=".$userId;
      if (db::query($sql, [':name'=>$name, ':hash'=>md5($name.$service.'@sdfsi5'), ':email'=>$email, ':phone'=>$phone])){
        $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
      } else {
        $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Не удалось обновить запись: '.db::$error.'</div>';
      }
    }
    */ 
}

?>
  <h2 class="text-right">Админка</h2>
  <div class="row">
    <ol class="breadcrumb">
      <li><a href="/admin/">Главная</a></li>
      <li class="active">Настройки</li>
    </ol>
    <?= $message ?>
    <div class="col-sm-9">
      <div class="row">
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-4 control-label">Е-mail сообщение:</label>
            <div class="col-sm-8">
              <textarea class="form-control" rows="3" name="email" placeholder="Укажите шаблон E-mail сообщения" required><?= $email ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8 text-right">
              <input type="hidden" name="on" value="emailOn" />
              <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Сохранить</button>
            </div>
          </div>
        </form>
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-4 control-label">СМС сообщение:</label>
            <div class="col-sm-8">
              <textarea class="form-control" rows="3" name="phone" placeholder="Укажите шаблон СМС сообщения" required><?= $phone ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8 text-right">
              <input type="hidden" name="on" value="phoneOn" />
              <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Сохранить</button>
            </div>
          </div>
        </form>
        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-4 control-label">Е-mail сообщение (взыскание):</label>
            <div class="col-sm-8">
              <textarea class="form-control" rows="3" name="email" placeholder="Укажите шаблон E-mail сообщения" required><?= $email2 ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8 text-right">
              <input type="hidden" name="on" value="emailOn1" />
              <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Сохранить</button>
            </div>
          </div>
        </form>

        <form class="form-horizontal" role="form" method="POST">
          <div class="form-group">
            <label class="col-sm-4 control-label">СМС сообщение (взыскание):</label>
            <div class="col-sm-8">
              <textarea class="form-control" rows="3" name="phone" placeholder="Укажите шаблон СМС сообщения" required><?= $phone2 ?></textarea>
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8 text-right">
              <input type="hidden" name="on" value="phoneOn1" />
              <button type="submit" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span> Сохранить</button>
            </div>
          </div>
        </form>
        
        <div class="form-group">
            <label class="col-sm-4 control-label"></label>
            <div class="col-sm-8"></div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-4 col-sm-8 text-left">
                <a href="?action=apicall" type="button" class="btn btn-primary">Настройка автовзысканий звонков</a>
            </div>
        </div>

      </div>
    </div>
  </div>
