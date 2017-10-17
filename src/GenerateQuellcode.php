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
	use Symfony\Component\Console\Question\ChoiceQuestion;

	class GenerateQuellcode extends Command
	{

		protected function configure()
		{
			$this->setName('render')
			     ->setDescription('render source code with project and template.')
			     ->setHelp('This command allows you to generate source code ...')
			     ->addArgument('project', InputArgument::OPTIONAL, 'name of the project')
			     ->addArgument('template', InputArgument::OPTIONAL, 'name of the project');
		}

		protected function execute(InputInterface $input, OutputInterface $output)
		{

			$io = new SymfonyStyle($input, $output);
			$io->title('render source code with project and template');

			$projectName = $input->getArgument('project');
			if (null === $projectName) {
				$projectDir = __DIR__ . '/../projects/';
				$projects   = [];
				$help       = array_diff(scandir($projectDir), ['..', '.']);
				foreach ($help as $key => $file) {
					if (is_dir($projectDir . $file)) {
						$projects[] = $file;
					}
				}
				$helper   = $this->getHelper('question');
				$question = new ChoiceQuestion(
					'Please select your project ',
					$projects,
					0
				);
				$question->setErrorMessage('Project %s is invalid.');
				$projectName = $helper->ask($input, $output, $question);
			}

			$templateName = $input->getArgument('template');
			if (null === $templateName) {
				$templateDir = __DIR__ . '/../templates/';
				$templates   = [];
				$help       = array_diff(scandir($templateDir), ['..', '.']);
				foreach ($help as $key => $file) {
					if (is_dir($templateDir . $file)) {
						$templates[] = $file;
					}
				}
				$helper   = $this->getHelper('question');
				$question = new ChoiceQuestion(
					'Please select your template',
					$templates,
					0
				);
				$question->setErrorMessage('Project %s is invalid.');
				$templateName = $helper->ask($input, $output, $question);
			}

			$io->clearScreen();

			$creator = new QuellcodeCreator();
			$creator->setCommandIo($io);
			$creator->run($projectName,$templateName);

		}
	}