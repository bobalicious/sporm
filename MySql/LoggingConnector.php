<?php

class LoggingConnector {
	
	function connect( $sDatabaseLocation, $sUsername, $sPassword, $sDatabase ) {
		echo( "\r\n");
		echo( "Connecting: \r\n");
		echo( "  sDatabaseLocation: $sDatabaseLocation\r\n");
		echo( "  sUsername        : $sUsername\r\n");
		echo( "  sPassword        : $sPassword\r\n");
		echo( "  sDatabase        : $sDatabase\r\n");
	}
	
	function getNumberOfQueriesRan() {
		return $this->iQueriesRan;
	}
	
	function runQuery( $sQuery, $aBindVariables ) {

		$aFormattedBindVariables = $this->formatBindVariables( $aBindVariables );
		$aBindVariableList = implode( $aBindVariables, ', ');
		
		echo( "\r\n");
		echo( "Running Query: \r\n");
		echo( "  Query        : $sQuery: \r\n");
		echo( "  BindVariables: $aBindVariableList \r\n");

		$this->iQueriesRan++;
		
		return array();
	}
	
	function executeStatement( $sStatement, $aBindVariables, $sAction ) {
		
		echo( "\r\n");
		echo( "Executing Statement: \r\n");
		echo( "  Statement    : $sStatement: \r\n");
		echo( "  BindVariables: $sBindVars: \r\n");

	}

	function formatBindVariables( $aBindVariableList ) {
	
		if ( count( $aBindVariableList ) > 0 ) {
	
			$aBindVariableCallStringElements = array();
			$aBindVariableTypes              = array();
				
			foreach( $aBindVariableList as $sBindVariableDataKey => $sBindVariableDataElement ) {
				$aBindVariableCallStringElements[] = '$aBindVariableList["'.$sBindVariableDataKey.'"]';
				$aBindVariableTypes[] = 's';
			}
			$sBindVariableCallString = implode( $aBindVariableCallStringElements, ", " );
			$sBindVariableTypes      = implode( $aBindVariableTypes );
				
			return array( $sBindVariableTypes, $sBindVariableCallString );
		}
	}

}
?>