<?php
namespace sporm\mysql;

class MySqlConnector {
	
	private $oDb = false;
	
	function connect( $sDatabaseLocation, $sUsername, $sPassword, $sDatabase ) {
		$this->oDb = mysqli_connect( $sDatabaseLocation, $sUsername, $sPassword, $sDatabase );
	}
	
	function getNumberOfQueriesRan() {
		return $this->iQueriesRan;
	}
	
	function runQuery( $sQuery, $aBindVariables ) {

		$rStatement = $this->oDb->prepare( $sQuery );

		if ( !$rStatement ) {
			throw( new Exception( 'Big big boom boom bang' ) );	
		}
		
		$this->bindVariables( $aBindVariables, $rStatement );
		$rStatement->execute();

		$aFullResults = array();
		$aResultRow   = array();
		$this->bindResults( $aColumnList, $rStatement, $aResultRow );
		
		while( $rStatement->fetch() ) {
			$aFullResults[] = $this->cloneArray( $aResultRow );
		}
		
		$rStatement->close();

		$this->iQueriesRan++;
		
		return $aFullResults;
	}
	
	private function cloneArray( $aArray ) {
		
		$aNewArray = array();
		foreach ( $aArray as $sKey => $sValue ) {
			$aNewArray[$sKey] = $sValue;
		}
		return $aNewArray;
	}

	
	function executeStatement( $sStatement, $aBindVariables, $sAction ) {
		
		$rStatement = $this->oDb->prepare( $sStatement );

		if ( !$rStatement ) {
			throw( new Exception( 'Error resolving ORM configuration for ' . $sAction ) );	
		}
		
		$this->bindVariables( $aBindVariables, $rStatement );

		$bSuccess = $rStatement->execute();

		// TODO: error handling
		$rStatement->close();
	
		return $bSuccess;
	
	}

	private function getNumberOfRowsInResultSet( $rResult ) {
		return count( $rResult );
	}
	
	private function getValueFromResultSet( $rResult, $iResultsIndex, $sColumnName ) {
		return $rResult[$iResultsIndex][$sColumnName];
	}
	
	function bindVariables( $aBindVariableList, $rStatement ) {

		if ( count( $aBindVariableList ) > 0 ) {
		
			$aBindVariableCallStringElements = array();
			$aBindVariableTypes              = array();
			
			foreach( $aBindVariableList as $sBindVariableDataKey => $sBindVariableDataElement ) {
				$aBindVariableCallStringElements[] = '$aBindVariableList["'.$sBindVariableDataKey.'"]';
				$aBindVariableTypes[] = 's';
			}
			$sBindVariableCallString = implode( $aBindVariableCallStringElements, ", " );
			$sBindVariableTypes      = implode( $aBindVariableTypes );
			
			$sBindCommand = '$rStatement->bind_param( $sBindVariableTypes, '.$sBindVariableCallString.' );';
			eval( $sBindCommand ); // TODO: only handles strings.
		}
	}
	
	function bindResults( $aResultColumnList, $rStatement, &$aResultsRow ) {

		if ( count( $aResultColumnList ) > 0 ) {
			
			foreach( $aResultColumnList as $sColumnName ) {
				$aBindResultVariable[] = '$aResultsRow[\''.$sColumnName.'\']';
			}
			$sBindVariableCallString = implode( $aBindResultVariable, ", " );
			$sBindCommand = '$rStatement->bind_result( '.$sBindVariableCallString.' );';
			eval( $sBindCommand );
		}
	}
}
?>