<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This is a query (statement) representation
 * @todo ALL
 * @author bob
 */
class Database_Query {
   
   protected static $datatype = array();
   protected static $default = array();

   protected static $key = array();
   
   protected $new = FALSE;
   protected $dirty = FALSE;

   protected $database;
   
   protected $values = array();
   protected $key = array();
   
   protected $whereAtts = array();
   
   public function __construct( $database ) {
      $this->database = $database;
   }

   public function where( $attributes ) {
      $this->whereAtts = $attributes;
   }
   
   //public function groupBy();
   //public function having();
   //public function orderBy();
   //public function limit();
   
   public function create();
   
   public function open();
   public function flush();
   public function close();
}

