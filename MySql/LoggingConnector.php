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

		$aBindVariableList       = implode( $aBindVariables, ', ');
		$aFormattedBindVariables = $this->formatBindVariables( $aBindVariables );
		$aBindVariableDatatypes  = $aFormattedBindVariables[0];
		$aBindVariableReferences = $aFormattedBindVariables[1];
		
		echo( "\r\n");
		echo( "Running Query: \r\n");
		echo( "  Query                   : $sQuery: \r\n");
		echo( "  BindVariables           : $aBindVariableList \r\n");
		echo( "  BindVariablesReferences : $aBindVariableReferences \r\n");
		echo( "  BindVariablesDatatypes  : $aBindVariableDatatypes \r\n");
		
		$this->iQueriesRan++;

		// This should be set up in some kind of mock object
		if ( $aBindVariables[0]==9999) {
			return array( array( 'id' => '9999', 'some_data' => 'data', 'some_other_data' => 'other_data' ) );
		}
		
		return array();
	}
	
	function executeStatement( $sStatement, $aBindVariables, $sAction ) {
		
		$aBindVariableList = implode( $aBindVariables, ', ');
		$aFormattedBindVariables = $this->formatBindVariables( $aBindVariables );
		$aBindVariableDatatypes  = $aFormattedBindVariables[0];
		$aBindVariableReferences = $aFormattedBindVariables[1];
		
		echo( "\r\n");
		echo( "Executing Statement: \r\n");
		echo( "  Statement               : $sStatement: \r\n");
		echo( "  BindVariables           : $aBindVariableList \r\n");
		echo( "  BindVariablesReferences : $aBindVariableReferences \r\n");
		echo( "  BindVariablesDatatypes  : $aBindVariableDatatypes \r\n");
		
	}

	function formatBindVariables( $aBindVariableList ) {
	
		if ( count( $aBindVariableList ) > 0 ) {
	
			$aBindVariableCallStringElements = array();
			$aBindVariableTypes              = array();
				
			foreach( $aBindVariableList as $sBindVariableDataKey => $sBindVariableDataElement ) {
				$aBindVariableCallStringElements[] = '$aBindVariableList["'.$sBindVariableDataKey.'"]';
				// Currently only supports strings...
				$aBindVariableTypes[] = 's';
			}
			$sBindVariableCallString = implode( $aBindVariableCallStringElements, ", " );
			$sBindVariableTypes      = implode( $aBindVariableTypes );
				
			return array( $sBindVariableTypes, $sBindVariableCallString );
		}
	}

}
?>