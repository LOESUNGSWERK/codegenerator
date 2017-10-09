<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 20.06.17
 * Time: 23:32
 */

namespace RkuCreator;


use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCreator
{

	/**
	 * @var SymfonyStyle
	 */
	protected $commandIo;

	const PATH_TO_PROJECTS = __DIR__.'/../projects/';
	const PATH_TO_DEFAUTLS = __DIR__.'/../data/defaultData/';
	const PATH_TO_TEMPLATES = __DIR__.'/../templates/';

	protected function generatePathToProject($projectName){
		return self::PATH_TO_PROJECTS.$projectName.'/';
	}

	protected function generatePathToTemplate($templateName){
		return self::PATH_TO_TEMPLATES.$templateName.'/';
	}

	protected function cleanFileName($string){
		$string=str_replace(" ","-",$string);
		$string=str_replace("_","-",$string);
		$string=str_replace(",","-",$string);
		$string=str_replace(";","-",$string);
		$string=str_replace(":","-",$string);
		$string=str_replace("'","-",$string);
		$string=str_replace('"',"-",$string);
		$string=str_replace(".","-",$string);
		$string=str_replace("Ä","Ae",$string);
		$string=str_replace("ä","ae",$string);
		$string=str_replace("Ö","Oe",$string);
		$string=str_replace("ö","oe",$string);
		$string=str_replace("Ü","Ue",$string);
		$string=str_replace("ü","ue",$string);
		$string=str_replace("ß","ss",$string);
		$string=str_replace("&","-und-",$string);
        $string = str_replace (" ", "-", $string);
        $string = str_replace ("..", ".", $string);
        $string = str_replace ("--", "-", $string);
        preg_replace ("/[^0-9^a-z^A-Z^-^_^.]/", "", $string);
        return $string;
	}

	/**
	 * @return SymfonyStyle
	 */
	public function getCommandIo()
	{
		return $this->commandIo;
	}

	/**
	 * @param SymfonyStyle $commandIo
	 *
	 * @return QuellcodeCreator
	 */
	public function setCommandIo($commandIo)
	{
		$this->commandIo = $commandIo;

		return $this;
	}

}