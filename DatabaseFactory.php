<?php

class DatabaseFactory {

	public static function buildDatabaseQueryer( $sDatabaseType, $aConfiguration ) {
		
		if ( $sDatabaseType == DatabaseConfiguration::MY_SQL ) {
			return new MySqlQueryer( $aConfiguration, new MySqlCommandGenerator(), new LoggingConnector() );
		}
		
	}
}
?>