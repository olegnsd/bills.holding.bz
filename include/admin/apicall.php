<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

include_once CORE.'/sql.php';

$message = false;
$err_prefix = "";
$has_prefix = "";
$err_api_key = "";
$has_api_key = "";
$err_time = "";
$has_time = "";
$err_timeto = "";
$err_timefrom = "";
$err_caller = "";
$has_caller = "";
$err_file = "";
$has_file = "";

$settings = db::find('tasks_user_api_calls');
$user_id = $settings['user_id'];
$api_key = $settings['api_key'];
$timefrom = $settings['timefrom'];
$timeto = $settings['timeto'];
$prefix = $settings['prefix'];
$prior = $settings['prior'];
$caller = $settings['caller'];
$file_name = $settings['file_name'];

$myecho = $user_id;
`echo " user_id:   $myecho " >>/tmp/qaz`;

if (isset($_POST['save'])) {
    switch ($_POST['save']) {
        case 'save_api_calls_settings': 
            $user_id = ($_POST['user_id']);
            $api_key = value_proc($_POST['api_key']);
            $timefrom = value_proc($_POST['timefrom']);
            $timeto = value_proc($_POST['timeto']);
            $prefix = value_proc($_POST['prefix']);
            $prior = value_proc($_POST['prior']);
            $caller = mb_strtoupper(value_proc($_POST['caller']));
            if(!$user_id)
            {
                exit();
            }
            // Проверка на ошибки
            $error = false;
            if($api_key=='')
            {
                $err_api_key = "Ключ АПИ не указан";
                $has_api_key = "has-error";
                $error = true;
            }
            if($timefrom < '10:00')
            {
                $err_timefrom = "Время начала не менее 10:00";
                $has_time = "has-error";
                $error = true;
            }
            if($timefrom == '')
            {
                $err_timefrom = "Не указано время начала";
                $has_time = "has-error";
                $error = true;
            }
            if($timeto > '21:00')
            {
                $err_timeto = "Время конца не более 21:00";
                $has_time = "has-error";
                $error = true;
            }
            if($timeto == '')
            {
                $err_timeto = "Не указано время конца";
                $has_time = "has-error";
                $error = true;
            }
            if($timefrom > $timeto)
            {
                $err_time = "Время конца больше времени начала";
                $has_time = "has-error";
                $error = true;
            }
            if(strlen($prefix) > 5)
            {
                $err_prefix = "Не более 5-ти символов";
                $has_prefix = "has-error";
                $error = true;
            }
            if(!preg_match('/^\d+\+$/', $prefix))
            {
                $err_prefix = "недопустимые символы";
                $has_prefix = "has-error";
                $error = true;
            }
            if($prefix == '')
            {
                $err_prefix = "";
                $has_prefix = "";
                $error = false;
            }
            if(strlen($caller) > 4)
            {
                $err_caller = "больше 4-x символов";
                $has_caller = "has-error";
                $error = true;
            }
            if(preg_match('/\W+/', $caller))
            {
                $err_caller = "недопустимые символы";
                $has_caller = "has-error";
                $error = true;
            }
            if(strlen($caller) == '')
            {
                $err_caller = "не заполнено";
                $has_caller = "has-error";
                $error = true;
            }
            if(is_uploaded_file($_FILES["Filedata"]["tmp_name"])){
    //            $myecho = json_encode($_FILES["Filedata"]["tmp_name"]);
    //            `echo " tmp_name:    " >>/tmp/qaz`;
    //            `echo "$myecho" >>/tmp/qaz`;

                $user_id = value_proc($_POST["user_id"]);
                $file_parts = pathinfo($_FILES['Filedata']['name']);
                $tmp = value_proc($_FILES['Filedata']['tmp_name']);
                // Расширение файла
                $extension = $file_parts['extension'];
                $size = value_proc($_FILES["Filedata"]["size"]);
                $error_file = value_proc($_FILES["Filedata"]["error"]);
                $targetFolder = '/temp/audio';
                $targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
                $targetFile = rtrim($targetPath,'/') . '/' . value_proc($_FILES['Filedata']['name']);
                //расширение не wav
                if($extension != 'wav' || !(preg_match("(WAVE)", file_get_contents($tmp)))){
                    $err_file = "файл не wav";
                    $has_file = "has-error"; 
                }elseif($size > 10485760){//размер файла больше 10М
                    $err_file = 'файл больше 10М';
                    $has_file = "has-error";
                }elseif($error_file != 0){
                    $err_file = 'не удалось загрузить файл';
                    $has_file = "has-error";
                }elseif(!move_uploaded_file($tmp,$targetFile)){
					$err_file = 'ошибка загрузки файла';
                    $has_file = "has-error";
				}else{
                    // Сохраняем api settings
                    $file_name = value_proc($_FILES["Filedata"]["name"]);
                    $row = db::find('tasks_user_api_calls');//." WHERE user_id='$user_id'";
                    if(!isset($row['user_id'])){
                        $sql = "INSERT INTO "."tasks_user_api_calls"." 
                            (user_id, file_name) 
                            VALUES 
                            ('$user_id','$file_name')
                            ";
                        db::query($sql, []);  
                    }
                    else{
                        unlink($targetPath . '/' . $row['file_name']);
                        $sql = "UPDATE "."tasks_user_api_calls"." SET
                            file_name = '$file_name'";
                            //WHERE user_id='$user_id'";
                        db::query($sql, []); 
                    }		
                }
            }
            // Если ошибок не обнаружено - сохраняем изменения профиля
            if(!$error && $err_file=="")
            {
                // Сохраняем api settings
                $sql = db::find('tasks_user_api_calls');//." WHERE user_id='$user_id'";
                if(!isset($sql['user_id'])){
                     $sql = "INSERT INTO "."tasks_user_api_calls"." 
                        (user_id,
                        api_key, 
                        timefrom,
                        timeto,
                        prefix,
                        prior,
                        caller) 
                        VALUES 
                        ('$user_id',
                        '$api_key', 
                        '$timefrom',
                        '$timeto',
                        '$prefix',
                        '$prior',
                        '$caller')
                        ";
                    db::query($sql, []);
                }
                else{
                    $sql = "UPDATE "."tasks_user_api_calls"." SET
                        api_key = '$api_key', 
                        timefrom = '$timefrom',
                        timeto = '$timeto',
                        caller = '$caller',
                        prefix = '$prefix',
                        prior = '$prior'
                        "; 
                        //WHERE user_id='$user_id'";
                    db::query($sql, []);  
                }			 

                if(!mysql_error() && $password_chenge && $current_user_id==$user_id)
                {
                    $auth->set_cookie_user_hash($user_hash);
                }

                $message = '<div class="alert alert-success"><strong>Выполнено:</strong> Запись успешно обновлена</div>';
            }
        break;
        default:
            $message = '<div class="alert alert-danger"><strong>Ошибка:</strong> Неизвестный запрос</div>';
    }
    
}


// Обработка переданных значений в запросах
function value_proc($value, $iconv=1, $allowable_tags=0)
{
	if($allowable_tags)
	{
		$value = trim(htmlspecialchars(strip_tags($value, "<h1><h2><h3><h4><h5><h6><strong><em><sup><sub><blockquote><div></pre><p><table><thead><th><tbody><tr><td>")));
	}
    else 
	{
		$value = trim(htmlspecialchars(strip_tags($value)));
	}
	
	if (!get_magic_quotes_gpc()) 
	{
		$value = addslashes($value);
	}
	
	if($iconv)
	{
		$value = iconv('utf-8//IGNORE', 'cp1251//IGNORE', $value);
	}
	 
	return $value;
}

?>
  <h2 class="text-right">Админка</h2>
  <div class="row">
    <ol class="breadcrumb">
        <li><a href="/admin/">Главная</a></li>
        <li><a href="/admin/?action=settings">Настройки</a></li>
        <li class="active">АПИ звонков</li>
    </ol>
    <?= $message ?>
    <div class="col-sm-9">
      <div class="row">
        <form class="form-horizontal" role="form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="save" value="save_api_calls_settings" />
            <input type="hidden" name="user_id" value="<?= $user_id ?>" />
            <div class="form-group <?= $has_api_key ?>">
                <label class="col-sm-4 control-label">Ключ АПИ</label>
                <div class="col-sm-8">
                    <input type="text" name="api_key" class="form-control" aria-describedby="helpBlock1" value="<?= $api_key ?>">
                    <span id="helpBlock1" class="help-block"><?= $err_api_key ?></span>
                </div>
            </div>
            <div class="form-group <?= $has_time ?>">
                <label class="col-sm-4 control-label">Временной диапазон обзвона</label>
                <div class="col-sm-4">
                    <input type="time" name="timefrom" class="form-control" aria-describedby="helpBlock2" value="<?= $timefrom ?>" />
                    <span id="helpBlock2" class="help-block"><?= $err_timefrom ?></span>
                </div>
                <div class="col-sm-4">
                    <input type="time" name="timeto" class="form-control" aria-describedby="helpBlock3" value="<?= $timeto ?>" />
                    <span id="helpBlock3" class="help-block"><?= $err_timeto ?></span>
                </div>
                <div class="col-sm-4"></div>
                <div class="col-sm-8">
                    <span id="helpBlock2" class="help-block"><?= $err_time ?></span>
                </div>
            </div>
            <div class="form-group <?= $has_prefix ?>">
                <label class="col-sm-4 control-label">Префикс телефона</label>
                <div class="col-sm-2">
                    <input type="text" name="prefix" class="form-control" aria-describedby="helpBlock4"  placeholder="XX+" maxlength="5" value="<?= $prefix ?>">
                    <span id="helpBlock4" class="help-block"><?= $err_prefix ?></span>
                </div>
                <div class="col-sm-4">
                    <input type="text" class="form-control" placeholder="7(XXX)XXXXXXX" disabled>
                    <!--p class="text-danger"></p-->
                </div>
                <div class="col-sm-2"></div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 control-label">Приоритет обзвона</label>
                <div class="col-sm-8">
                    <select name="prior" id="prior" class="form-control">
                        <option value="-3">-3
                        <option value="-2">-2
                        <option value="-1">-1
                        <option value="0">0
                        <option value="1">1
                        <option value="2">2
                        <option value="3">3
                    </select>
                </div>
            </div>
            <div class="form-group <?= $has_caller ?>">
                <label class="col-sm-4 control-label">Канал</label>
                <div class="col-sm-8">
                    <input type="text" name="caller" class="form-control" style="width:6em" placeholder="XXGX" maxlength="4" aria-describedby="helpBlock5" value="<?= $caller ?>">
                    <span id="helpBlock5" class="help-block"><?= $err_caller ?></span>
                </div>
            </div>
            <div class="form-group <?= $has_file ?>">
                <label class="col-sm-4 control-label">Звуковой файл</label>
                <div class="col-sm-8">
                    <input name="Filedata" class="form-control" type="file" multiple="false" aria-describedby="helpBlock6" /><small>Обязательно в формате WAV для астериска. </small><small>Загружен: <?= $file_name ?> </small>
                    <span id="helpBlock6" class="help-block"><?= $err_file ?></span>
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-offset-4 col-sm-8 text-right">
                  <button type="submit" class="btn btn-success">Сохранить</button>
                </div>
          </div>
        </form> 
      </div>
    </div>
  </div>

<script src="/js/jquery-1.8.3.min.js"></script>
<script type="text/javascript">
    $('#prior>option[value $= <?= $prior ?>]').attr('selected', 'selected');
</script>

