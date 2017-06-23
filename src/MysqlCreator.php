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
	private $overrideIfExists = true;

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
		$this->getReferences();
		$this->mySql->close();
	}

	private function getReferences(){
		$result = $this->mySql->query('SELECT CONSTRAINT_NAME, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA= "'.$this->mySql->escape_string($this->datenbank).'" AND REFERENCED_TABLE_SCHEMA is not null' );
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
		  $filename = $this->generatePathToProject($this->projectName).'data/References/'.$this->cleanFileName($myrow['CONSTRAINT_NAME']).'.json';
			if (!file_exists($filename) or $this->overrideIfExists){
				if (!is_dir(pathinfo($filename,PATHINFO_DIRNAME))){
					mkdir(pathinfo($filename,PATHINFO_DIRNAME),0777,true);
				}

				$datas = [
					"masterTable"=> $myrow['REFERENCED_TABLE_NAME'],
					"masterField"=>$myrow['REFERENCED_COLUMN_NAME'],
					"childrenTable"=> $myrow['TABLE_NAME'],
					"childrenField"=>$myrow['COLUMN_NAME'],
					"onDelete"=> null,
					"onUpdate"=> null,
					"onInsert"=> null
				];

				file_put_contents($filename,json_encode([$datas],JSON_PRETTY_PRINT));
			}

		}
		$result->close();
	}


	private function getTables(){
		$tables = array();
		$result = $this->mySql->query('SHOW TABLE STATUS FROM '.$this->mySql->escape_string($this->datenbank) );
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
		  $tables[$myrow['Name']]=$myrow;
		}
		$result->close();

		while (list($tableName,$table)=@each($tables)){
			$tables[$tableName] = $this->convertMysqlTable($table);
			$filename = $this->generatePathToProject($this->projectName).'data/Table/'.$this->cleanFileName($tableName).'.json';
			if (!file_exists($filename) or $this->overrideIfExists){
				if (!is_dir(pathinfo($filename,PATHINFO_DIRNAME))){
					mkdir(pathinfo($filename,PATHINFO_DIRNAME),0777,true);
				}
				file_put_contents($filename,json_encode($tables[$tableName],JSON_PRETTY_PRINT));
			}
		}

	}

	private function convertMysqlTable($table){
		$return = [
			"name"         		=> $table['Name'],
			"desctiption"   	=> $table['Comment'],
			"modul"         	=> null,
			"isDepricated"      => false,
			"type"         		=> "table",
			"extraInformation"  => [
				"hasPictureliste" => false,
				"isDistributable" => true
			],
			"fields" => []
		];

		try {
			$help = json_decode($table['Comment'],1);
			$return['desctiption'] 	= $help['description'];
			$return['modul'] 		= $help['modul'];
		}
		catch(Exception $e) { }


		$result = $this->mySql->query('SHOW FULL COLUMNS FROM '.$table['Name']);
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
		  $return['fields'][] =  $this->convertMysqlFieldToField($myrow);
		}
		$result->close();
		return $return;
	}

	private function convertMysqlFieldToField($mysqlField){
		$return = [
		 	"name"     => $mysqlField["Field"],
          	"type"     => $this->getType($mysqlField),
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

		switch (strtoupper($help[0])){
			case 'TINYINT': 	case 'SMALLINT':
			case 'MEDIUMINT': 	case 'INT':
			case 'INTINTEGER': 	case 'BIGINT':
				if ($lenght==1){
					$return='boolean';
				}else{
					$return = 'integer';
				}
			break;
			case 'DOUBLE':	case 'REAL':
			case 'NUMERIC':	case 'DECIMAL':
				$return = 'float';
			break;

			case 'CHAR':
			case 'VARCHAR':
				if (in_array(strtolower($mysqlField["Field"]),['bez','label','bez1','kbez','lbez'])){
					$return = 'label';
				}elseif ($lenght<200){
					$return = 'string';
				}else{
					$return = 'text';
				}
			break;

			case 'DATE':		case 'DATETIME':
			case 'TIMESTAMP':
				$return = 'datetime';
			break;

			default:
				return strtolower($help[0]);
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