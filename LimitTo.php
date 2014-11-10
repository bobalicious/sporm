<?php
class LimitTo {
	
	private $iMaximum;
	
	function __construct( $iMaximum ) {
		$this->iMaximum = $iMaximum;
	}
	
	/**
	 * @return LimitTo
	 */
	static function numberOfRecords( $iMaximum ) {
		return new LimitTo( $iMaximum );
	}
	
	function getNumberOfRecords() {
		return $this->iMaximum;
	}

	function getHash() {
		return $this->getNumberOfRecords();
	}
}

?>