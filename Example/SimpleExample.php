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


class SimpleClassToBeStoredOrmConfiguration extends OrmConfiguration {
	
	function __construct( $sBaseTable = 'simple_class_to_be_stored' ) {
		
		parent::__construct( $sBaseTable
							, array( 'id'
								   , 'some_data'
								   , 'some_other_data'
								   ) );
	}
	
	function buildRawData( $oObject ) {
		return array( 'id'                 => $oObject->getId()
					, 'some_data'          => $oObject->getSomeData()
					, 'some_other_data'    => $oObject->getSomeOtherData()
					);
	}
	
	function buildReturnObject( $aData ) {
		
		$oObject  = new SimpleClassToBeStored( $aData['id']
											 , $aData['some_data']
						  		        	 , $aData['some_other_data']
			  		        				 );
						  		
		return $oObject;
	}
	
	function buildInvalidObject(  $sId = false  ) {
		
		return new SimpleClassToBeStored( false
										, null
						  		   		, null );
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

