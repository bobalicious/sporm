<?php

require_once( '../IncludeDatabaseConnection.php' );

class SimpleClassToBeStored extends LoadableObject {

	const OBJECT_TYPE = __CLASS__;
	private $sSomeData;
	private $sSomeOtherData;
	
	function __construct( $sId, $sSomeData, $sSomeOtherData ) {
		
		parent::__construct( $sId );
		$this->sSomeData      = $sSomeData;
		$this->sSomeOtherData = $sSomeOtherData;
		
		$this->setIsValid( true );
	}
	
	function getSomeData() {
		return $this->sSomeData;
	}
	
	function setSomeData( $sData ) {
		$this->sSomeData = $sData;
	}
	
	function getSomeOtherData() {
		return $this->sSomeOtherData;
	}
	
	function setSomeOtherData( $sData ) {
		$this->sSomeOtherData = $sData;
	}
}

// The configuration for the class that you are going to store

// Badly named class
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



class SimpleClassToBeStoredOrmConfiguration extends OrmConfigurationGenerator {
	
	function __construct( $sBaseTable = 'simple_class_to_be_stored' ) {
		
		parent::__construct( 'SimpleClassToBeStored'
		                    , $sBaseTable
							, array( 'id'              => 'getId'
								   , 'some_data'       => 'getSomeData'
								   , 'some_other_data' => 'getSomeOtherData'
								   ) );
	}

}




$aConfiguration = array( 'DatabaseType' => DatabaseConfiguration::MY_SQL
						, 'Username'     => 'Username'
						, 'Password'     => 'Password'
						, 'Database'     => 'Database'
						, 'Location'     => 'Location'
						);

$oDatabaseConfiguration = DatabaseReader::registerConfiguration( $aConfiguration );

$oOrmRegister = new OrmRegister();
$oOrmRegister->registerOrmConfigration( SimpleClassToBeStored::OBJECT_TYPE, new SimpleClassToBeStoredOrmConfiguration() );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will connect to the database\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oDatabaseReader = DatabaseReader::getInstance( $oOrmRegister );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will create a simple select by ID\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotById = $oDatabaseReader->getById( 123, SimpleClassToBeStored::OBJECT_TYPE );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will create a simple select\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotByFiltering = $oDatabaseReader->getData( Filter::attribute('some_data')->isEqualTo('value'), SimpleClassToBeStored::OBJECT_TYPE );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will create a multiple where claused select\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotByComplexFiltering = $oDatabaseReader->getData(
													Filter::attribute('some_data')->isEqualTo('value')
														->andAttribute('some_other_data')->isNotNull()
														->andAttribute('some_other_data')->isNotEqualTo('badValue')
													, SimpleClassToBeStored::OBJECT_TYPE );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will select all the data from a table\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotByFiltering = $oDatabaseReader->getData( Filter::none(), SimpleClassToBeStored::OBJECT_TYPE );

													
// Writing data back

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will check for existence, then insert\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
													
$oObject = new SimpleClassToBeStored( 999, "data", "other data" );  // will create an insert
$oDatabaseReader->writeData( $oObject );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will check for existence, then update\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oObject = new SimpleClassToBeStored( 9999, "updated data", "updated other data" );  // will create an insert (because of the mocking of 9999)
$oDatabaseReader->writeData( $oObject );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will check for existence, then delete and re-insert\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oDeleteOnUpdates = new SimpleClassToBeStoredOrmConfiguration();
$oDeleteOnUpdates->setDeleteOnUpdates();

$oOrmRegister->registerOrmConfigration( SimpleClassToBeStored::OBJECT_TYPE, $oDeleteOnUpdates );
$oDatabaseReader->writeData( $oObject );

