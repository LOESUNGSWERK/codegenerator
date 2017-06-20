<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 20.06.17
 * Time: 23:32
 */

namespace RkuCreator;


abstract class AbstractCreator
{

	const PATH_TO_PROJECTS = __DIR__.'/../projects/';
	const PATH_TO_DEFAUTLS = __DIR__.'/../data/defaultData/';
	const PATH_TO_TEMPLATES = __DIR__.'/../templates/';

	protected function generatePathToProject($projectName){
		return self::PATH_TO_PROJECTS.$projectName.'/';
	}

	protected function generatePathToTemplate($templateName){
		return self::PATH_TO_TEMPLATES.$templateName.'/';
	}

}