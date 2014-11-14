<?php
namespace sporm\mysql;

class MySqlQueryer {
	
	private $sUsername;
	private $sPassword;
	private $sDatabase;
	private $sDatabaseLocation;
	
	/**
	 * @var MySqlCommandGenerator
	 */
	private $oCommandGenerator;

	/**
	* @var MySqlConnector
	*/
	private $oConnector;
	
	function __construct( $aConfig, $oCommandGenerator, $oConnector ) {
		
		$this->sUsername         = $aConfig['Username'];
		$this->sPassword         = $aConfig['Password'];
		$this->sDatabase         = $aConfig['Database'];
		$this->sDatabaseLocation = $aConfig['Location'];		
		
		$this->oCommandGenerator = $oCommandGenerator;
		$this->oConnector        = $oConnector;
		$this->connect();
	}
	
	private function connect() {
		$this->oConnector->connect( $this->sDatabaseLocation, $this->sUsername, $this->sPassword, $this->sDatabase );
	}
	
	function getNumberOfQueriesRan() {
		return $this->iQueriesRan;
	}
	
	private function runQuery( \sporm\OrmConfiguration $oMapping, \sporm\Filter $oFilter, $oOrderBy = false, $oLimit = false ) {
		$sQuery         = $this->oCommandGenerator->getQuery( $oMapping, $oFilter, $oOrderBy, $oLimit );
		
		$aBindVariables = $oFilter->getFullVariableListWithoutNulls();

		$sBindVars = implode( ',', $aBindVariables );

		return $this->oConnector->runQuery($sQuery, $aBindVariables);
	}
	
	private function getNumberOfRowsInResultSet( $rResult ) {
		return count( $rResult );
	}
	
	private function getValueFromResultSet( $rResult, $iResultsIndex, $sColumnName ) {
		
		// This is a nonsense to deal with shitty assignment of array keys in PHP
		if ( isset( $rResult[$iResultsIndex][$sColumnName] ) ) {
			return $rResult[$iResultsIndex][$sColumnName];
		} elseif ( isset( $rResult[$iResultsIndex][(int)$sColumnName] ) ) {
			return $rResult[$iResultsIndex][(int)$sColumnName];
		}
		return false;
	}
	
		
	function getData( \sporm\OrmConfiguration $oMapping, \sporm\Filter $oFilter, $oOrderBy = false, $oLimit = false  ) {
		
		$aData = array();

		$rResult = $this->runQuery( $oMapping, $oFilter, $oOrderBy, $oLimit );

		if ( $rResult ) {
			$iNumberOfRows = $this->getNumberOfRowsInResultSet( $rResult );
			
			$iResultsIndex=0;
			while ( $iResultsIndex < $iNumberOfRows ) {
			
				$aRow = array();
				foreach ( $oMapping->getColumns() as $sColumnName ) {
					$aRow[ $sColumnName ] = $this->getValueFromResultSet( $rResult, $iResultsIndex, $sColumnName );		
				}
				
				$iResultsIndex++;
				$aData[] = $oMapping->buildReturnObject( $aRow );
			}
		}
				
		return $aData;
	}
	
	function insertData( \sporm\OrmConfiguration $oMapping, \sporm\LoadableObject $oObject ) {
		
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
		
		$this->oConnector->executeStatement( $sInsert, $aRawData, 'update of ' . get_class( $oObject ) );		
		
	}	
	
	function deleteData( \sporm\OrmConfiguration $oMapping, \sporm\LoadableObject $oObject ) {
		
		$sBaseTable = $oMapping->getBaseTable();
		
		$iIdLookup   = $oMapping->getIdField() . ' = ?'; 
		$aRawData    = array( $oObject->getId() );
		
		$sDelete     = "DELETE FROM $sBaseTable WHERE $iIdLookup;";
		
		$this->oConnector->executeStatement( $sDelete, $aRawData, 'delete of ' . get_class( $oObject ) );		
		
	}	
	
	function updateData( \sporm\OrmConfiguration $oMapping, \sporm\LoadableObject $oObject ) {
		
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
		
		$this->oConnector->executeStatement( $sUpdate, $aRawData, 'update of ' . get_class( $oObject ) );		
		
	}
	
}
?>