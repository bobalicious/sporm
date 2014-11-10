<?php

// This is how I'd LIKE to use it, not how it can be used now...

$oDatabaseConfiguration = DatabaseReader::registerConfiguration( [ DatabaseType: DatabaseConfiguration::MY_SQL
																 , Username    : 'DatabaseUsername'
																 , Password    : 'DatabasePassword'             ] );
$oDatabaseReader = DatabaseReader::getInstance();

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


class SimpleClassToBeStored extends LoadableObject {

	public const OBJECT_TYPE = __CLASS__;
	private $sId;
	private $sSomeData;
	private $sSomeOtherData;
	
	function __construct( $sId, $sSomeData, $sSomeOtherData ) {
		
		parent::__construct( $sId );
		$this->sSomeData      = $sSomeData;
		$this->sSomeOtherData = $sSomeOtherData;
		
		$this->setIsValid( true );
	}
	
	function getId() {
		return $this->sId;
	}
	
	function getSomeData() {
		return $this->sSomeData;
	}
	
	function getSomeOtherData() {
		return $this->sSomeOtherData;
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
	
	function buildInvalidObject( $sId ) {
		
		return new SimpleClassToBeStored( false
										, null
						  		   		, null );
	}
}