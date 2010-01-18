<?php
/**
 *  Author: Justin Vincent (justin@visunet.ie)
 *  Web...: http://php.justinvincent.com
 *  Name..: ezSQL_mysql
 *  Desc..: mySQL component (part of ezSQL databse abstraction library)
 *
 */
/**
 * @author Justin Vincent <justin@visunet.ie>,  Modifications by Nabeel Shahzad <nabeel@nsslive.net>
 * @link www.nsslive.net 
 */

class DB_MySQL extends DB_Core
{

	
	/**
	 * Constructor, connects to database immediately, unless $dbname is blank
	 *
	 * @param string $dbuser Database username
	 * @param string $dbpassword Database password
	 * @param string $dbname Database name (if blank, will not connect)
	 * @param string $dbhost Hostname, optional, default is 'localhost'
	 * @return bool Connect status
	 *
	 */
	public function __construct($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
	{
		if($dbname == '') return false;
		
		if($this->connect($dbuser, $dbpassword, $dbhost))
		{
			return $this->select($dbname);
		}
		
		return false;
	}
	
	/**
	 * Explicitly close the connection on destruct
	 */
	 
	public function __destruct()
	{
		//$this->close();
	}
	
	/**
	 * Connects to database immediately, unless $dbname is blank
	 *
	 * @param string $dbuser Database username
	 * @param string $dbpassword Database password
	 * @param string $dbname Database name (if blank, will not connect)
	 * @param string $dbhost Hostname, optional, default is 'localhost'
	 * @return bool Connect status
	 *
	 */
	public function quick_connect($dbuser='', $dbpassword='', $dbname='', $dbhost='localhost')
	{
		$this->__construct($dbuser, $dbpassword, $dbname, $dbhost);
	}

	
	/**
	 * Connect to MySQL, but not to a database
	 *
	 * @param string $dbuser Username
	 * @param string $dbpassword Password
	 * @param string $dbhost Host, optional, default is localhost
	 * @return bool Success
	 *
	 */
	public function connect($dbuser='', $dbpassword='', $dbhost='localhost')
	{
		if(!$this->dbh = @mysql_connect($dbhost, $dbuser, $dbpassword, true))
		{
			$this->register_error(mysql_error(), mysql_errno());
			return false;
		}
		
	
		$this->clear_errors();
		return true;
	}
	
	/**
	 * Select a MySQL Database
	 *
	 * @param string $dbname Database name
	 * @return bool Success or not
	 *
	 */
	public function select($dbname='')
	{
		// Must have a database name
		if ($dbname == '')
		{
			$this->register_error('No database name specified!');
			return false;
		}
		// Must have an active database connection
		if (!$this->dbh)
		{
			$this->register_error('Can\'t select database, invalid or inactive connection', -1);
			return false;
		}

		if(!@mysql_select_db($dbname, $this->dbh))
		{
			$this->register_error(mysql_error($this->dbh), mysql_errno($this->dbh));
			return false;
		}
		
		$this->clear_errors();
		return true;
	}
	
	/**
	 * Close the database connection
	 */
	public function close()
	{
		return @mysql_close($this->dbh);
	}
	
	/**
	 * Format a mySQL string correctly for safe mySQL insert
	 *  (no matter if magic quotes are on or not)
	 *
	 * @param string $str String to escape
	 * @return string Returns the escaped string
	 *
	 */
	public function escape($str)
	{
		return mysql_real_escape_string($str, $this->dbh);
	}
	
	/**
	 * Returns the DB specific timestamp function (Oracle: SYSDATE, MySQL: NOW())
	 *
	 * @return string Timestamp function
	 *
	 */
	public function sysdate()
	{
		return 'NOW()';
	}

	/**
	 * Run the SQL query, and get the result. Returns false on failure
	 *  Check $this->error() and $this->errno() functions for any errors
	 *  MySQL returns errno() == 0 for no error. That's the most reliable check
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

		// Perform the query via std mysql_query function..
		$this->result = @mysql_query($query);

		// If there is an error then take note of it..
		if(!$this->result && $errno = mysql_errno() != 0)
		{
			// Something went wrong				
			$this->register_error(mysql_error(), $errno);
			return false;
		}
		else
		{
			$this->clear_errors();
		}

		// Query was an insert, delete, update, replace
		$is_insert = false;
		if(preg_match("/^(insert|delete|update|replace)\s+/i",$query))
		{
			$this->rows_affected = @mysql_affected_rows();
			$this->num_rows = $this->rows_affected;
						
			if(mysql_insert_id() > 0)
			{
				$this->insert_id = @mysql_insert_id();
				$is_insert = true;
			}
			
			// Return number fo rows affected
			$return_val = $this->rows_affected;
		}
		// Query was a select
		else
		{
			// Take note of column info
			$i=0;
			
			while ($i < @mysql_num_fields($this->result))
			{
				$this->col_info[$i] = @mysql_fetch_field($this->result);
				$i++;
			}
			
			// Store Query Results
			$num_rows=0;
			
			while($row = @mysql_fetch_object($this->result))
			{
				// Store relults as an objects within main array
				$this->last_result[$num_rows] = $row;
				$num_rows++;
			}

			@mysql_free_result($this->result);
			
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