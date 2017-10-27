<?php

// Front-контроллер

class ctrl {

	public function __construct() {
		$this->db = new db();
        $this->user = false;
		if (!empty($_COOKIE['uid']) && !empty($_COOKIE['key']))
			$this->user = $this->db->query("SELECT * FROM admin WHERE id = ? AND cookie = ?",$_COOKIE['uid'],$_COOKIE['key'])->assoc();
	}
    
    // подключение шаблонов из tpl (отображение представлений)
	public function out($tplname,$nested=false) {
		if (!$nested) {
			$this->tpl = $tplname;
			include "tpl/main.php";
		} else
			include "tpl/" . $tplname;
	}

}

// роутер

class app {

	public function __construct($path) {
		// http://blogofcrazyjunior/?add
		$this->route = explode('/', $path);
		$this->run();
	}
    
    // передача управления контроллеру
	private function run() {
	   
        // извлекаю первый url и проверяю его
		$url = array_shift($this->route);
		if (!preg_match('#^[a-zA-Z0-9.,-]*$#', $url))  throw new Exception('Invalid path');
           
		$ctrlName = 'ctrl' . ucfirst($url);
        
        // если файл с таким названием существует, то будет произведен запуск контроллера
		if (file_exists('app/' . $ctrlName.'.php')) {
			$this->runController($ctrlName);
		} else {
			array_unshift($this->route, $url);
			$this->runController('ctrlIndex');
		}
	}
    
    // запуск контроллера
	private function runController($ctrlName) {
		include "app/" . $ctrlName . ".php";
		$ctrl = new $ctrlName();
        
        // если оставшихся элементов в пути нет, то вызывается index, если есть - элементы пути дальше извлекаются
		if (empty($this->route) || empty($this->route[0])) {
			$ctrl->index();
		} else {
			if (empty($this->route))
				$method = 'index';
			else
				$method = array_shift($this->route);
			if (method_exists($ctrl, $method)) {
				if (empty($this->route))
				$ctrl->$method();
				else
					call_user_func_array (array($ctrl,$method), $this->route);
			} else
				throw new Exception('Error 404');
		}
	}

}