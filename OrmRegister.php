<?php
namespace sporm;

class OrmRegister {
	
	private $aRegisteredObjects;
	
	function __construct() {
		$this->aRegisteredObjects = array();
	}

	function registerOrmConfigration( $sObjectType, $sOrmConfiguration ) {
		$this->aRegisteredObjects[ $sObjectType ] = $sOrmConfiguration;
	}
	
	function getOrmConfiguration( $sObjectType ) {
		if ( isset( $this->aRegisteredObjects[ $sObjectType ] ) ) {
			// TODO: this is kind of backwards...
			$this->aRegisteredObjects[ $sObjectType ]->setObjectType( $sObjectType );
			return $this->aRegisteredObjects[ $sObjectType ];
		}		
		// TODO: Else return an invalid Orm		
	}
}

?>