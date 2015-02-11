<?php

/***************************************************************************
*
* The model in the model, view, controller design patter.
*
* The model is what deals directly with the applications data, the database.
*
*****************************************************************************/

class Model {

	private $connection;//This is more of a handler now
	
	public function __construct()
	{
		global $config;

		try 
		{
		    $this->connection = new PDO($config['db_dsn'], $config['db_username'], $config['db_password']);
		} 
		catch (PDOException $e) 
		{
		    echo 'Connection failed: ' . $e->getMessage();
		}
	}

	public function lastInsertId()
	{
		return $this->connection->lastInsertId();
	}

	public function fetchIntoClass($qry, $params=array(), $className)
	{		
		//Example array: array(':calories' => $calories, ':colour' => $colour)
		//Or by order array($calories, $colour)
		try 
		{
			require_once(APP_DIR .'viewmodels/' . $className .'.php');

			//echo "<br /><br /><br /><br /><br /><br /><br /><br />" . APP_DIR .'viewmodels/' . $className .'.php';
			 $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			 $pdo = $this->connection->prepare($qry);
			 $pdo->execute($params);

			 $urlArray = explode("/", $className);

			if(count($urlArray) > 1)
			{
				$className = $urlArray[count($urlArray) - 1];
			}

			//Fetches data and adds it to the specified class
			return $pdo->fetchAll(PDO::FETCH_CLASS, $className);
		}
		catch(PDOException $e) 
		{
			echo $e->getMessage();
			return $e->getMessage();
		}
	}

	public function fetchIntoObject($qry, $params=array())
	{
		//Example array: array(':calories' => $calories, ':colour' => $colour)
		//Or by order array($calories, $colour)
		try 
		{
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$pdo = $this->connection->prepare($qry);
			$pdo->execute($params);

			//Fetches data and puts it into object form
			return $pdo->fetchAll(PDO::FETCH_OBJ);
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}
	public function fetchRowCount($qry, $params=array()){
		//Example array: array(':calories' => $calories, ':colour' => $colour)
		//Or by order array($calories, $colour)
		try 
		{
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$pdo = $this->connection->prepare($qry);
			$pdo->execute($params);

			return $pdo->rowCount();
		}
		catch(PDOException $e) 
		{
			echo $e->getMessage();
			return $e->getMessage();
		}
	}
	public function fetchColumn($qry, $params=array()){
		//Example array: array(':calories' => $calories, ':colour' => $colour)
		//Or by order array($calories, $colour)
		try 
		{
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			$pdo = $this->connection->prepare($qry);
			$pdo->execute($params);

			return $pdo->fetchColumn();
		}
		catch(PDOException $e) 
		{
			return $e->getMessage();
		}
	}

	public function escapeString($string)
	{
		return mysql_real_escape_string($string);
	}

	public function escapeArray($array)
	{
	    array_walk_recursive($array, create_function('&$v', '$v = mysql_real_escape_string($v);'));
		return $array;
	}
	
	public function to_bool($val)
	{
	    return !!$val;
	}
	
	public function to_date($val)
	{
	    return date('Y-m-d', $val);
	}
	
	public function to_time($val)
	{
	    return date('H:i:s', $val);
	}
	
	public function to_datetime($val)
	{
	    return date('Y-m-d H:i:s', $val);
	}

}
?>
