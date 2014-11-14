<?php
namespace sporm;

class DatabaseFactory {

	public static function buildDatabaseQueryer( $sDatabaseType, $aConfiguration ) {
		
		if ( $sDatabaseType == DatabaseConfiguration::MY_SQL ) {
			return new \sporm\mysql\MySqlQueryer( $aConfiguration, new \sporm\mysql\MySqlCommandGenerator(), new \sporm\mysql\LoggingConnector() );
		}
		
	}
}
?>