<?php 

namespace Keyhole\Registry;

abstract class RegistryEntity {
	
	protected $id;
	protected $readyToUpdate = array();
	
	abstract public function setId($id);
	
	abstract public function getId();
	
	public function observe($property, $value) {
		if(property_exists($this, $property)) {
			$method = 'set' . ucwords($property);
			if(method_exists($this, $method)) {
				$this->{$method}($value);
				$this->readyToUpdate[] = $property;
			}
		}
	}
	
	public function getReadyToUpdate() {
		return $this->readyToUpdate;
	}
	
}