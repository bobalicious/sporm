<?php

require_once( '../SplClassLoader.php' );

$myLibLoader = new SplClassLoader('sporm', '../..');
$myLibLoader->setNamespaceSeparator('\\');
$myLibLoader->register();




class SimpleClassToBeStored extends \sporm\LoadableObject {

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

// Create a configuration for the class you're going to store

$oOrmConfiguration = new \sporm\OrmConfigurationGenerator( 'SimpleClassToBeStored'
										                  , 'simple_class_to_be_stored'
														  , array( 'id'              => 'getId'
															     , 'some_data'       => 'getSomeData'
																 , 'some_other_data' => 'getSomeOtherData'
																 ) );

// Register it with an OrmRegister
$oOrmRegister = new \sporm\OrmRegister();
$oOrmRegister->registerOrmConfigration( SimpleClassToBeStored::OBJECT_TYPE, $oOrmConfiguration );
														 

// Create a database configuration
$aConfiguration = array( 'DatabaseType' => \sporm\DatabaseConfiguration::MY_SQL
						, 'Username'     => 'Username'
						, 'Password'     => 'Password'
						, 'Database'     => 'Database'
						, 'Location'     => 'Location'
						);

// Register the database configuration

\sporm\DatabaseReader::registerConfiguration( $aConfiguration );





echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will connect to the database\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oDatabaseReader = \sporm\DatabaseReader::getInstance( $oOrmRegister );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will create a simple select by ID\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotById = $oDatabaseReader->getById( 123, SimpleClassToBeStored::OBJECT_TYPE );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will create a simple select\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotByFiltering = $oDatabaseReader->getData( \sporm\Filter::attribute('some_data')->isEqualTo('value'), SimpleClassToBeStored::OBJECT_TYPE );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will create a multiple where claused select\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotByComplexFiltering = $oDatabaseReader->getData(
													\sporm\Filter::attribute('some_data')->isEqualTo('value')
														->andAttribute('some_other_data')->isNotNull()
														->andAttribute('some_other_data')->isNotEqualTo('badValue')
													, SimpleClassToBeStored::OBJECT_TYPE );

echo( "\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );
echo( "Will select all the data from a table\r\n" );
echo( "-----------------------------------------------------------------------------------------\r\n" );

$oGotByFiltering = $oDatabaseReader->getData( \sporm\Filter::none(), SimpleClassToBeStored::OBJECT_TYPE );

													
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

$oOrmConfiguration->setDeleteOnUpdates();
$oDatabaseReader->writeData( $oObject );

