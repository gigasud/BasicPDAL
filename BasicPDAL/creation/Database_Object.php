<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/** 
 * This class provide all the method to write the Database_Objec onto the database <br/>
 * ! Note that this is the twin of the abstract Database_Object: You should never
 * include both.
 * @author bob
 */
class Database_Object {

   protected static $tableName;
   protected static $datatype = array();
   protected static $default = array();
   protected static $key = array();
   
   /**
    * This method provides the instruction to write the class onto to database
    * @param Database_Abstraction $db
    */
   public static function createTable( Database_Abstraction $db ) {
      $db->createTable(
              static::$tableName,
              static::$datatype,
              static::$creationInfo,
              static::$key
              );
   }
}
