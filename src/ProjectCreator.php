<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 28.05.17
 * Time: 22:21
 */

namespace RkuCreator;


class ProjectCreator extends AbstractCreator
{

	private $projectName;
	private $overrideIfExists=false;

	public function create(){
		$newDir = self::PATH_TO_PROJECTS.$this->projectName.'/';
		if(!is_dir($newDir)){
			$this->commandIo->write('Erzeuge das Projekt "'.$this->projectName.'" ');
			$this->fillPathWithDummyData($newDir);
		}else{
			$this->commandIo->writeln('Das Projekt "'.$this->projectName.'" gibt es schon...');
		}
	}

	private function fillPathWithDummyData($path){
		$this->commandIo->write('.');
		mkdir($path.'data/Table',0777,true);
		$this->commandIo->write('.');
		mkdir($path.'data/References',0777,true);
		$this->commandIo->write('.');
		mkdir($path.'data/temp',0777,true);
		$this->commandIo->write('.');
		mkdir($path.'dist',0777,true);
		$this->commandIo->write('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/Table/content.json',$path.'data/Table/content.json');
		$this->commandIo->write('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/Table/user.json',$path.'data/Table/user.json');
		$this->commandIo->write('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/Table/userGruppen.json',$path.'data/Table/userGruppen.json');
		$this->commandIo->write('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/References/user_user_gruppen.json',$path.'data/References/user_user_gruppen.json');
		$this->commandIo->write('.');
		copy(self::PATH_TO_DEFAUTLS.'project/data/References/content_user.json',$path.'data/References/content_user.json');
		$this->commandIo->writeln(' fertig ');
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