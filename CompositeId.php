<?php
namespace sporm;

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
