<?php
	/**
	 * Created by PhpStorm.
	 * User: renne
	 * Date: 09.10.17
	 * Time: 21:54
	 */

	namespace RkuCreator;


	class SymfonyStyle extends \Symfony\Component\Console\Style\SymfonyStyle
	{

		public function clearScreen()
		{
			//$this->write("\033[2J",false,self::OUTPUT_RAW);
		}

	}