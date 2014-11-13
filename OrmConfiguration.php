<?php

class CompositeId {
	
	private $aIds; // [id] => function to get value, or the value
	
	function __construct( $aIds ) {
		$aIdNames = array_keys( $aIds );
		asort( $aIdNames );
		foreach( $aIdNames as $sIdName ) {
			$this->aIds[$sIdName] = $aIds[$sIdName];
		}
		$this->aIds = $aIds;
	}
	
	function extractIdAsStringFromObject( $oObject ) {
		$aValues = array();
		foreach( $this->aIds as $sFunctionToGetId ) {
			$aValues[] = $oObject->$sFunctionToGetId();
		}
		return implode( ",", $aValues );
	}
	
	function extractIdValuesAsString() {
		$aValues = array();
		foreach( $this->aIds as $sIdName => $sValue ) {
			$aValues[] = $sValue;
		}
		return implode( ",", $aValues );
	}
	
	function __toString() {
		return implode( ",", array_keys( $this->aIds ) );
	}
	
}


abstract class OrmConfiguration {
	
	private $aColumns;
	private $sBaseTable;
	private $sIdField    = 'id';
	private $sObjectType;
	private $aAlternativeIds = array();
	private $aAlternativeIdsAsKey = array();
	
	/**
	 * @var DatabaseReader
	 */
	protected $oDatabaseReader;
	private   $bDeleteOnUpdates  = false;
	
	function __construct( $sBaseTable, $aColumns ) {
		$this->aColumns        = $aColumns;
		$this->sBaseTable      = $sBaseTable;
	}

	abstract function buildReturnObject( $aData );
	abstract function buildInvalidObject( $sId = false );
	abstract function buildRawData( $oObject );

	function setObjectType( $sObjectType) {
		$this->sObjectType = $sObjectType;
	}
	
	function getObjectType() {
		return $this->sObjectType;
	}
	
	function setIdField( $sIdField ) {
		$this->sIdField = $sIdField;
	}

	function getIdField() {
		return $this->sIdField;
	}
	
	function addAlternativeId( $oId ) {
		$this->aAlternativeIdsAsKey[] = (String)$oId;
		$this->aAlternativeIds[]      = $oId;
	}
	
	function isAnAlternativeId( CompositeId $oId ) {
		return ( in_array( (String)$oId, $this->aAlternativeIdsAsKey ) );
	}
	
	function getAlternativeIds() {
		return $this->aAlternativeIds;
	}
	
	function setDatabaseReader( DatabaseReader $oDatabaseReader ) {
		$this->oDatabaseReader = $oDatabaseReader;
	}
	
	function getColumns() {
		return $this->aColumns;
	}
	
	function getBaseTable() {
		return $this->sBaseTable;
	}
	
	function deleteOnUpdates() {
		return $this->bDeleteOnUpdates;	
	}
	
	function setDeleteOnUpdates() {
		$this->bDeleteOnUpdates = true;
	}
	
}
?>