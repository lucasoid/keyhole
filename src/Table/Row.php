<?php

namespace Keyhole\Table;

class Row {
	
	private $id;
	private $data;
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getData() {
		return $this->data;
	}
}
	
?>