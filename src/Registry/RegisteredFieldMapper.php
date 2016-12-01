<?php 

namespace Keyhole\Registry;

class RegisteredFieldMapper extends RegistryMapper {
	
	/**
	 * 
	 * @var string
	 */
	const TABLE_NAME = 'field_registry';
	
	public function setMappedClass() {
		$this->mappedClass = '\Keyhole\Registry\RegisteredField';
	}
	
	public function setTableName() {
		$this->fullTableName = $this->tablePrefix . self::TABLE_NAME;
	}
	
	public function setColumnDefinitions() {
		
		$tableMapper = new RegisteredTableMapper($this->conn, $this->tablePrefix);
		$tableRegistryName = $tableMapper->getTableName();
		
		$this->columnDefinitions = array(
			array('name'=>'id', 'type'=>'integer', 'options'=>array('autoincrement'=>true), 'primarykey'=>true),
			array('name'=>'table_id', 'type'=>'integer', 'options'=>array(), 'foreignkey'=>array($tableRegistryName, array('id'), array('onDelete'=>'CASCADE'))),
			array('name'=>'name', 'type'=>'string', 'options'=>array()),
			array('name'=>'label', 'type'=>'string', 'options'=>array()),
			array('name'=>'options', 'type'=>'json_array', 'options'=>array()),
			array('name'=>'fieldtype', 'type'=>'string', 'options'=>array()),
			array('name'=>'meta', 'type'=>'json_array', 'options'=>array('notnull'=>false)),
			array('name'=>'updated_at', 'type'=>'datetime', 'options'=>array()),
		);
		
	}
	
	public function setMap() {
		$this->map = array(
			array('column'=>'id', 'property'=>'id'),
			array('column'=>'table_id', 'property'=>'tableId'),
			array('column'=>'name', 'property'=>'name'),
			array('column'=>'label', 'property'=>'label'),
			array('column'=>'options', 'property'=>'options'),
			array('column'=>'fieldtype', 'property'=>'fieldtype'),
			array('column'=>'meta', 'property'=>'active'),
			array('column'=>'updated_at', 'property'=>'updatedAt')
		);
	}
	
	
}