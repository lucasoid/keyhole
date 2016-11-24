<?php 

namespace Keyhole\Registry;

class Mock extends RegistryEntity {
	
	protected $id;
	protected $name;
	protected $updatedAt;
	protected $createdBy;
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}
	
	public function setCreatedBy($createdBy) {
		$this->createdBy = $createdBy;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
	public function getCreatedBy() {
		return $this->createdBy;
	}
	
}

?>