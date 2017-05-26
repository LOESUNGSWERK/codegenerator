<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 22.05.17
 * Time: 23:26
 */

namespace RkuCreator;


final class Log
{

	static public function writeLog($message,$ebene=0){
			echo Log::writeSpace($ebene).$message;
	}

	static public function writeLogLn($message,$ebene=0){
			echo Log::writeSpace($ebene).$message.PHP_EOL;
	}

	static public function writeSpace($ebene){
		return str_pad('',$ebene,' ');
	}

}