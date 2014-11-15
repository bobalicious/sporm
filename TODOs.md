SPORM - Simple PHP Object Relational Mapper
--------------------------------------------------------

##Things that I'm planning on doing:

###Housekeeping
* Tidy up the namespaces a little
* Change the name of:
  * DatabaseReader
  * OrmConfigurationGenerator



###Structural
* Split up the MySql classes a little better
* Review the filters so that they're not so text driven?
* Implement a simpler configuration
  * Use naming conventions to tie up with methods
  * Use reflection to tie up with member variables directly
* registerConfiguration should return a register which can then be passed into the DatabaseReader
  * Should be able to use multiple configurations in the database

###Enhancements
* Add a configuration file mechanism
* Add ability to delete data (not as an update)
* Add an Oracle connection
* Add a Postgres connection
* Connections based on the configuration of an object (e.g. this comes from Oracle, this comes from MySql)

###Documentation
* Write a proper README with examples for:
  * Simplest example?
    * Create objects
    * Write the data
    * Update the data
    * Reload the data
  * More of an overview
    * Load by ID
    * Load by simple where
    * Load by complex where
    * Load by getting the whole table
    * Insert
    * Update
      * Delete then Insert
    * Delete
  * Example using convention
  * Example using reflection
  * Example using a class based configuration
    * Relationships between objects
    * How to implement lazy reads
  * Registering multiple connections

###You're dreaming
* Add the generation of the database scripts
* Add a migration mechanism
* Unit tests!