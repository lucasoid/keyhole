<?php 

namespace Keyhole\Registry;

class RegisteredTable extends RegistryEntity {
	
	protected $id;
	protected $name;
	protected $label;
	protected $meta;
	protected $updatedAt;
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	public function setMeta($meta) {
		$this->meta = $meta;
	}
	
	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}
		
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getMeta() {
		return $this->meta;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
}

?>