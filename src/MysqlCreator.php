<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 30.05.17
 * Time: 22:22
 */

namespace RkuCreator;


class MysqlCreator
{

	private $localhost;
	private $user;
	private $pw;
	private $datenbank;
	private $tabelle;
	private $projectName;
	private $overrideIfExists;

	/**
	 * @var \mysqli
	 */
	private $mySql;


	public function createDatenmodelle(){
		Log::writeLogLn('Connect Datenbank');
		$this->mySql = new \mysqli($this->localhost, $this->user, $this->pw, $this->datenbank);
		if ($this->mySql->connect_error) { die('Connect Error (' . $this->mySql->connect_errno . ') '. $this->mySql->connect_error); }
		Log::writeLogLn('...verbunden');
		$this->getTables();


		$this->mySql->close();
	}


	private function getTables(){
		$table = array();
		$result = $this->mySql->query('SHOW FULL TABLES');
		while ($myrow = $result->fetch_array(MYSQLI_NUM)){
		  $table[$myrow[0]]=array();
		}
		$result->close();

		while (list($tableName,$val)=@each($table)){
			$result = $this->mySql->query('SHOW FULL COLUMNS FROM '.$tableName);
			while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
			  $table[$tableName]['fields'][] =  $myrow;
			}
			$result->close();
		}


		print_r($table);


	}

	/**
	 * @return mixed
	 */
	public function getLocalhost()
	{
		return $this->localhost;
	}

	/**
	 * @param mixed $localhost
	 */
	public function setLocalhost($localhost)
	{
		$this->localhost = $localhost;
	}

	/**
	 * @return mixed
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param mixed $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return mixed
	 */
	public function getPw()
	{
		return $this->pw;
	}

	/**
	 * @param mixed $pw
	 */
	public function setPw($pw)
	{
		$this->pw = $pw;
	}

	/**
	 * @return mixed
	 */
	public function getDatenbank()
	{
		return $this->datenbank;
	}

	/**
	 * @param mixed $datenbank
	 */
	public function setDatenbank($datenbank)
	{
		$this->datenbank = $datenbank;
	}

	/**
	 * @return mixed
	 */
	public function getTabelle()
	{
		return $this->tabelle;
	}

	/**
	 * @param mixed $tabelle
	 */
	public function setTabelle($tabelle)
	{
		$this->tabelle = $tabelle;
	}

	/**
	 * @return mixed
	 */
	public function getProjectName()
	{
		return $this->projectName;
	}

	/**
	 * @param mixed $projectName
	 */
	public function setProjectName($projectName)
	{
		$this->projectName = $projectName;
	}

	/**
	 * @return mixed
	 */
	public function getOverrideIfExists()
	{
		return $this->overrideIfExists;
	}

	/**
	 * @param mixed $overrideIfExists
	 */
	public function setOverrideIfExists($overrideIfExists)
	{
		$this->overrideIfExists = $overrideIfExists;
	}




}