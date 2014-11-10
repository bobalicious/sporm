<?php

class DatabaseReader {
	
	//TODO: reverse the objects for reading and caching, sort of...
	
	/**
	 * @var MySqlQueryer
	 */
	private $oQueryer;
	
	/**
	 * @var OrmRegister
	 */
	private $oOrmRegister;
	
	/**
	 * @var ReaderCache
	 */
	private $oCache;
	
	static private $oInstance;
	
	private function __construct() {
		// TODO: could be driven by config...
		$this->oQueryer 	= new MySqlQueryer();
		$this->oOrmRegister = new OrmRegister();
		$this->oCache       = ReaderCache::getInstance( $this->oOrmRegister );
	}

	/**
	 * @return DatabaseReader
	 */
	static function getInstance() {
		if ( !isset( self::$oInstance ) ) {
			self::$oInstance = new DatabaseReader();
			self::$oInstance->prepareCache();
		}
		return self::$oInstance;
	}	

	function prepareCache() {
		// Pre-load the cache with the teams
		$aTeams    = ApplicationState::getObjectRegister()->getData( Filter::none(), 'Team' );
	}
	
	function getNumberOfQueriesRan() {
		return $this->oQueryer->getNumberOfQueriesRan();
	}

	function exists( $sId, $sObjectType ) {
		
		$oMapping 		= $this->oOrmRegister->getOrmConfiguration( $sObjectType );
		$sIdField       = $oMapping->getIdField();
		$oFilter		= Filter::attribute( $sIdField )->isEqualTo( $sId  );
		
		$aReturnList	= $this->getDataUsingMapping( $oMapping, $oFilter );

		if ( count( $aReturnList ) == 1 ) {
			return true;
		}
		
		return false;
	}
	
	function filterMatchesARecord( $oFilter, $sObjectType ) {

		$oMapping 		= $this->oOrmRegister->getOrmConfiguration( $sObjectType );
		$aReturnList	= $this->getDataUsingMapping( $oMapping, $oFilter );

		if ( count( $aReturnList ) == 1 ) {
			return true;
		}
		
		return false;
	}
	
	function getById( $sId, $sObjectType ) {
		
		if ( $this->oCache->singleObjectIsInCache( $sObjectType, $sId ) ) {
			return $this->oCache->getSingleObjectFromCache( $sObjectType, $sId );
		}

		$oMapping       = $this->oOrmRegister->getOrmConfiguration( $sObjectType );
				
		$sIdField       = $oMapping->getIdField();
		$oFilter		= Filter::attribute( $sIdField )->isEqualTo( $sId  );

		$aReturnList	= $this->getData( $oFilter, $sObjectType );

		if ( count( $aReturnList ) == 1 ) {
			return $aReturnList[0];
		}
		
		$oInvalidObject = $oMapping->buildInvalidObject( $sId );
		$oInvalidObject->setIsValid( false );
		return $oInvalidObject;
	}
	
	function getData( Filter $oFilter, $sObjectType, $oOrderBy = false, $oLimit = false ) {
		$oMapping = $this->oOrmRegister->getOrmConfiguration( $sObjectType );
		return $this->getDataUsingMapping( $oMapping, $oFilter, $oOrderBy, $oLimit );
	}
	
	private function getDataUsingMapping( OrmConfiguration $oMapping, Filter $oFilter, $oOrderBy = false, $oLimit = false ) {
		$sObjectType = $oMapping->getObjectType();

		$aCachedData = $this->oCache->getDataFromCache( $oMapping->getObjectType(), $oFilter, $oOrderBy, $oLimit );

		if ( $aCachedData ) {
			return $aCachedData;
		}
		
		$oMapping->setDatabaseReader( $this );
		$aData = $this->oQueryer->getData( $oMapping, $oFilter, $oOrderBy, $oLimit );
		foreach ( $aData as $iKey => $oObject ) {
			if ( $this->oCache->singleObjectIsInCache( $oMapping->getObjectType(), $oObject->getId() ) ) {
				$aData[ $iKey ] = $this->oCache->getSingleObjectFromCache( $oMapping->getObjectType(), $oObject->getId() );
			}
		}
		
		$this->oCache->putObjectsIntoCache( $sObjectType, $oFilter, $oOrderBy, $oLimit, $aData );
		
		return $aData;
	}
	
	function writeData( LoadableObject $oObject, $bClearCache = true ) {
		// TODO: sort out writing into the cache
		// TODO: Should be able to do something to work out what the ID is an put it onto the object
		// TODO: If this fails at the database level it doesn't throw an exception...
		if ( $oObject->isValid() ) {
			
			// TODO: get this from the ORM register?
			$sObjectType = $oObject->getObjectType();
			
			$oMapping = $this->oOrmRegister->getOrmConfiguration( $sObjectType );	
			if ( $this->exists( $oObject->getId(), $sObjectType  ) ) {
				if ( $oMapping->deleteOnUpdates() ) {
					$this->oQueryer->deleteData( $oMapping, $oObject );
					$this->oQueryer->insertData( $oMapping, $oObject );
				} else {
					$this->oQueryer->updateData( $oMapping, $oObject );
				}
			} else {
				$this->oQueryer->insertData( $oMapping, $oObject );
			}
		}
		
		if ( $bClearCache ) {
			$this->oCache->clearCache();
			$this->prepareCache();
		}
		
		return true;
	}
}
?>