<?php 

namespace Keyhole\Registry;

class RegisteredTableMapper extends RegistryMapper {

	/**
	 * 
	 * @var string
	 */
	const TABLE_NAME = 'table_registry';
	
	public function setMappedClass() {
		$this->mappedClass = '\Keyhole\Registry\RegisteredTable';
	}
			
	public function setTableName() {
		$this->fullTableName = $this->tablePrefix . self::TABLE_NAME;
	}
		
	public function setColumnDefinitions() {
		
		$this->columnDefinitions = array(
			array('name'=>'id', 'type'=>'integer', 'options'=>array('autoincrement'=>true), 'primarykey'=>true),
			array('name'=>'name', 'type'=>'string', 'options'=>array('length'=>255, 'customSchemaOptions'=>array('unique'=>true))),
			array('name'=>'label', 'type'=>'string', 'options'=>array()),
			array('name'=>'meta', 'type'=>'json_array', 'options'=>array('notnull'=>false)),
			array('name'=>'updated_at', 'type'=>'datetime', 'options'=>array()),
		);
		
	}
	
	public function setMap() {
		$this->map = array(
			array('column'=>'id', 'property'=>'id'),
			array('column'=>'name', 'property'=>'name'),
			array('column'=>'label', 'property'=>'label'),
			array('column'=>'meta', 'property'=>'meta'),
			array('column'=>'updated_at', 'property'=>'updatedAt')
		);
	}
	
}