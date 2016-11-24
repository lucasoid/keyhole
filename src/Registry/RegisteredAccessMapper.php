<?php 

namespace Keyhole\Registry;

class RegisteredAccessMapper extends RegistryMapper {
	
	/**
	 * 
	 * @var string
	 */
	const TABLE_NAME = 'access_registry';
	
	public function setMappedClass() {
		$this->mappedClass = '\Keyhole\Registry\RegisteredAccess';
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
			array('name'=>'accessor', 'type'=>'string', 'options'=>array()),
			array('name'=>'access_level', 'type'=>'string', 'options'=>array()),
			array('name'=>'updated_at', 'type'=>'datetime', 'options'=>array()),
		);
		
	}
	
	public function setMap() {
		$this->map = array(
			array('column'=>'id', 'property'=>'id'),
			array('column'=>'table_id', 'property'=>'tableId'),
			array('column'=>'accessor', 'property'=>'accessor'),
			array('column'=>'access_level', 'property'=>'accessLevel'),
			array('column'=>'updated_at', 'property'=>'updatedAt')
		);
	}
	
	
}