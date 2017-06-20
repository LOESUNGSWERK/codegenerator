<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 29.05.17
 * Time: 20:17
 */

namespace RkuCreator;


class TemplateCreator
{
	const PATH_TO_TEMPLATES = __DIR__.'/../templates/';
	const PATH_TO_DEFAUTLS = __DIR__.'/defaultData/';

	private $templateName;
	private $overrideIfExists=false;

	public function create(){
		$newDir = self::PATH_TO_TEMPLATES.$this->templateName.'/';
		if(!is_dir($newDir)){
			Log::writeLog('Erzeuge das Template "'.$this->templateName.'" ');
			$this->fillPathWithDummyData($newDir);
		}else{
			Log::writeLogLn('Das Template "'.$this->templateName.'" gibt es schon...');
		}
	}

	private function fillPathWithDummyData($path){
		mkdir($path.'templates',0777,true);
		mkdir($path.'templates/index',0777,true);
		copy(self::PATH_TO_DEFAUTLS.'template/templates/dm.html',$path.'templates/dm.html');
		copy(self::PATH_TO_DEFAUTLS.'template/templates/index/index.html',$path.'templates/index/index.html');
		copy(self::PATH_TO_DEFAUTLS.'template/templates/index/datenmodellDiv.html',$path.'templates/index/datenmodellDiv.html');
		copy(self::PATH_TO_DEFAUTLS.'template/templates/index/datenmodelleLi.html',$path.'templates/index/datenmodelleLi.html');
		copy(self::PATH_TO_DEFAUTLS.'template/creator.json',$path.'creator.json');
	}

	/**
	 * @return mixed
	 */
	public function getTemplateName()
	{
		return $this->templateName;
	}

	/**
	 * @param mixed $templateName
	 */
	public function setTemplateName($templateName)
	{
		$this->templateName = $templateName;
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