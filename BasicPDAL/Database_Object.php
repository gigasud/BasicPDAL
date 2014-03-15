<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/** 
 * This is the basic rapresentation of a database object ( a table row )
 * Note that this Object is thinked to be created with a magic procedure like 
 * described in the follow:
 * 
 * $userRef = new ReflectionClass( 'CLASS_NAME' );
 * $constructor = $userRef->getConstructor();
 * //
 * // Oh my FSM, help me with Your Noodly Appendage
 * //
 * $newUser = $userRef->newInstanceWithoutConstructor();
 * //
 * // HERE DO OBJECT PRE-CREATION STUFFS (things like setting attributes)
 * //
 * // Now the magic: set the creator method accessible and invoke it
 * $constructor->setAccessible( true );
 * $constructor->invokeArgs( $newUser, array( *CONSTRUCTOR PARAMS* ) ) ;
 * //
 * // RAMEN !
 * 
 * @author bob
 */
abstract class Database_Object {

   /**
    * The table name for the class
    * @var String
    */
   protected static $tableName;
   /**
    * Associative array of ( property => datatype )
    * @var array 
    */
   protected static $datatype;
   /**
    *Associative array of ( property => contruction_info )
    * @var array 
    */
   protected static $creationInfo;
   /**
    * The key tuple for the relationship
    * @var array 
    */
   protected static $key = array();
   
   /**
    * Used in order to check if the constructor method is called:
    * this state is usefull since the DAL can easily use the
    * ReflectionClass::newInstanceWithoutConstructor for create an object instance
    * before calling the constructor
    * @var boolean 
    */
   protected $contructorCalled = FALSE;
   /** Dinamic part **/
   /**
    * Ask to "Has this record been modified?"
    * @var boolean
    */
   protected $dirty = FALSE;
   /**
    * True if the record is currently opened for insert/update reason
    * @var boolean
    */
   protected $open  = FALSE;
   /**
    * True if the current record is a new record
    * @var boolean
    */
   protected $new = FALSE;
   /**
    * True if the current record have been deleted from database
    * @var boolean
    */
   protected $deleted = FALSE;

   /**
    * The DAL dependency
    * @var Database_Abstraction 
    */
   protected $database;
   
   /**
    * The associative array of the value for the record <br/>
    * This represent a single row
    * @var array 
    */
   protected $row = array();
   /**
    * The associative array for the key of current object stored in the database
    * Note that this array is created on open(), destroyed on close() and update by flush()
    * @var type 
    */
   protected $rowKey = NULL;


   public function __get($name) {
      return $this->row[$name];
   }
   
   public function __set($name, $value) {
      if( $this->contructorCalled ) {
         if( !$this->open )
            throw new RuntimeException('You are tryin to update a non open record');
         $this->dirty = TRUE;
      }
      $this->row[$name] = $value;
      
   }

   public function __construct( $database ) {
      //check for if the extended class has been correct implemented
      if( !isset(static::$creationInfo) ) {
         throw new LogicException( 'static::$creationInfo should be specified' );
      }
      if( !isset(static::$datatype) ) {
         throw new LogicException( 'static::$datatype should be specified' );
      }
      if( !isset(static::$tableName) ) {
         throw new LogicException( 'static::$tableName should be specified' );
      }
      if( !isset(static::$key) ) {
         throw new LogicException( 'static::$key should be specified' );
      }
      
      $this->contructorCalled = TRUE;
      $this->database = $database;
   }
   public function __destruct() {
      if( $this->open ) {
         $this->close();
      }
   }

   /**
    * Returns the associative array for the keys of the current record
    * @return array
    */
   public function getKey() {
      return $this->rowKey;
   }
   
   /**
    * Returns the associative array which represents this Object
    * @return array
    */
   public function toArray() {
      return $this->row;
   }

   /**
    * Returns the $key name of the auto increment key for the current record if
    * exists, FALSE otherwise
    * !Note since is not possible have a boolean name for a SQL table this normally work
    * @author gigasud
    * @return boolean
    */
   public function checkAiKey() {
      foreach ( static::$creationInfo as $key => $value ) {
         if( preg_match('AUTO_INCREMENT', $value) === 1 && 
                 is_null($this->$key) )
            return $key;
      }
      return FALSE;
   }

   /**
    * Delete the current record from the database
    */
   public function delete() {
      if ( !$this->open ) {
         throw new RuntimeException('You can not delete a non opened record');
      }
      $this->deletedCheck();
      
      $this->database->deleteObj( $this, static::$tableName );
      $this->open = FALSE;
      $this->deleted = TRUE;
   }
   protected function deletedCheck() {
      if( $this->deleted )
         throw new BadMethodCallException('You can not do any operation on a deleted record');
   }

   /**
    * open the current record for writing on the database
    */
   public function open() {
      if( $this->open ) {
         throw new RuntimeException('Would you open an opened doar?');
      }
      $this->deletedCheck();
      
      $this->updateKeys();
      $this->open = TRUE;
   }
   /**
    * create a new record
    */
   public function create() {
      if( $this->open ) {
         throw new RuntimeException("the record should be closed before creation");
      }
      $this->deletedCheck();
      
      $this->open = TRUE;
      $this->dirty = TRUE;
      $this->new = TRUE;
   }
   /**
    * Alias for flush() but should be used when after you creates your object
    */
   public function insert() {
      if( !$this->new ) {
         throw new RuntimeException('To be inserted the object should be in new state, invoke the create() before !');
      }
      $this->deletedCheck();
      
      $this->flush();
   }

   /**
    * Updated the $key array of the instance according to the $row array
    * This should be automatically called after flush() or after create()
    */
   protected function updateKeys() {
      foreach ( static::$key as $key ) {
         $this->rowKey[$key] = $this->row[$key];
      }
   }

   /**
    * Creates the current record on the database
    * @todo creare un cotrollo di inserimento robusto
    * (soprattutto per i campi NOT NULL)
    */
   protected function _flush_insert() {
      if ( !$this->open ) {
         throw new RuntimeException('Before create a record you should open it');
      }
      $this->database->insertObj( $this, static::$tableName );
   }
   /**
    * Updates the current record on the database
    */
   protected function _flush_update() {
      $this->database->updateObj( $this, static::$tableName );
   }

   /**
    * Flush the current record on the database
    * @throws RuntimeException if tring to flush an closed record
    */
   public function flush() {
      if ( !$this->open )
         throw new RuntimeException('Flush tried on a non open record');
      $this->deletedCheck();
      
      if ( $this->dirty ) :
         
         if( $this->new ) {
            $this->_flush_insert();
            $this->new = FALSE;
         } else {
            $this->_flush_update();
         }
         $this->updateKeys();
         $this->dirty = FALSE;
      
      endif;  
   }
   
   /**
    * Close the currend record on the database
    */
   public function close() {
      if ( !$this->open ) {
         throw new RuntimeException('Could you close a closed doar?');
      }
      $this->deletedCheck();
      
      if( $this->dirty ) {
         $this->flush();
      }
      $this->rowKey = NULL;
      $this->open = FALSE;
   }
   
   public function isDirty() {
      return $this->dirty;
   }
   public function isNew() {
      return $this->new;
   }
   public function isOpened() {
      return $this->open;
   }
   public function statusArray() {
      return array(
          'dirty' => $this->isDirty(),
          'open'  => $this->isDirty(),
          'new'   => $this->isNew(),
      );
   }
}
