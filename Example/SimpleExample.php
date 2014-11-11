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
		
		// Not quite sure why I had this mapping?  Maybe useful for datatypes
		// Move out into configuration though...?
		parent::__construct( $sBaseTable
							, array( 'id'                 => 'id'
								   , 'some_data'          => 'some_data'
								   , 'some_other_data'    => 'some_other_data'
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
						  		        	 , $aData['some_data']
			  		        				 );
						  		
		return $oObject;
	}
	
	function buildInvalidObject(  $sId = false  ) {
		
		return new SimpleClassToBeStored( false
										, null
						  		   		, null );
	}
}




// This is how I'd LIKE to use it, not how it can be used now...

$aConfiguration = array( 'DatabaseType' => DatabaseConfiguration::MY_SQL
						, 'Username'     => 'Username'
						, 'Password'     => 'Password'
						, 'Database'     => 'Database'
						, 'Location'     => 'Location'
						);

$oDatabaseConfiguration = DatabaseReader::registerConfiguration( $aConfiguration );

$oOrmRegister = new OrmRegister();
$oOrmRegister->registerOrmConfigration( SimpleClassToBeStored::OBJECT_TYPE, new SimpleClassToBeStoredOrmConfiguration() );

$oDatabaseReader = DatabaseReader::getInstance( $oOrmRegister );

$oGotById = $oDatabaseReader->getById( 123, SimpleClassToBeStored::OBJECT_TYPE );

$oGotByFiltering = $oDatabaseReader->getData( Filter::attribute('some_data')->isEqualTo('value'), SimpleClassToBeStored::OBJECT_TYPE );

$oGotByComplexFiltering = $oDatabaseReader->getData(
													Filter::attribute('some_data')->isEqualTo('value')
														->andAttribute('some_other_data')->isNotNull()
														->andAttribute('some_other_data')->isNotEqualTo('badValue')
													, SimpleClassToBeStored::OBJECT_TYPE );

// Writing data back

$oGotById->setSomeData('changed');
$oDatabaseReader->writeData( $oGotById );



