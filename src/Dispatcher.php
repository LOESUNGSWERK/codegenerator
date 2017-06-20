<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 28.05.17
 * Time: 17:40
 */

namespace RkuCreator;


class Dispatcher
{

	public function run(){
		if ($this->getOption('generateFromDb')){ $this->generateFromDb();  }
		if ($this->getOption('new')){ 			$this->createNew(); }
		if ($this->getOption('generate')){ 		$this->generate();  }
		if ($this->getOption('help')){ 			$this->paintHelp(); }
	}


	private function generateFromDb(){
			$localhost  		= trim( $this->getOption('localhost:')  );
			$user  				= trim( $this->getOption('user:')  );
			$pw  				= trim( $this->getOption('pw:')  );
			$datenbank  		= trim( $this->getOption('datenbank:')  );
			$tabelle  			= trim( $this->getOption('table:')  );
			$projectName  		= trim( $this->getOption('project:')  );
			$overrideIfExists  	= trim( $this->getOption('overrideIfExists')  );

			$oldStuff = json_decode(base64_decode(file_get_contents(__DIR__.'/lastTemp.dat')),true);


			$newLocalhost 	= $oldStuff['localhost'];
			$newUser		= $oldStuff['user'];
			$newPw			= $oldStuff['pw'];
			$newDatenbank	= $oldStuff['datenbank'];

			if ($projectName ==''){ $projectName 		= $this->asktProject(); }
			if ($localhost == ''){	$newLocalhost 		= readline('localhost ('.$newLocalhost.'): '); }
			if ($user == ''){		$newUser 			= readline('user ('.$newUser.'): '); }
			if ($pw == ''){			$newPw 				= readline('pw (******): '); }
			if ($datenbank == ''){	$newDatenbank 		= readline('datenbank ('.$newDatenbank.'): '); }

			if (trim($newLocalhost)!=''){	$localhost = $newLocalhost; }
			if (trim($newUser)!=''){		$user =$newUser; }
			if (trim($newPw)!=''){			$pw = $newPw; }
			if (trim($newDatenbank)!=''){	$datenbank = $newDatenbank;}

			file_put_contents( __DIR__.'/lastTemp.dat', base64_encode(json_encode(
					array(
						'localhost' => $localhost,
						'user' => $user,
						'pw' => $pw,
						'datenbank' => $datenbank
					)
			)));

			$mysql = new MysqlCreator();
			$mysql->setProjectName( $projectName );
			$mysql->setLocalhost( $localhost);
			$mysql->setUser( $user);
			$mysql->setPw( $pw);
			$mysql->setDatenbank( $datenbank);
			$mysql->setTabelle( $tabelle);
			$mysql->setOverrideIfExists( $overrideIfExists);
			$mysql->createDatenmodelle();

	}



	private function generate(){
			$projectName  = trim( $this->getOption('project:')  );
			$templateName = trim( $this->getOption('template:') );

			if ($projectName ==''){ $projectName = $this->asktProject(); }
			if ($templateName==''){ $templateName = $this->asktTemplate(); }


			if ( ($projectName !='') &&
				 ($templateName!='')
			){
				$this->generateQuellcode($projectName, $templateName);
			}
	}


	private function generateQuellcode($projectName, $templateName){
		$creator = new Creator();
		$creator-> setProject($projectName);
		$creator-> setTemplate($templateName);
		$creator-> run();
	}


	private function asktProject(){
		$return = '';
		Log::writeLogLn('Liste aller Projekte:');
		Log::writeLogLn('=====================');
		Log::writeLogLn('');
		$projectDir = __DIR__.'/../projects/';
		$help =  array_diff(scandir( $projectDir ), array('..', '.'));
		foreach ($help as $key => $file){
			if (is_dir($projectDir.$file)){
				Log::writeLogLn(($key-1).' '.$file);
			}
		}
		Log::writeLogLn('');
		$key = readline('Bitte geben Sie die Nummer des Projektes ein: ');
		Log::writeLogLn('');

		return $help[($key+1)];
	}

	private function asktTemplate(){
		$return = '';
		Log::writeLogLn('Liste aller Templates:');
		Log::writeLogLn('=====================');
		Log::writeLogLn('');
		$projectDir = __DIR__.'/../templates/';
		$help =  array_diff(scandir( $projectDir ), array('..', '.'));
		foreach ($help as $key => $file){
			if (is_dir($projectDir.$file)){
				Log::writeLogLn(($key-1).' '.$file);
			}
		}
		Log::writeLogLn('');
		$key = readline('Bitte geben Sie die Nummer des Projektes ein: ');
		Log::writeLogLn('');

		return $help[($key+1)];
	}


	private function createNew(){
			$projectName  = trim( $this->getOption('project:')  );
			$templateName = trim( $this->getOption('template:') );

			if ($projectName !=''){ $this->createProject($projectName); }
			if ($templateName!=''){ $this->createTemplate($templateName); }

	}

	private function createProject($projectName){
			$help = new ProjectCreator();
			$help->setProjectName($projectName);
			$help->setOverrideIfExists( $this->getOption('overrideIfExists') );
			$help->create();
	}

	private function createTemplate($templateName){
			$help = new TemplateCreator();
			$help->setTemplateName($templateName);
			$help->setOverrideIfExists( $this->getOption('overrideIfExists') );
			$help->create();
	}

	/**
	 * @var array
	 */
	private $options = array();

	private function getOption($option){
		if ( !key_exists($option,$this->options ) ){
			$help = getopt('',array($option));
			$optinReturn = $option;
			if ( substr($optinReturn,-1,1)==':' ){ $optinReturn = substr($optinReturn,0,-1); }
			if ( key_exists($optinReturn,$help)){
				if ( substr($option,-1,1)==':' ){
					$this->options[$option] = $help[$optinReturn];
				}else{
					$this->options[$option] = true;
				}
			}
		}
		return $this->options[$option];
	}



	private function paintHelp(){
		Log::writeLogLn('Codecreator vom LOESUNGSWERK');
		Log::writeLogLn('=============================');
		Log::writeLogLn('create options');
		Log::writeLogLn('');
		Log::writeLogLn('       --new --project `projektname` :: legt ein neues Projekt unter ./projects/`projektname`/ an und füllt es mit dumydaten ');
		Log::writeLogLn('       --new --template `templatetname` :: legt ein neues Template unter ./templares/`templatetname`/ an ');
		Log::writeLogLn('');
		Log::writeLogLn('        --generate  :: erzeugt den Quellcode');
		Log::writeLogLn('        --generate --project `projektname` :: erzeugt den quellcode für das angegbene Projet ');
		Log::writeLogLn('        --generate --template`templatetname` :: erzeugt den quellcode für das angegebe Template');
		Log::writeLogLn('        --generate --project `projektname` --template`templatetname` :: erzeugt den quellcode für das angegbene Projet mit dem angegeben Template');
		Log::writeLogLn('');
		Log::writeLogLn('        --generateFromDb  :: erzeugt das Datenmodell aus einer Datenbank');
		Log::writeLogLn('        --generateFromDb --localhost --user --pw --datenbank --table --project --overrideIfExists');
		Log::writeLogLn('');
		Log::writeLogLn('');
		Log::writeLogLn('Beispiele:');
		Log::writeLogLn('create --newProject Demo2.0');
		Log::writeLogLn('create --newTemplate angular.js');
		Log::writeLogLn('create --generateFromDb --localhost localhost --pot 3306 --user test --pw geheim --datenbank testDb --project Demo2.0');
		Log::writeLogLn('create --generate --project Demo2.0 --template angular.js');
		Log::writeLogLn('');
		Log::writeLogLn('');
	}

}