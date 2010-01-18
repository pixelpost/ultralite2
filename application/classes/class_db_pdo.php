<?php
/**
 *  Author: Justin Vincent (justin@visunet.ie)
 *  Web...: http://php.justinvincent.com
 *  Name..: ezSQL_pdo
 *  Desc..: SQLite component (part of ezSQL databse abstraction library)
 *
 */
/**
 * @author Justin Vincent <justin@visunet.ie>,  Modifications by Nabeel Shahzad <nabeel@nsslive.net> and Jay Williams <jaywilliams@pixelpost.org>
 * @link www.nsslive.net 
 * @link www.pixelpost.org
 */

class DB_PDO extends DB_Core
{

	/**
	 * Constructor, connects to database immediately, unless $dsn is blank
	 *
	 * @param string $dsn Data Source Name
	 * @param string $dbuser Database username, optional
	 * @param string $dbpassword Database password, optional
	 * @return bool Connect status
	 */
	public function __construct($dsn='', $dbuser='', $dbpassword='')
	{
		if($dsn == '') return false;
		
		if($this->connect($dbuser, $dbpassword, $dbhost))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Explicitly close the connection on destruct
	 */
	 
	public function __destruct()
	{
		$this->close();
	}
	
	/**
	 * Connects to database immediately, unless $dsn is blank
	 * 
	 * In the case of SQLite quick_connect is not really needed
	 * because std. connect already does what quick connect does - 
	 * but for the sake of consistency it has been included
	 *
	 * @param string $dsn Data Source Name
	 * @param string $dbuser Database username, optional
	 * @param string $dbpassword Database password, optional
	 * @return bool Connect status
	 */
	public function quick_connect($dsn='', $dbuser='', $dbpassword='')
	{
		$this->__construct($dsn, $dbuser, $dbpassword);
	}

	/**
	 * Connects to database immediately, unless $dsn is blank
	 *
	 * @param string $dsn Data Source Name
	 * @param string $dbuser Database username, optional
	 * @param string $dbpassword Database password, optional
	 * @return bool Connect status
	 */
	public function connect($dsn='', $dbuser='', $dbpassword='')
	{
		try 
		{
			$this->dbh = new PDO($dsn, $dbuser, $dbpassword);
		} 
		catch (PDOException $e) 
		{
			$this->register_error($e->getMessage());
			return false;
		}	
	
		$this->clear_errors();
		return true;
	}

	/**
	 * Connects to database immediately, unless $dsn is blank
	 * If the database is already connected, it will simply return true.
	 * 
	 * No real equivalent of mySQL select in SQLite 
	 * once again, function included for the sake of consistency
	 *
	 * @param string $dsn Data Source Name
	 * @param string $dbuser Database username, optional
	 * @param string $dbpassword Database password, optional
	 * @return bool Connect status
	 */
	public function select($dsn='', $dbuser='', $dbpassword='')
	{
		if (!isset($this->dbh) || !$this->dbh)
			return $this->connect($dsn, $dbuser, $dbpassword);
		
		return true;
	}
	
	/**
	 * Close the database connection
	 */
	public function close()
	{
		$this->dbh = null;
		return true;
	}
	
	/**
	 * Format a SQLite string correctly for safe SQLite insert
	 *
	 * @param string $str String to escape
	 * @return string Returns the escaped string
	 *
	 */
	public function escape($str)
	{
		switch (gettype($str))
		{
			case 'string' : 
				return str_replace("'","''",str_replace("''","'",$str));
				break;
			case 'boolean' :
			 	return ($str === FALSE) ? 0 : 1;
				break;
			default : 
				return ($str === NULL) ? 'NULL' : $str;
				break;
		}
	}
	
	/**
	 * Returns the DB specific timestamp function (Oracle: SYSDATE, MySQL: NOW())
	 *
	 * @return string Timestamp function
	 *
	 */
	public function sysdate()
	{
		return 'datetime(\'now\')';
	}

	/**
	 * Hooks into PDO error system and logs any errors
	 *
	 * @return bool True if an error occurred
	 */
	function catch_error()
	{
		$error_str = 'No error info';
		
		$err_array = $this->dbh->errorInfo();
		
		// If this the error code is '00000', the query was a success
		if ($err_array[0] === '00000') {
			return false;
		}
		
		// Note: Ignoring error - bind or column index out of range
		if ( isset($err_array[1]) && $err_array[1] != 25)
		{
			
			$error_str = '';
			foreach ( $err_array as $entry )
			{
				$error_str .= $entry . ', ';
			}

			$error_str = substr($error_str,0,-2);

			$this->register_error($error_str);
			return true;
		}
		
		return false;
	}

	/**
	 * Run the SQL query, and get the result. Returns false on failure
	 *  Check $this->error() and $this->errno() functions for any errors
	 *
	 * @param string $query SQL Query
	 * @return mixed Return values
	 *
	 */
	public function query($query)
	{
		// Flush cached values..
		$this->flush();

		// For reg expressions
		$query = trim($query);
		
		// Log how the function was called
		$this->func_call = "\$db->query(\"$query\")";
		
		// Keep track of the last query for debug..
		$this->last_query = $query;

		// Count how many queries there have been
		$this->num_queries++;

		// Use core file cache function
		if($cache = $this->get_cache($query))
		{
			return $cache;
		}

		// Make sure connection is ALIVEE!
		if (!isset($this->dbh) || !$this->dbh )
		{
			$this->register_error('There is no active database connection!');
			return false;
		}

		// Query was an insert, delete, update, replace
		$is_insert = false;
		if(preg_match("/^(insert|delete|update|replace|drop|create)\s+/i",$query))
		{
			// Perform the query and log number of affected rows
			$this->rows_affected = $this->dbh->exec($query);
			
			$this->num_rows = $this->rows_affected;
			
			// If there is an error then take note of it..
			if(!$this->rows_affected && $this->catch_error())
			{
				// Something went wrong
				return false;
			}
			else
			{
				$this->clear_errors();
			}
						
			if($this->dbh->lastInsertId() > 0)
			{
				$this->insert_id = $this->dbh->lastInsertId();
				$is_insert = true;
			}
			
			// Return number fo rows affected
			$return_val = $this->rows_affected;
		}
		// Query was a select
		else
		{
			// Perform the query and log number of affected rows
			$this->result = $this->dbh->query($query);
			
			// If there is an error then take note of it..
			if(!$this->result && $this->catch_error())
			{
				// Something went wrong
				return false;
			}
			else
			{
				$this->clear_errors();
			}
			
			$col_count = $this->result->columnCount();
			
			for ( $i=0 ; $i < $col_count ; $i++ )
			{
				if ( $meta = $this->result->getColumnMeta($i) )
				{					
					@$this->col_info[$i]->name       = $meta['name'];
					 $this->col_info[$i]->type       = $meta['native_type'];
					 $this->col_info[$i]->max_length = '';
				}
				else
				{
					@$this->col_info[$i]->name       = 'undefined';
					 $this->col_info[$i]->type       = 'undefined';
					 $this->col_info[$i]->max_length = '';
				}
			}
			
			// Store Query Results
			$num_rows=0;
			
			while ( $row = @$this->result->fetch(PDO::FETCH_ASSOC) )
			{
				// Store relults as an objects within main array
				$this->last_result[$num_rows] = (object) $row;
				$num_rows++;
			}
			
			// Log number of rows the query returned
			$this->rows_affected = $num_rows;
			$this->num_rows = $num_rows;

			// Return number of rows selected
			$return_val = $this->num_rows;
		}

		// disk caching of queries
		$this->store_cache($query,$is_insert);

		// If debug ALL queries
		$this->trace || $this->debug_all ? $this->debug() : null ;

		return $return_val;
	}
}