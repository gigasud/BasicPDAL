<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Database Abstraction Layer <br/>
 * This represent a database in order to provides an abraction layer to the SQL code
 * @author bob
 */
class Database_Abstraction {
   /**
    * Insert $obj in $table
    * @param Database_Object $obj
    * @param String $table
    */
   public function insertObj( Database_Object $obj, $table ){
//      INSERT INTO table_name (column1, column2, column3,...)
//      VALUES (:value1, :value2, :value3,...)
      
      $rowNames = array();
      $rowParams = array();
      $params = array();
      
      foreach ( $obj->toArray() as $key => $value ) {
         $rowNames[] = $key;
         $rowParams[] = ":$key";
         $params[":$key"] = $value;
      }
      
      $sql = "INSERT INTO $table ( ".join(", ", $rowNames)." ) ".PHP_EOL.
             "VALUES ( ".join(", ", $rowParams)." );";
      $this->exec($sql, $params);
   }
   /**
    * Update $obj from $table
    * @param Database_Object $obj
    * @param String $table
    */
   public function updateObj( Database_Object $obj, $table ){
//      UPDATE table_name
//      SET column1=value, column2=value2,...
//      WHERE some_column=some_value
      $rowNames = array();
      $params = array();
      
      foreach ( $obj->toArray() as $key => $value ) {
         $rowNames[] = "$key = :$key";
         $params[":$key"] = $value;
      }
      
      $rowKeys = array();
      foreach ($obj->getKey() as $key => $value) {
         //ci do una doppia discriminante ( doppio : e prefisso )
         $rowKeys[] = "$key = ::where_$key ";
         $params[ "::where_$key" ] = $value;
      }
      
      $sql = "UPDATE $table ".PHP_EOL.
             " SET ".  join( ", ", $rowNames ).PHP_EOL.
             " WHERE ".  join( ", ", $rowKeys )." ;";
      $this->exec($sql, $params);
   }
   /**
    * Remove $obj from $table
    * @param Database_Object $obj
    * @param String $table
    */
   public function deleteObj( Database_Object $obj, $table ){
//      DELETE FROM table_name
//      WHERE some_column = some_value 
      $rowKeys = array();
      foreach ($obj->getKey() as $key => $value) {
         //ci do una doppia discriminante ( doppio : e prefisso )
         $rowKeys[] = "$key = ::where_$key ";
         $params[ "::where_$key" ] = $value;
      }
      
      $sql = "DELETE FROM $table ".PHP_EOL.
             " WHERE ".  join( ", ", $rowKeys )." ;";
      $this->exec($sql, $params);
   }
   
   /**
    * Return a String representing the SQL inserctionQuery <br/>
    * ie.                                                  <br/>
    * CREATE TABLE example_autoincrement (                 <br/>
    * id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,          <br/>
    * data VARCHAR(100)                                    <br/>
    * );                                                   <br/>
    * @param String $tableName the name of the table
    * @param array $datatype the association ( fieldName => dataType )
    * @param array $creationInfo the association ( fieldName => creation_information )
    * @return string
    */
   protected function getSqlCreateTable( $tableName, $datatype, $creationInfo ) {
      // loop for compute the column creation lines
      foreach ( $datatype as $key => $value ) {
         $info = isset($creationInfo[$key])? $creationInfo[$key] : '';
         $columns[] = "$key $value $info";
      }
      $query = "CREATE TABLE IF NOT EXIST $tableName ( \n".
              join(" , \n", $columns).
              "\n );";
      return $query;
   }
   
   /**
    * Thi is the method to create a table into the database
    * @param String $tableName the name of the table
    * @param array $datatype the association ( fieldName => dataType )
    * @param array $creationInfo the association ( fieldName => creation_information )
    * @param array $key the list of the keys for the current object
    */
   public function createTable( $tableName, $datatype, $creationInfo, $key ) {
      $query = $this->getSqlCreateTable( $tableName, $datatype, $creationInfo, $key );
      $this->exec( $query );
   }
   
   /**
    * Execute the Query
    * @param type $query
    * @param type $params
    */
   public function exec( $query, $params = NULL ) {
      print PHP_EOL.$query.PHP_EOL.PHP_EOL.
            '-----'.PHP_EOL.PHP_EOL;
      print_r($params);
   }
}
