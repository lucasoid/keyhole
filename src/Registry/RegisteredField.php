<?php 

namespace Keyhole\Registry;

class RegisteredField extends RegistryEntity {
	
	protected $id;
	protected $tableId;
	protected $name;
	protected $label;
	protected $options;
	protected $fieldtype;
	protected $meta;
	protected $updatedAt;
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function setTableId($tableId) {
		$this->tableId = $tableId;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function setLabel($label) {
		$this->label = $label;
	}
	
	/**
	 *
	 * @param string $options JSON-encoded array
	 */
	public function setOptions($options) {
		$this->options = $options;
	}
	
	public function setFieldtype($fieldtype) {
		$this->fieldtype = $fieldtype;
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

	public function getTableId() {
		return $this->tableId;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getLabel() {
		return $this->label;
	}
	
	public function getOptions() {
		return $this->options;
	}

	public function getFieldtype() {
		return $this->fieldtype;
	}
	
	public function getMeta() {
		return $this->meta;
	}
	
	public function getUpdatedAt() {
		return $this->updatedAt;
	}
	
}

?>