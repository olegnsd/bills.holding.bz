<?
$ar=array();
$ar["TYPE"]="mysql";
$ar["HOST"]="localhost";
$ar["LOGIN"]=""; // Имя пользователя базы данных
$ar["PASS"]=""; // Пароль
$ar["DATABASE"]=""; // Имя базы данных

$SETTINGS["SQL"]=$ar;
$connection = @mysqli_connect($SETTINGS["SQL"]["HOST"], $SETTINGS["SQL"]["LOGIN"], $SETTINGS["SQL"]["PASS"], $SETTINGS["SQL"]["DATABASE"]);

if (!$connection) {
    echo "Ошибка: Невозможно установить соединение с MySQL." . PHP_EOL;
    echo "Код ошибки errno: " . mysqli_connect_errno() . PHP_EOL;
    echo "Текст ошибки error: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
mysqli_set_charset ( $connection , 'utf8' );

class db
{
		
	static $in_charset = 'UTF-8'; //'windows-1251'; //кодировка бд
	static $out_charset = 'UTF-8'; //кодировка сайта
    static $connection;
    static $sql;
    static $error;
				
	public function query($sql, $params = [])
    {
		if (!empty($params)) {
            $search = [];
            $replace = [];
            foreach($params as $key=>$val) {
                $search[] = $key;
                $replace[] = "'".trim(db::mysqli_real_escape_string($val))."'";
            }
            $sql = str_replace ($search, $replace, $sql);
        }
            
        if(self::$out_charset != self::$in_charset) {
            $sql=iconv(self::$out_charset, self::$in_charset, $sql);
        }
			
		$res = mysqli_query(self::$connection, $sql);
			
		if(mysqli_errno(self::$connection)){
            self::$error = mysqli_errno(self::$connection) . ": " . mysqli_error(self::$connection);
            file_put_contents(dirname(__FILE__) .'/logs.txt', date("d.m.Y H:i:s") . "| " . $sql . "\n" . mysqli_errno(self::$connection) . ": " . mysqli_error(self::$connection) . "\n", FILE_APPEND);
            return false;
			//echo '<p>'.$sql.'</p>';
			//die(mysqli_errno(self::$connection) . ": " . mysqli_error(self::$connection));
		}
			
		return $res;
			
	}
        
    public function affected_rows () {
      return mysqli_affected_rows (self::$connection);
    }
       
    public function insert_id () {
      return mysqli_insert_id(self::$connection);
    }
		
    public function mysqli_real_escape_string($escapestr) {
      return mysqli_real_escape_string(self::$connection, $escapestr);
    }
        
	public function findAll($table = false, $option = false, $params = [], $arrayKey = false){
			
		if(!$table)
			return false;
			
		$select = '*';
		$where = false;
		$limit = false;
		$order = false;
        $join = false;
				
		if(is_array($option)){
			if(isset($option['select']))
				$select = $option['select'];
            if(isset($option['join']))
				$join = " " . $option['join'];
			if(isset($option['where']))
				$where = " WHERE ". $option['where'];
			if(isset($option['order']))
				$order = " ORDER BY ". $option['order'];
			if(isset($option['limit']))
				$limit = " LIMIT ".$option['limit'];
		}
						
		$sql = "select ".$select." from `".$table."` `t1`".$join.$where.$order.$limit;
			
		$result = self::query($sql, $params);
		if (!$result) {
            return [];
        }
		$i=0;
		$array = array();
		while ($db = mysqli_fetch_assoc($result))
		{ 
			if(self::$out_charset != self::$in_charset)
			{
				foreach ($db as $key => $value) {
					$db[$key]=iconv(self::$in_charset, self::$out_charset, $db[$key]);
				}
			}
			
            if ($arrayKey and isset($db[$arrayKey])) {
                $array[$db[$arrayKey]] = $db;
            }
			$array[] = $db;
		}
						
		return $array;
	}
		
	public function find($table = false, $option = false, $params = []){
			
		if(!$table)
			return false;
			
		$select = '*';
		$where = false;
		$order = false;
		$group = false;
			
		if(is_array($option)){
			if(isset($option['select']))
				$select = $option['select'];
            if(isset($option['join']))
				$join = " " . $option['join'];
			if(isset($option['where']))
				$where = " WHERE ". $option['where'];
			if(isset($option['order']))
				$order = " ORDER BY ". $option['order'];
			if(isset($option['group']))
				$order = " GROUP BY ". $option['group'];
		}
						
		self::$sql = $sql = "select ".$select." from `".$table."` `t1`".$join.$where.$group.$order." LIMIT 1";
            
		if(!$result = self::query($sql, $params)) {
            return false;
        }
			
		$db = mysqli_fetch_assoc($result);
			
		if(!$db)
			return false;
		
		if(self::$out_charset != self::$in_charset)
		{
            foreach ($db as $key => $value) {
				$db[$key]=iconv(self::$in_charset, self::$out_charset, $db[$key]);
			}
		}
			
		return $db;
	}
}
  
db::$connection = $connection;