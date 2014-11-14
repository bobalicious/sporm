<?php 
namespace sporm;

class OrmConfigurationGenerator extends OrmConfiguration {
	
	private $sClass;
	private $aMappingConfiguration;
	
	function __construct( $sClass, $sBaseTable, $aMappingConfiguration ) {
		parent::__construct( $sBaseTable, array_keys( $aMappingConfiguration ) );
		$this->sClass = $sClass;
		$this->aMappingConfiguration = $aMappingConfiguration;
	}

	function buildRawData( $oObject ) {
		$aRawData = array();
		foreach ( $this->aMappingConfiguration as $sColumn => $sMethod ) {
			// Needs some crazy error handling
			$aRawData[ $sColumn ] = $oObject->$sMethod();
		}
		return $aRawData;
	}
	
	function buildReturnObject( $aData ) {
		
		$fMapFunction = function( &$sValue , $sKey ) { $sValue =  '$aData["'.$sKey.'"]'; };

		$aParameters = $this->aMappingConfiguration;
		array_walk( $aParameters, $fMapFunction );
		$sParameters = implode( $aParameters, ',' );

		$sCreateStatement = '$oObject = new ' . $this->sClass . " ( $sParameters );";

		eval( $sCreateStatement );
		
		return $oObject;
	}
	
	
	function buildInvalidObject( $sId = false ) {
		
		$sParameters = 'false' . str_repeat( ',null', count( $this->aMappingConfiguration )-1 );

		$sCreateStatement = '$oObject = new ' . $this->sClass . " ( $sParameters );";

		eval( $sCreateStatement );
		
		return $oObject;
	}
	
}