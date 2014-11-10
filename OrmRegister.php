<?php

class OrmRegister {
	
	private $aRegisteredObjects;
	
	function __construct() {
		// TODO: Put into a configuration file
		$this->aRegisteredObjects = array( 'Team'                               => new TeamOrmConfiguration()
										 , 'Fixture'                            => new FixtureOrmConfiguration()
										 , 'FixtureDate'                        => new FixtureDateOrmConfiguration()
										 , 'Result'                             => new SaveableResultOrmConfiguration()
										 , 'ImmutableResult'                    => new ResultOrmConfiguration()
										 , 'PredictionPlayer'                   => new PredictionPlayerOrmConfiguration()
										 , 'PredictionLeague'                   => new PredictionLeagueOrmConfiguration()
										 , 'PredictionPlayerLeagueMembership'   => new PredictionPlayerLeagueMembershipOrmConfiguration()
										 , 'PredictionPlayerLeaguePosition'     => new PredictionPlayerLeaguePositionOrmConfiguration()
										 , 'PredictionPlayerLeagueCalculation'  => new PredictionPlayerLeagueCalculationOrmComfiguration()
										 , 'Prediction'                         => new PredictionOrmConfiguration()
										 , 'PredictionExistence'                => new PredictionExistenceOrmConfiguration()
										 , 'Points'                             => new PointsOrmConfiguration()
										 , 'PointsComponent'                    => new PointsComponentOrmConfiguration()
										 , 'LeagueScoringMechanism'             => new LeagueScoringMechanismOrmConfiguration()
										 , 'News'                               => new NewsOrmConfiguration()
										 );
	}

	function getOrmConfiguration( $sObjectType ) {
		if ( isset( $this->aRegisteredObjects[ $sObjectType ] ) ) {
			// TODO: this is kind of backwards...
			$this->aRegisteredObjects[ $sObjectType ]->setObjectType( $sObjectType );
			return $this->aRegisteredObjects[ $sObjectType ];
		}		
		// TODO: Else return an invalid Orm		
	}
}

?>