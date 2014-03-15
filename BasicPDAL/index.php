<?php

function tablecreation() {
   require 'Database_Abstraction.php';
   require 'creation/Database_Object.php';
   require 'User.php';

   $db = new Database_Abstraction();

   User::createTable( $db );
}

function queryExec() {
   require 'Database_Abstraction.php';
   require 'Database_Object.php';
   require 'User.php';

   $db = new Database_Abstraction();

   $user = new User($db);
   
   $user->create();
      
   $user->ID = 1;
   $user->uname = 'driller';
   $user->pass = 'bar';
   $user->lastmod = time();
   
   $user->insert();
   
   $user->pass = 'hello';
   
   $user->flush();
   $user->close();
   
   $user->open();
   $user->delete();
   
   $arr = $user->toArray();
   
   $userRef = new ReflectionClass( 'User' );
   $constructor = $userRef->getConstructor();
   
   // Thanks PHP 5.4
   $newUser = $userRef->newInstanceWithoutConstructor();
   
   foreach ( $arr as $key => $value ) {
      $newUser->$key = $value;
   }
   $newUser->ID = 10;
   
   // The magic.
   $constructor->setAccessible( true ) ;
   
   $constructor->invokeArgs( $newUser, array( $db ) ) ;
   
   var_dump( $newUser );
   $newUser->open();
   
   $newUser->ID = 50;
   
   $newUser->flush();
   
   $newUser->close();
}

queryExec();

