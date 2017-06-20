<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 30.05.17
 * Time: 22:22
 */

namespace RkuCreator;


class MysqlCreator extends AbstractCreator
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
		$tables = array();
		$result = $this->mySql->query('SHOW FULL TABLES');
		while ($myrow = $result->fetch_array(MYSQLI_NUM)){
		  $tables[$myrow[0]]=1;
		}
		$result->close();

		while (list($tableName,$val)=@each($tables)){
			$tables[$tableName] = $this->convertMysqlTable($tableName);
			file_put_contents($this->generatePathToProject($this->projectName).'data/Table/'.$tableName.'.json',json_encode($tables[$tableName],JSON_PRETTY_PRINT));
		}

	}

	private function convertMysqlTable($tableName){
		$return = [
			"tableName"         => $tableName,
			"desctiption"       => null,
			"modulName"         => null,
			"isDepricated"      => false,
			"tableType"         => "table",
			"extraInformation"  => [
				"hasPictureliste" => false,
				"isDistributable" => true
			],
			"fields" => []
		];

		$result = $this->mySql->query('SHOW FULL COLUMNS FROM '.$tableName);
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
		  $return['fields'][] =  $this->convertMysqlFieldToField($myrow);
		}
		$result->close();
		return $return;
	}

	private function convertMysqlFieldToField($mysqlField){
		$return = [
		 	"fieldName"     => $mysqlField["Field"],
          	"fieldType"     => $this->getType($mysqlField),
          	"defaultValue"  => $mysqlField["Default"],
          	"isAutoinc"     => ($mysqlField["Extra"]=='auto_increment'),
          	"isPrimaryKey"  => ($mysqlField["Key"]=='PRI'),
          	"isIndex"       => in_array($mysqlField["Key"],['PRI','MUL']),
          	"canBeNull"     => ($mysqlField["Null"] == "YES"),
			"description"	=> $mysqlField["Comment"],
		];
		return $return;
	}

	private function getType($mysqlField){
		$return = '';
		$help = explode('(',$mysqlField["Type"]);
		$lenght = substr($help[1],0,-1);

		switch (strtolower($help[0])){
			case 'int':
				if ($lenght==1){
					$return='boolean';
				}else{
					$return = 'integer';
				}
			break;

			case 'varchar':
				if (in_array(strtolower($mysqlField["Field"]),['bez','label','bez1','kbez','lbez'])){
					$return = 'label';
				}elseif ($lenght<200){
					$return = 'string';
				}else{
					$return = 'text';
				}
			break;

			case 'timestamp':
				$return = 'dateTime';
			break;

			default:
				return $help[0];
		}

		return $return;
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