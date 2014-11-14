<?php
namespace sporm;

class OrderBy {
	
	private $sField;
	private $bIsAscending;
	
	/**
	 * @var OrderBy
	 */
	private $oMoreOrderBy;
	
	function __construct( $sField, $oMoreOrderBy = false ) {
		$this->sField       = $sField;
		$this->oMoreOrderBy = $oMoreOrderBy;
	}
	
	function hasMore() {
		return ( is_object( $this->oMoreOrderBy ) );
	}
	
	function getMore() {
		return $this->oMoreOrderBy;
	}
	
	/**
	 * @return OrderBy
	 */
	static function attribute( $sField ) {
		return new OrderBy( $sField );
	}
	
	/**
	 * @return OrderBy
	 */
	function thenAttribute( $sField ) {
		return new OrderBy( $sField, $this );
	}
	
	/**
	 * @return OrderBy
	 */	
	function ascending() {
		$this->bIsAscending = true;
		return $this;
	}
	
	function descending() {
		$this->bIsAscending = false;
		return $this;
	}
	
	function isAscending() {
		return $this->bIsAscending;
	}
	
	function getField() {
		return $this->sField;
	}

	function getHash() {
		$sHash = $this->getField().$this->isAscending()?'ASC':'DESC';
		$sMore  = '';
		if ( $this->hasMore() ) {
			$sMore = ','.$this->getMore()->getHash();
		}
		return $sHash.$sMore;
	}
}

?>