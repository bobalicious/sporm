<?php
namespace sporm;

class DatabaseFactory {

	public static function buildDatabaseQueryer( $sDatabaseType, $aConfiguration ) {
		
		if ( $sDatabaseType == DatabaseConfiguration::MY_SQL ) {
			return new \sporm\mysql\DatabaseQueryer( $aConfiguration, new \sporm\mysql\DatabaseCommandGenerator(), new \sporm\mysql\LoggingDatabaseConnector() );
		}
		
	}
}
