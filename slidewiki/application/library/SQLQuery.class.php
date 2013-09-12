<?php
error_reporting(0);
class SQLQuery {
    protected $db;

    /** Connects to database **/
    function connect($dsn, $account, $pwd) {
		
		try {
			$this->db = new PDO ($dsn, $account, $pwd, array(PDO::ATTR_PERSISTENT => true));
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}
		
		//problem with the windows
		//$this->db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		//$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		
		$this->db-> exec("set names utf8");
		if (! $this->db) {
			return 0;
		}else{
			return 1;
		}
    }
	 function dbQuery($query, $params = array()) {
	 	try {
			$s = $this->db->prepare ( $query );
			if (! $s->execute ( $params )) {
				echo $query;
				print_r ( $s->errorInfo () );
			}
			return $s->fetchAll ();
	 	}catch(PDOException $e){
	 		return 0;
	 	}
	}
	 function dbGetRow($query, $params = array()) {
		return array_pop ( $this->dbQuery ( $query, $params ) );
	}
	 function dbGetCol($query, $params = array()) {
		$ret = array();
		foreach ( $this->dbQuery ( $query, $params ) as $val )
			$ret[] = array_pop ( $val );
		return $ret;
	}
	 function dbGetOne($query, $params = array()) {
		$results = $this->dbQuery ( $query, $params );
		if( $results ){
			return array_pop ( array_pop ( $results ) );
		}else{
			return null;
		}
	}
	 function dbInsert($table, $values) {
		$this->dbQuery ( 'INSERT INTO `' . $table . '` (`' . join ( '`,`', array_keys ( $values ) ) . '`) VALUES (:' . join ( ',:', array_keys ( $values ) ) . ')', $values );
		return $this->db->lastInsertId ();
	}
	 function dbUpdate($table, $values) {
		foreach ( $values as $key => $val )
			if ($key != 'id')
				$set [] = "`$key`=:$key";
		return $this->dbQuery ( 'UPDATE `' . $table . '` SET ' . join ( ',', $set ) . ' WHERE id=:id', $values );
	}
}
