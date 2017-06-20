<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 28.05.17
 * Time: 22:21
 */

namespace RkuCreator;


class ProjectCreator
{

	const PATH_TO_PROJECTS = __DIR__.'/../projects/';
	const PATH_TO_DEFAUTLS = __DIR__.'/../data/defaultData/';

	private $projectName;
	private $overrideIfExists=false;

	public function create(){
		$newDir = self::PATH_TO_PROJECTS.$this->projectName.'/';
		if(!is_dir($newDir)){
			Log::writeLog('Erzeuge das Projekt "'.$this->projectName.'" ');
			$this->fillPathWithDummyData($newDir);
		}else{
			Log::writeLogLn('Das Projekt "'.$this->projectName.'" gibt es schon...');
		}
	}

	private function fillPathWithDummyData($path){
		Log::writeLog('.');
		mkdir($path.'data/Table',0777,true);
		Log::writeLog('.');
		mkdir($path.'data/References',0777,true);
		Log::writeLog('.');
		mkdir($path.'data/temp',0777,true);
		Log::writeLog('.');
		mkdir($path.'dist',0777,true);
		Log::writeLog('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/Table/content.json',$path.'data/Table/content.json');
		Log::writeLog('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/Table/user.json',$path.'data/Table/user.json');
		Log::writeLog('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/Table/userGruppen.json',$path.'data/Table/userGruppen.json');
		Log::writeLog('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/References/user_user_gruppen.json',$path.'data/References/user_user_gruppen.json');
		Log::writeLog('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/References/content_user.json',$path.'data/References/content_user.json');
		Log::writeLogLn(' fertig ');
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
	 * @return bool
	 */
	public function isOverrideIfExists()
	{
		return $this->overrideIfExists;
	}

	/**
	 * @param bool $overrideIfExists
	 */
	public function setOverrideIfExists($overrideIfExists)
	{
		$this->overrideIfExists = $overrideIfExists;
	}




}