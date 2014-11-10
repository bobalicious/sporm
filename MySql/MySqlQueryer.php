<?php
class MySqlQueryer {
	
	private $sUsername;
	private $sPassword;
	private $sDatabase;
	private $sDatabaseLocation;
	
	private $iQueriesRan = 0;
	
	/**
	 * @var MySqlCommandGenerator
	 */
	private $oCommandGenerator;
	
	function __construct( $aConfig ) {
		
		$this->sUsername         = $aConfig['Username'];
		$this->sPassword         = $aConfig['Password'];
		$this->sDatabase         = $aConfig['Database'];
		$this->sDatabaseLocation = $aConfig['Location'];		
		
		$this->oCommandGenerator = new MySqlCommandGenerator();
	}
	
	private function connect() {
		$this->oDb = mysqli_connect( $this->sDatabaseLocation, $this->sUsername, $this->sPassword, $this->sDatabase );
	}
	
	function getNumberOfQueriesRan() {
		return $this->iQueriesRan;
	}
	
	private function runQuery( OrmConfiguration $oMapping, Filter $oFilter, $oOrderBy = false, $oLimit = false ) {
		$sQuery         = $this->oCommandGenerator->getQuery( $oMapping, $oFilter, $oOrderBy, $oLimit );
		
		$aColumnList    = $oMapping->getColumns();
		$aBindVariables = $oFilter->getFullVariableListWithoutNulls();

		$this->connect();
		$rStatement = $this->oDb->prepare( $sQuery );

		$sBindVars = implode( ',', $aBindVariables );
//		echo( "$sQuery: $sBindVars</br>");

		if ( !$rStatement ) {
			throw( new Exception( 'Error resolving ORM configuration for Select of ' . $oMapping->getObjectType() ) );	
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
		
		$this->connect();
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
	
		
	function getData( OrmConfiguration $oMapping, Filter $oFilter, $oOrderBy = false, $oLimit = false  ) {
		
		$aData = array();

		$this->connect();
		$rResult = $this->runQuery( $oMapping, $oFilter, $oOrderBy, $oLimit );

		if ( $rResult ) {
			$iNumberOfRows = $this->getNumberOfRowsInResultSet( $rResult );
			
			$iResultsIndex=0;
			while ( $iResultsIndex < $iNumberOfRows ) {
			
				$aRow = array();
				foreach ( $oMapping->getColumnAttributeMappings() as $sAttributeName => $sColumnName ) {
					$aRow[ $sAttributeName ] = $this->getValueFromResultSet( $rResult, $iResultsIndex, $sColumnName );		
				}
				
				$iResultsIndex++;
				$aData[] = $oMapping->buildReturnObject( $aRow );
			}
		}
				
		return $aData;
	}
	
	function insertData( OrmConfiguration $oMapping, LoadableObject $oObject ) {
		
		// TODO: move statement generation into the other object
		
		$aRawData    = $oMapping->buildRawData( $oObject );
		$sBaseTable  = $oMapping->getBaseTable();
		$sColumnList = implode( array_keys( $aRawData ), ", " );
		
		$aBindVariableNames = array();
		foreach( $aRawData as $sColumnName ) {
			$aBindVariableNames[] = '?';
		}
		$sBindVariableList  = implode( $aBindVariableNames, ", " );
		
		$sInsert = "INSERT INTO $sBaseTable ( $sColumnList ) VALUES ( $sBindVariableList );";
		$this->executeStatement( $sInsert, $aRawData, 'update of ' . get_class( $oObject ) );		
		
	}	
	
	function deleteData( OrmConfiguration $oMapping, LoadableObject $oObject ) {
		
		$sBaseTable = $oMapping->getBaseTable();
		
		$iIdLookup   = $oMapping->getIdField() . ' = ?'; 
		$aRawData    = array( $oObject->getId() );
		
		$sDelete     = "DELETE FROM $sBaseTable WHERE $iIdLookup;";
		
		$this->executeStatement( $sDelete, $aRawData, 'delete of ' . get_class( $oObject ) );		
		
	}	
	
	function updateData( OrmConfiguration $oMapping, LoadableObject $oObject ) {
		
		$sBaseTable = $oMapping->getBaseTable();
		$aRawData   = $oMapping->buildRawData( $oObject );
		
		$aColumnNames = array_keys( $aRawData );
		
		foreach ( $aColumnNames as $sThisColumnName ) {
			$aColumnList[] = $sThisColumnName .' = ?'; 
		}
		
		$sColumnList = implode( $aColumnList, ", " );
		
		$iIdLookup   = $oMapping->getIdField() . ' = ?'; 
		$aRawData[]  = $oObject->getId();
		
		// TODO: don't update the ID field...
		$sUpdate = "UPDATE $sBaseTable SET $sColumnList WHERE $iIdLookup;";
		$this->executeStatement( $sUpdate, $aRawData, 'update of ' . get_class( $oObject ) );		
		
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