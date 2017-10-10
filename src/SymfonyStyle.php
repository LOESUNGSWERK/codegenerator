<?php
	/**
	 * Created by PhpStorm.
	 * User: renne
	 * Date: 09.10.17
	 * Time: 21:54
	 */

	namespace RkuCreator;


	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class SymfonyStyle extends \Symfony\Component\Console\Style\SymfonyStyle
	{

		/**
		 * @var OutputInterface
		 */
		private $input;

		/**
		 * @var OutputInterface
		 */
		private $output;

		public function __construct(InputInterface $input, OutputInterface $output)
		{
			$this->input  = $input;
			$this->output = $output;
			parent::__construct($input, $output);
		}

		public function clearScreen()
		{
			//$this->write("\033[2J",false,self::OUTPUT_RAW);
		}

		/**
		 * @return OutputInterface
		 */
		public function getInput()
		{
			return $this->input;
		}

		/**
		 * @param OutputInterface $input
		 *
		 * @return SymfonyStyle
		 */
		public function setInput($input)
		{
			$this->input = $input;

			return $this;
		}

		/**
		 * @return OutputInterface
		 */
		public function getOutput()
		{
			return $this->output;
		}

		/**
		 * @param OutputInterface $output
		 *
		 * @return SymfonyStyle
		 */
		public function setOutput($output)
		{
			$this->output = $output;

			return $this;
		}

	}