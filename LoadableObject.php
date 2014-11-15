<?php
namespace sporm;

class LoadableObject {
	
	protected $sId;
	protected $bIsValid           = true;
	
	function __construct( $sId ) {
		$this->sId         = $sId;
	}
	
	function isValid() {
		return $this->bIsValid;
	}
	
	function setIsValid( $bIsValid ) {
		$this->bIsValid = $bIsValid;
	}
	
	function getId() {
		return $this->sId;
	}
	
	function setId( $sId ) {
		$this->sId = $sId;
	}
	
	// replace with CLASS_NAME
	function getObjectType() {
		return get_class( $this );
	}
}

