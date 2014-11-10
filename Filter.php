<?php
class Filter {
	
	private $sField;
	private $sValue;
	private $sComparitor;
	
	/**
	 * @var Filter
	 */
	private $oAndFilter;
	
	function __construct( $sField, $oAndFilter = false ) {
		$this->sField     = $sField;
		$this->oAndFilter = $oAndFilter;
	}
	
	function hasAnd() {
		return ( is_object( $this->oAndFilter ) );
	}
	
	function getAnd() {
		return $this->oAndFilter;
	}
	
	/**
	 * @return Filter
	 */
	static function none() {
		$oFilter = new Filter( "1" );
		return $oFilter->isEqualTo( 1 );
	}
	
	/**
	 * @return Filter
	 */
	static function attribute( $sField ) {
		return new Filter( $sField );
	}
	
	/**
	 * @return Filter
	 */
	function andAttribute( $sField ) {
		return new Filter( $sField, $this );
	}
	
	/**
	 * @return Filter
	 */	
	function isEqualTo( $sValue ) {
		$this->sComparitor = '=';
		$this->sValue      = $sValue;
		return $this;
	}
	
	/**
	 * @return Filter
	 */	
	function isNotEqualTo( $sValue ) {
		$this->sComparitor = '!=';
		$this->sValue      = $sValue;
		return $this;
	}
	
	/**
	 * @return Filter
	 */
	function isLessThan( $sValue ) {
		$this->sComparitor = '<';
		$this->sValue      = $sValue;
		return $this;
	}

	/**
	 * @return Filter
	 */
	function isLessThanOrEqualTo( $sValue ) {
		$this->sComparitor = '<=';
		$this->sValue      = $sValue;
		return $this;
	}
	
	/**
	 * @return Filter
	 */
	function isGreaterThan( $sValue ) {
		$this->sComparitor = '>';
		$this->sValue      = $sValue;
		return $this;
	}
	
	/**
	 * @return Filter
	 */
	function isGreaterThanOrEqualTo( $sValue ) {
		$this->sComparitor = '>=';
		$this->sValue      = $sValue;
		return $this;
	}
	
	/**
	 * @return Filter
	 */
	function isNotNull() {
		$this->sComparitor = 'IS NOT NULL';
		$this->sValue      = '!NULL!';
		return $this;
	}
	
	function getValue() {
		return $this->sValue;
	}
	
	function getComparitor() {
		return $this->sComparitor;
	}
	
	function getField() {
		return $this->sField;
	}
	
	function getFullVariableList( $aValues = array() ) {
		$aValues[] = $this->sValue;
		if ( $this->hasAnd() ) {
			$aValues = $this->getAnd()->getFullVariableList( $aValues );		
		}
		return $aValues;
	}
	
	function getFullVariableListWithoutNulls() {
		$aReturnValues = array();
		$aValues = $this->getFullVariableList();
		foreach ( $aValues as $sValue ) {
			if ( $sValue !== '!NULL!' ){
				$aReturnValues[] = $sValue;
			}
		}
		return $aReturnValues;
	}
	
	function getHash() {
		$sHash = $this->getField().$this->getComparitor().$this->getValue();
		$sAnd  = '';
		if ( $this->hasAnd() ) {
			$sAnd = '&'.$this->getAnd()->getHash();
		}
		return $sHash.$sAnd;
	}
}

?>