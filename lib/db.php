<?php

// модель

class db {

	static $mysqli;
	static $last;

	public function __construct() {
		// mysqli_connect("hostname.tld", "user", "password", "database")
		$this->mysqli = mysqli_connect("blogOfCrazyJunior", "root", "", "blogofcrazyjunior_db");

	}
    
    // метод sql-запросов
    // первая строка метода для предотвращения sql-инъекций
	public function query($sql) {
		// $db->query("SELECT * FROM aslkd WHERE id = ?",$id);
		$args = func_get_args();
        
        // выделение первого аргумента
		$sql = array_shift($args);
		$link = $this->mysqli;
        
        //прогоном по массиву экранирую спецсимволы
		$args = array_map(function ($param) use ($link) {
			return "'".$link->escape_string($param)."'";
		},$args);
		$sql = str_replace(array('%','?'), array('%%','%s'), $sql);
        
        // вставка аргумента обратно в массив
		array_unshift($args, $sql);                
        
        // получение готового sql-запроса
		$sql = call_user_func_array('sprintf', $args);
		$this->last = mysqli_query($this->mysqli, $sql);
		if ($this->last === false) throw new Exception('Database error: '.$this->mysqli->error);
		return $this;
	}
    
    //возврат результата запроса ассоциативным массивом
	public function assoc() {
		return $this->last->fetch_assoc();
	}
    
    //возврат реультатов всех запросов
	public function all() {
		$result = array();
		while ($row = $this->last->fetch_assoc()) $result[] = $row;
		return $result;
	}

}