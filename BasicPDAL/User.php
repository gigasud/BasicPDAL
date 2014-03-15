<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This is the User Database representation:
 * use this as template
 * @property bigint $ID AUTO_INCREMENT
 * @property varchar(10) $uname NOT NULL
 * @property varchar(50) $pass NOT NULL
 * @property timestamp $lastmod DEFAULT CURRENT_TIMESTAMP
 * @author bob
 */
class User extends Database_Object {
   
   /**
    * The table name for the class
    * @var String
    */
   protected static $tableName = 'User';
   
   /**
    * Associative array of ( property => datatype )
    * @var array 
    */
   protected static $datatype = array(
       'ID' => 'bigint',
       'uname' => 'varchar(10)',
       'pass' => 'varchar(50)',
       'lastmod' => 'timestamp',
   );
   /**
    *Associative array of ( property => contruction_info )
    * @var array 
    */
   protected static $creationInfo = array(
       'ID'      => 'AUTO_INCREMENT PRIMARY KEY',
       'uname'   => 'NOT NULL INDEX',
       'pass'    => 'NOT NULL',
       'lastmod' => 'DEFAULT CURRENT_TIMESTAMP',
   );

   /**
    * The key tuple for the relationship
    * @var array 
    */
   protected static $key = array( 'ID' );
   
}
