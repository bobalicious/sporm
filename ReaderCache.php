<?php
namespace sporm;

class ReaderCache {
	
	private $aSingleObjectCache;
	private $aFilterCache;
	private $aAlternativeIdsSingleObjectCache;

	/**
	 * @var OrmRegister
	 */
	private $oOrmRegister;
	
	/**
	 * @var ReaderCache
	 */
	
	static private $oInstance;
	
	private function __construct( OrmRegister $oOrmRegister ) {
		$this->clearCache();
		$this->oOrmRegister = $oOrmRegister;
	}
	
	static function getInstance( OrmRegister $oOrmRegister ) {
		if ( !isset( self::$oInstance ) ) {
			self::$oInstance = new ReaderCache( $oOrmRegister );
		}
		return self::$oInstance;
	}
	
	function clearCache() {
		$this->aSingleObjectCache               = array();
		$this->aFilterCache                     = array();
		$this->aAlternativeIdsSingleObjectCache = array();
	}

	// TODO: Group the filter, order by and limit into a query object
	
	function getDataFromCache( $sObjectType, Filter $oFilter, $oOrderBy, $oLimit ) {
		$sId = $this->buildHash( $oFilter, $oOrderBy, $oLimit );
		if ( isset( $this->aFilterCache[ $sObjectType ][ $sId ] ) ) {
			return $this->aFilterCache[ $sObjectType ][ $sId ];
		}
		
		
		$aFields = $this->getEqualsFields( $oFilter );
		if ( $aFields ) {
			$oCompositeId = new CompositeId( $aFields );
//			echo( "Looking in cache based on filter: $oCompositeId <br/>");		
			$oMapping     = $this->oOrmRegister->getOrmConfiguration( $sObjectType );
			if ( $oMapping->isAnAlternativeId( $oCompositeId ) ) {
//				echo( 'Might be in the cache!<br/>');
				if ( $this->singleObjectIsInAlternativeIdCache( $sObjectType, $oCompositeId ) ) {
//					echo( 'Get it from the cache<br/>');
					return array( $this->getSingleObjectFromAlternativeIdCache( $sObjectType, $oCompositeId ) );
				}
//					echo( 'Not in the cache<br/>');
			}
		}	
		
		return false;
	}
	
	function getEqualsFields( Filter $oFilter, $aFilterFields = array() ) {
		if ( $oFilter->getComparitor() == '=' ) {
			$aFilterFields[ $oFilter->getField() ] = $oFilter->getValue();
			
			if ( $oFilter->hasAnd() ) {
				$aFilterFields = $this->getEqualsFields( $oFilter->getAnd(), $aFilterFields );
			}
			return $aFilterFields;
		}
		return false;
	}
	
	function putObjectsIntoCache( $sObjectType, Filter $oFilter, $oOrderBy, $oLimit, $aObjects ) {
		foreach( $aObjects as $oThisObject ) {
			$this->putSingleObjectIntoCache( $sObjectType, $oThisObject );
			$this->putSingleObjectIntoAlternativeIdCache( $sObjectType, $oThisObject );
		}
		$this->putCollectionIntoCache( $sObjectType, $oFilter, $oOrderBy, $oLimit, $aObjects );
	}
	
	function putSingleObjectIntoCache( $sObjectType, $oObject ) {
		$sId = $oObject->getId();
		$this->aSingleObjectCache[ $sObjectType ][ $sId ] = $oObject;
	}
	
	function putSingleObjectIntoAlternativeIdCache( $sObjectType, $oObject ) {
		$oMapping 	= $this->oOrmRegister->getOrmConfiguration( $sObjectType );
		$aAlternativeIds = $oMapping->getAlternativeIds();
		
		foreach( $aAlternativeIds as $oIdDefinition ) {
			$sId = $oIdDefinition->extractIdAsStringFromObject( $oObject );
//			echo( "Adding to alternative id cache: $sObjectType, $oIdDefinition = $sId<br/>");
			$this->aAlternativeIdsSingleObjectCache[ $sObjectType ][ (String)$oIdDefinition ][ $sId ] = $oObject;
		}
	}
	
	function singleObjectIsInAlternativeIdCache( $sObjectType, CompositeId $oIdDefinition ) {
		$sId = $oIdDefinition->extractIdValuesAsString( $oIdDefinition );
//		echo( "Looking in cache for $sObjectType, $oIdDefinition = $sId<br/>");
		return isset( $this->aAlternativeIdsSingleObjectCache[ $sObjectType ][ (String)$oIdDefinition ][ $sId ] );
	}

	function getSingleObjectFromAlternativeIdCache( $sObjectType, CompositeId $oIdDefinition ) {
		$sId = $oIdDefinition->extractIdValuesAsString( $oIdDefinition );
		return $this->aAlternativeIdsSingleObjectCache[ $sObjectType ][ (String)$oIdDefinition ][ $sId ];
	}
	
	function singleObjectIsInCache( $sObjectType, $sId ) {
		$bExists = ( isset( $this->aSingleObjectCache[ $sObjectType ][ $sId ] ) );
		return $bExists;
	}
	
	function getSingleObjectFromCache( $sObjectType, $sId ) {
		return $this->aSingleObjectCache[ $sObjectType ][ $sId ];
	}
	
	private function putCollectionIntoCache( $sObjectType, Filter $oFilter, $oOrderBy, $oLimit, $aObjects ) {
		$this->aFilterCache[ $sObjectType ][ $this->buildHash( $oFilter, $oOrderBy, $oLimit ) ] = $aObjects;
	}
		
	private function buildHash( $oFilter, $oOrderBy, $oLimit )  {
		$sOrderByHash = '';	
		$sLimitHash   = '';	

		if ( $oOrderBy ) {
			$sOrderByHash = $oOrderBy->getHash();	
		}
		if ( $oLimit ) {
			$sLimitHash = $oLimit->getHash();	
		}
		return $oFilter->getHash().';'.$sOrderByHash.';'.$sLimitHash;
	}
	
}

