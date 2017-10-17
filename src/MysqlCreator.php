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
	private $possibleReference = [];

	/**
	 * @var \mysqli
	 */
	private $mySql;


	public function createDatenmodelle(){
		$this->commandIo->writeln('Connect Datenbank');
		$this->mySql = new \mysqli($this->localhost, $this->user, $this->pw, $this->datenbank);
		if ($this->mySql->connect_error) { die('Connect Error (' . $this->mySql->connect_errno . ') '. $this->mySql->connect_error); }
		$this->commandIo->writeln('...verbunden');
		$this->getTables();
		$this->getReferences();
		$this->mySql->close();
	}

	private function getReferences(){
		$this->commandIo->writeln('Refernzen von MySql');
		$result = $this->mySql->query('SELECT CONSTRAINT_NAME, TABLE_SCHEMA, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_SCHEMA, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA= "'.$this->mySql->escape_string($this->datenbank).'" AND REFERENCED_TABLE_SCHEMA is not null' );
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
			  $this->saveReference(
				  $this->getReferencesFileName(
				  	$this->cleanFileName(
				  		$myrow['REFERENCED_TABLE_NAME'].'_'.
				  		$myrow['REFERENCED_COLUMN_NAME'].'_'.
				  		$myrow['TABLE_NAME'].'_'.
				  		$myrow['COLUMN_NAME']
					)
				  ),
				  [
							"masterTable"=> $myrow['REFERENCED_TABLE_NAME'],
							"masterField"=>$myrow['REFERENCED_COLUMN_NAME'],
							"childrenTable"=> $myrow['TABLE_NAME'],
							"childrenField"=>$myrow['COLUMN_NAME'],
							"onDelete"=> null,
							"onUpdate"=> null,
							"onInsert"=> null
				  ]
			  );
		}
		$result->close();
		$this->commandIo->writeln('');

		$this->commandIo->writeln('Refernzen aus den Fieldnamen abgeleitet:');
		$this->commandIo->createProgressBar(count($this->possibleReference));
		$this->commandIo->progressStart(count($this->possibleReference));
		foreach ($this->possibleReference as $possibleReference){
			if (file_exists($this->getTableFileName($possibleReference['masterTable']))){
				$this->saveReference(
					$this->getReferencesFileName(
						$this->cleanFileName(
							$possibleReference['masterTable'].'_'.
							$possibleReference['masterField'].'_'.
							$possibleReference['childrenTable'].'_'.
							$possibleReference['childrenField']
						)
					),
					$possibleReference
				);
			}
		}
		$this->commandIo->progressFinish();
	}

	private function saveReference($fileName,$data){
			if (!file_exists($fileName) || $this->overrideIfExists){
				if (!is_dir(pathinfo($fileName,PATHINFO_DIRNAME))){
					mkdir(pathinfo($fileName,PATHINFO_DIRNAME),0777,true);
				}
				$this->commandIo->progressAdvance();
				file_put_contents($fileName,json_encode([$data],JSON_PRETTY_PRINT));
			}

	}


	private function getTables(){
		$this->commandIo->writeln('Lade Tabellen');
		$tables = array();
		$result = $this->mySql->query('SHOW TABLE STATUS FROM '.$this->mySql->escape_string($this->datenbank) );
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
		  $tables[$myrow['Name']]=$myrow;
		}
		$result->close();

		$result = $this->mySql->query('SHOW FULL TABLES IN '.$this->mySql->escape_string($this->datenbank) );
		while ($myrow = $result->fetch_array(MYSQLI_NUM)){
		  $tables[$myrow[0]]['type']=$myrow[1];
		}
		$result->close();


		$this->commandIo->writeln('analysiere Tabellen:');
		$this->commandIo->createProgressBar(count($tables));
		$this->commandIo->progressStart(count($tables));

		while (list($tableName,$table)=@each($tables)){
			$tables[$tableName] = $this->convertMysqlTable($table);
			$filename = $this->getTableFileName($tableName);
			if (!file_exists($filename) || $this->overrideIfExists ){
				if (!is_dir(pathinfo($filename,PATHINFO_DIRNAME))){
					mkdir(pathinfo($filename,PATHINFO_DIRNAME),0777,true);
				}
				$this->commandIo->progressAdvance();
				file_put_contents($filename,json_encode($tables[$tableName],JSON_PRETTY_PRINT));
			}
		}
		$this->commandIo->progressFinish();
	}

	private function getTableFileName($tableName){
		return $this->generatePathToProject($this->projectName).'data/Table/'.$this->cleanFileName(strtolower($tableName)).'.json';
	}

	private function getReferencesFileName($referencesName){
		return $this->generatePathToProject($this->projectName).'data/References/'.strtolower($referencesName).'.json';
	}

	private function convertMysqlTable($table){
		$return = [
			"name"         		=> $table['Name'],
			"desctiption"   	=> $table['Comment'],
			"modul"         	=> 'settings',
			"isDepricated"      => false,
			"type"         		=> null,
			"datenbank" 		=> $this->getDatenbank(),
			"extraInformation"  => [
				"hasPictureliste" => false,
				"isDistributable" => true
			],
			"fields" => []
		];

		switch (strtoupper($table['type'])){
			case 'VIEW': $return['type']='view'; break;
			case 'BASE TABLE': $return['type']='table'; break;
			default:
				echo 'UNKNOWN TYP! '.$table['type'];
				$return['type']='unknown';
		}

		try {
			$help = json_decode($table['Comment'],1);
			$return['desctiption'] 	= $help['description'];
			$return['modul'] 		= $help['modul'];
		}
		catch(Exception $e) { }

		$result = $this->mySql->query('SHOW FULL COLUMNS FROM '.$table['Name']);
		while ($myrow = $result->fetch_array(MYSQLI_ASSOC)){
			$this->possibleReferenceDetection($table['Name'],$myrow);
			$return['fields'][] =  $this->convertMysqlFieldToField($myrow);
		}
		$result->close();
		return $return;
	}

	private function possibleReferenceDetection($mysqlTableName,$mysqlField){
		$help = explode('_',$mysqlField["Field"]);
		if (count($help)<2){ return; }
		end($help);
		$key = key($help);
		if (strtolower($help[$key]) =='id'){
			array_pop($help);
			$this->possibleReference[] = [
					"masterTable"	=> implode('_',$help),
					"masterField"	=> 'id',
					"childrenTable"	=> $mysqlTableName,
					"childrenField"	=> $mysqlField["Field"],
					"onDelete"=> null,
					"onUpdate"=> null,
					"onInsert"=> null
			];
		}

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
		if (!empty($help[1])){
			$lenght = substr($help[1],0,-1);
		}

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