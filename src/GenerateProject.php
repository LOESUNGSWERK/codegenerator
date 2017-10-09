<?php
	/**
	 * Created by PhpStorm.
	 * User: renne
	 * Date: 06.10.17
	 * Time: 23:16
	 */

	namespace RkuCreator;

	use Symfony\Component\Console\Command\Command;
	use Symfony\Component\Console\Input\InputArgument;
	use Symfony\Component\Console\Input\InputInterface;
	use Symfony\Component\Console\Output\OutputInterface;

	class GenerateProject extends Command
	{

		const PATH_TO_PROJECTS = __DIR__ . '/../projects/';
		const PATH_TO_DEFAUTLS = __DIR__ . '/../data/defaultData/';

		protected function configure()
		{
			$this->setName('newProject')
			     ->setDescription('Creates a new project.')
			     ->setHelp('This command allows you to create a new project...')
			     ->addArgument('name', InputArgument::REQUIRED, 'name of the project');
		}

		protected function execute(InputInterface $input, OutputInterface $output)
		{

			$io = new SymfonyStyle($input, $output);
			$io->title('create new project');
			$projectName = $input->getArgument('name');

			$newDir = self::PATH_TO_PROJECTS . $projectName . '/';
			if (!is_dir($newDir)) {
				$io->writeln('Erzeuge das Projekt');
				$this->fillPathWithDummyData($newDir,$io);
			} else {
				$io->error('Das Projekt "' . $projectName . '" gibt es schon...');
			}
		}

		/**
		 * @param string $path
		 * @param SymfonyStyle $io
		 */
		private function fillPathWithDummyData($path,$io)
		{
			$io->createProgressBar(10);
			$io->progressStart(10);
			mkdir($path . 'data/Table', 0777, true);
			$io->progressAdvance();
			mkdir($path . 'data/References', 0777, true);
			$io->progressAdvance();
			mkdir($path . 'data/temp', 0777, true);
			$io->progressAdvance();
			mkdir($path . 'dist', 0777, true);
			$io->progressAdvance();
			copy(self::PATH_TO_DEFAUTLS . 'project/data/Table/content.json', $path . 'data/Table/content.json');
			$io->progressAdvance();
			copy(self::PATH_TO_DEFAUTLS . 'project/data/Table/user.json', $path . 'data/Table/user.json');
			$io->progressAdvance();
			copy(self::PATH_TO_DEFAUTLS . 'project/data/Table/userGruppen.json', $path . 'data/Table/userGruppen.json');
			$io->progressAdvance();
			copy(self::PATH_TO_DEFAUTLS . 'project/data/References/user_user_gruppen.json', $path . 'data/References/user_user_gruppen.json');
			$io->progressAdvance();
			copy(self::PATH_TO_DEFAUTLS . 'project/data/References/content_user.json', $path . 'data/References/content_user.json');
			$io->progressFinish();

		}

	}