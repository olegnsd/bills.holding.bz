<?php

ini_set('display_errors', 0);



if(!isset($_POST['stat']) || $_POST['stat'] != 'kj54n9gub9249'){
    die();
}

define ('CORE', '/include');
include_once '../include/sql.php';

$tabsList = ['Все', 'EasyWork24', 'Миелофон', 'Конструткор Медиа', 'Хостинг KHost', 'Инвестиции в KSRI', 'Прочие доходы', 'Строй-Авеню'];
$search = 2;
$option = [];
$params = [];
$revenueOption = ['select'=>'sum(t1.amount) as summ', 'where'=>'t1.code=1'];
if ($search) {
    $option['where'] = "service=:service";
    $revenueOption['join'] .= 'left join users t2 ON t2.id=t1.userId';
    $revenueOption['where'] .= ' AND t2.service=:service';
    $params[':service'] = $tabsList[$search];
}
  
$revenue = db::find('statistics', $revenueOption, $params)['summ'];

$revenue = number_format(($revenue/100), 0, ',', ' ');

$option['select'] = 'sum(balance) as summ';
$option['where'] .= (isset($option['where']) ? ' AND ' : '') . 'balance<0';
$debt = db::find('users', $option, $params)['summ'];
$debt = number_format(($debt/100), 0, ',', ' ');

$stat_bills = array(
	'revenue' => $revenue,
    'debt' => $debt
	);

echo(json_encode($stat_bills));
