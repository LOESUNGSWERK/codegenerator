<?php
	/**
	 * Created by PhpStorm.
	 * User: renne
	 * Date: 17.05.17
	 * Time: 23:01
	 */

	namespace RkuCreator;

	use RkuCreator\Twig\TwigExtension;
	use Symfony\Component\Console\Helper\ProgressBar;

	class TaskControler extends AbstractCreator
	{

		/**
		 * @var array
		 */
		private $task;
		private $projectRoot;
		private $templateRoot;
		private $projectData = [];
		private $templateData = [];


		public function run()
		{
			reset($this->projectData['tables']);
			$totalSteps = count($this->projectData['tables']);
			$bar1 = new ProgressBar($this->commandIo->getOutput(), 1024);
			$bar1->start($totalSteps);
			$bar1->advance();
			$bar1->setFormat('%current:3s%/%max:3s% [%bar%] %percent:3s%% %message%');

			foreach ($this->projectData['tables'] as $table) {
				$templateVars = $table;
				$bar1->setMessage($table['name']);
				$bar1->advance();

				$templateVars['project']   = $this->projectData['project'];
				$templateVars['tables']    = $this->projectData['tables'];
				$templateVars['modules']   = $this->projectData['module'];
				$templateVars['template']  = $this->templateData;
				$templateVars['today']     = date('d.m.Y');
				$templateVars['now']       = date('d.m.Y H:i:s');
				$templateVars['debug']     = print_r($templateVars,true);

				$destination = $this->getDesinationFile($templateVars);
				if (file_exists($destination)) {

					switch (strtoupper($this->task['onUpdate'])) {
						case 'OVERWRIDE':
							$this->updateQuellcode($destination, $templateVars);
							break;
						case 'REPLACE':
							$this->replaceQuellcode($destination, $templateVars);
							break;
						case 'IGNORE':
						default:
							break;
					}


				} else {
					$dir = pathinfo($destination, PATHINFO_DIRNAME);
					if (!is_dir($dir)) {
						mkdir($dir, 0777, true);
					}
					$code = $this->renderMe($this->task['insertTemplate'], $templateVars);
					file_put_contents($destination, $code);
					if (strtoupper($this->task['onUpdate']) == 'REPLACE') {
						$this->replaceQuellcode($destination, $templateVars);
					}
				}
			}
			$bar1->setMessage('');
			$bar1->finish();
		}

		private function renderMe($string, $templateVars)
		{
			$possibleTemplateFile = $this->getTemplateRoot() . 'templates/' . $string;
			if (file_exists($possibleTemplateFile)) {
				return $this->getTemplater([pathinfo($string, PATHINFO_DIRNAME)])->render($string, $templateVars);
			} else {
				return $this->renderString($string, $templateVars);
			}
		}

		private function replaceQuellcode($destination, $templateVars)
		{
			$aktuellerQuellcode = file_get_contents($destination);
			$newQuellcode       = $aktuellerQuellcode;
			$replacetasks       = $this->task['replaceTasks'];
			foreach ($replacetasks as $rtask) {

				if ($rtask['type']=='field'){
				 $subTast = $templateVars['fields'];
				}else{
				  $subTast= [1];
				}

				foreach ($subTast as $field){
					$aktualTemplateVars = $templateVars;
					if (is_array($field)){
						$aktualTemplateVars['field'] = $field;
					}
					$neddle = $this->renderMe($rtask['detect'], $aktualTemplateVars);
					if (strpos($newQuellcode, $neddle) === false) {
						$replaceAfter = $this->renderMe($rtask['replaceAfter'], $aktualTemplateVars);
						if (strpos($newQuellcode, $replaceAfter) !== false) {
							$newQuellcode = str_replace(
								$replaceAfter,
								$replaceAfter . $this->renderMe($rtask['template'], $aktualTemplateVars),
								$newQuellcode
							);
						}
					}
				}

			}
			if ($aktuellerQuellcode != $newQuellcode) {
				file_put_contents($destination, $newQuellcode);
			}
		}

		private function updateQuellcode($destination, $templateVars)
		{
			if (empty($this->task['updateTemplate'])) {
				$template = $this->task['insertTemplate'];
			} else {
				$template = $this->task['updateTemplate'];
			}
			$code = $this->renderMe($template, $templateVars);
			file_put_contents($destination, $code);
		}

		/**
		 * @param string[] $additionalTemplatePath
		 *
		 * @return \Twig_Environment
		 */
		private function getTemplater($additionalTemplatePath = [])
		{
			$templatePath = [];
			foreach ($additionalTemplatePath as $tplPath) {
				$helpPath = $this->templateRoot . 'templates/' . $tplPath;
				if (is_dir($helpPath)) {
					$templatePath[] = $helpPath;
				}
			}
			$templatePath[] = $this->templateRoot . 'templates/';
			$templatePath[] = $this->getProjectRoot() . 'data/temp/';

			$loader = new \Twig_Loader_Filesystem($templatePath);
			$twig   = new \Twig_Environment(
				$loader, [
				'cache' => $this->getCacheDir(),
				'debug' => true,
			]
			);
			$twig->addExtension(new \Twig_Extension_Debug());
			$twig->addExtension(new TwigExtension());

			return $twig;
		}


		private function getDesinationFile($templateVars)
		{
			return $this->getProjectRoot() . 'dist/' . $templateVars['template']['target'] . $this->renderString(
					$this->task['destinationFile'],
					$templateVars
				);
		}

		private function renderString($string, $templateVars)
		{
			$templateFile = $this->getCacheDir() . md5($string) . '.html';
			file_put_contents($templateFile, $string);

			return $this->getTemplater()->render(md5($string) . '.html', $templateVars);
		}

		private $cacheDirExisits;

		private function getCacheDir()
		{
			$return = $this->projectRoot . 'data/temp/';
			if ($this->cacheDirExisits != 1) {
				if (!is_dir($return)) {
					mkdir($return, 0777, true);
				}
				$this->cacheDirExisits = 1;
			}

			return $return;
		}

		/**
		 * @return array
		 */
		public function getTask()
		{
			return $this->task;
		}

		/**
		 * @param array $task
		 */
		public function setTask($task)
		{
			$this->task = $task;
		}

		/**
		 * @return mixed
		 */
		public function getProjectRoot()
		{
			return $this->projectRoot;
		}

		/**
		 * @param mixed $projectRoot
		 */
		public function setProjectRoot($projectRoot)
		{
			$this->projectRoot = $projectRoot;
		}

		/**
		 * @return mixed
		 */
		public function getTemplateRoot()
		{
			return $this->templateRoot;
		}

		/**
		 * @param mixed $templateRoot
		 */
		public function setTemplateRoot($templateRoot)
		{
			$this->templateRoot = $templateRoot;
		}


		/**
		 * @return mixed
		 */
		public function getProjectData()
		{
			return $this->projectData;
		}

		/**
		 * @param mixed $projectData
		 */
		public function setProjectData($projectData)
		{
			$this->projectData = $projectData;
		}

		/**
		 * @return array
		 */
		public function getTemplateData()
		{
			return $this->templateData;
		}

		/**
		 * @param array $templateData
		 *
		 * @return TaskControler
		 */
		public function setTemplateData($templateData)
		{
			$this->templateData = $templateData;

			return $this;
		}

	}