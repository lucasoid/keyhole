<?php 

namespace Keyhole\Registry;

class RegisteredAccess extends RegistryEntity {
	
	protected $id;
	protected $tableId;
	protected $accessor;
	protected $accessLevel;
	protected $updatedAt;
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setTableId($tableId) {
		$this->tableId = $tableId;
	}
	
	public function setAccessor($accessor) {
		$this->accessor = $accessor;
	}
	
	public function setAccessLevel($accessLevel) {
		$this->accessLevel = $accessLevel;
	}
	
	public function setUpdatedAt($updatedAt) {
		$this->updatedAt = $updatedAt;
	}
	
	public function getId() {
		return $this->id;
	}

	public function getTableId() {
		return $this->tableId;
	}
	
	public function getAccessor() {
		return $this->accessor;
	}
	
	public function getAccessLevel() {
		return $this->accessLevel;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
}

?>