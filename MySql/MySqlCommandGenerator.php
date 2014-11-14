<?php
namespace sporm\mysql;

class MySqlCommandGenerator {
	
	// TODO: build insert and update statements here...
	
	function getQuery( \sporm\OrmConfiguration $oMapping, \sporm\Filter $oFilter, $oOrderBy = false, $oLimit = false ) {
		$sBaseQuery   = $this->getBaseQuery( $oMapping );
		$sWhereClause = $this->getWhereClause( $oMapping, $oFilter );
		$sOrderBy     = $this->getOrderBy( $oMapping, $oOrderBy );
		$sLimit       = $this->getLimit( $oMapping, $oLimit );
		return $sBaseQuery . $sWhereClause . $sOrderBy . $sLimit;
	}
	
	private function getBaseQuery( \sporm\OrmConfiguration $oMapping ) {
		
		$aColumns   = $oMapping->getColumns();
		$sBaseTable = $oMapping->getBaseTable();
		
		$sQuery       = 'SELECT ' . implode( $aColumns, ',' ) . ' FROM ' . $sBaseTable;
		return $sQuery;
	}
	
	private function getWhereClause( \sporm\OrmConfiguration $oMapping, $oFilter = false  ) {
		
		if ( $oFilter ) {
			$sCondition = $this->getConditionFromFilter( $oMapping, $oFilter );
			return " WHERE $sCondition";
		}
		return '';
	}
	
	private function getOrderBy( \sporm\OrmConfiguration $oMapping, $oOrderBy = false  ) {
		
		if ( $oOrderBy ) {
			return $this->getStringForOrderBy( $oMapping, $oOrderBy );
		}
		return '';
	}
	
	private function getLimit( \sporm\OrmConfiguration $oMapping, $oLimit = false  ) {
		
		if ( $oLimit ) {
			return ' LIMIT '. $oLimit->getNumberOfRecords();
		}	
		return '';
	}

	private function getStringForOrderBy( \sporm\OrmConfiguration $oMapping, \sporm\OrderBy $oOrderBy, $sString = '' ) {
		
		$sDatabaseField = $oOrderBy->getField();
		$sDirection     = $oOrderBy->isAscending()?'ASC':'DESC';
		
		if ( $sString ) {
			$sString = ", $sString";
		}
		
		$sString = "$sDatabaseField $sDirection $sString";
		
		if ( $oOrderBy->hasMore() ) {
			$sString = $this->getStringForOrderBy( $oMapping, $oOrderBy->getMore(), $sString );
		} else {
			return "ORDER BY ISNULL($sDatabaseField), $sString";
		}
		
		return $sString;
	}
	
	private function getConditionFromFilter( \sporm\OrmConfiguration $oMapping, \sporm\Filter $oFilter ) {
		
		$sDatabaseField = $oFilter->getField();
		$sComparitor    = $oFilter->getComparitor();
		$sValue         = $oFilter->getValue();
		
		$sAnd = '';
		if ( $oFilter->hasAnd() ) {
			$sAnd = 'AND ' . $this->getConditionFromFilter( $oMapping, $oFilter->getAnd() );
		}

		if ( $sComparitor == 'IS NOT NULL' ) {
			return "$sDatabaseField IS NOT NULL $sAnd";
		}
		
		return "$sDatabaseField $sComparitor ? $sAnd";
		
	}
	
	
}

?>