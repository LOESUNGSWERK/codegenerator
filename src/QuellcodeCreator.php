<?php
	/**
	 * Created by PhpStorm.
	 * User: renne
	 * Date: 17.05.17
	 * Time: 21:25
	 */

	namespace RkuCreator;


	use Symfony\Component\Filesystem\Filesystem;

	class QuellcodeCreator extends AbstractCreator
	{
		/**
		 * @param string $project
		 * @param string $template
		 */
		public function run($project, $template)
		{
			$startTime = microtime(true);

			$projectDaten  = $this->loadProjectData($project);
			$templateDaten = $this->loadTempateDaten($template);

			$taskListe                = $templateDaten['tasks'];
			$projectDaten['defaults'] = array_merge_recursive(
				$templateDaten['defaults'],
				$projectDaten['defaults']
			);

			file_put_contents($this->generatePathToProject($project) . '/data/tableData.json', json_encode($projectDaten, JSON_PRETTY_PRINT));

			$this->commandIo->section('render project ' . $project . ' with template ' . $template);
			$totalSteps = (count($taskListe) * count($projectDaten['tables']));
			$this->commandIo->createProgressBar($totalSteps);
			$this->commandIo->progressStart($totalSteps);
			foreach ($taskListe as $taskData) {
				$task = new TaskControler();
				$task->setTemplateRoot($this->generatePathToTemplate($template));
				$task->setProjectRoot($this->generatePathToProject($project));
				$task->setTask($taskData);
				$task->setProjectData($projectDaten);
				$task->setTemplateData($templateDaten);
				$task->setCommandIo($this->commandIo);
				$task->run();
			}

			$this->commandIo->progressFinish();

			$this->commandIo->block(
				'fertig nach ' .
				number_format((microtime(true) - $startTime), 3, ',', '.') . ' sek'
			);

			$fs = new Filesystem();
			$fs->remove($this->generatePathToProject($project) . 'data/temp');

		}


		protected function loadTempateDaten($template)
		{
			return array_merge_recursive(
				[
					'target'   => './',
					'defaults' => [],
				],
				json_decode(
					file_get_contents($this->generatePathToTemplate($template) . 'creator.json'),
					true
				)
			);
		}

		private function loadProjectData($projetName)
		{
			$path           = $this->generatePathToProject($projetName);
			$tablePath      = $path . 'data/Table/';
			$referencesPath = $path . 'data/References/';
			$daten          = [
				'defaults' => [
					'field'     => [],
					'table'     => [],
					'reference' => [],
				],
				'tables'   => [],
				'module'   => [],
				'project'  => [],
			];

			if (file_exists($path . 'data/project.json')) {
				$daten['project'] = json_decode(file_get_contents($path . 'data/project.json'), true);
				if (!empty($daten['project']['defaults'])) {
					$daten['defaults'] = array_merge_recursive(
						$daten['defaults'],
						$daten['project']['defaults']
					);
				}
			}

			$defaults = $daten['defaults'];


			if (is_dir($tablePath)) {
				$files = array_diff(scandir($tablePath), ['..', '.']);

				if (null !== $files) {
					foreach ($files as $file) {
						if (!is_array($defaults['table'])) {
							$defaults['table'] = [];
						}
						$table           = array_merge_recursive(
							$defaults['table'],
							json_decode(file_get_contents($tablePath . '/' . $file), true)
						);
						$fields          = $table['fields'];
						$table['fields'] = [];
						while (list($key, $val) = @each($fields)) {
							$tableField                                                       = array_merge($defaults['field'], $val);
							$table['fields'][$tableField['name']]                             = $tableField;
							$table['fieldsByTypes'][$tableField['type']][$tableField['name']] = $tableField;
							if ($val['isPrimaryKey']) {
								$table['primaryFields'][$tableField['name']] = $tableField;
							}
							if ($val['isIndex']) {
								$table['indexFields'][$tableField['name']] = $tableField;
							}
						}
						$daten['tables'][$table['name']]    = $table;
						$daten['module'][$table['modul']][] = $table['name'];
					}
				}
			}

			if (is_dir($referencesPath)) {

				$files = array_diff(scandir($referencesPath), ['..', '.']);
				if (null !== $files) {
					foreach ($files as $file) {

						$defaults['reference'] = [];
						$references            = array_merge_recursive(
							$defaults['reference'],
							json_decode(file_get_contents($referencesPath . '/' . $file), true)
						);
						foreach ($references as $reference) {
							if (trim($reference['masterField']) != '') {
								$reference['masterField']   = $daten['tables'][$reference['masterTable']]['fields'][$reference['masterField']];
								$reference['childrenField'] = $daten['tables'][$reference['childrenTable']]['fields'][$reference['childrenField']];
								if (key_exists($reference['masterTable'], $daten['tables'])) {
									$daten['tables'][$reference['masterTable']]['children'][] = $reference;
								}
								if (key_exists($reference['childrenTable'], $daten['tables'])) {
									$daten['tables'][$reference['childrenTable']]['parents'][] = $reference;
								}
							}
						}
					}
				}

			}

			return $daten;
		}

		/**
		 * @return mixed
		 */
		public function getProject()
		{
			return $this->project;
		}

		/**
		 * @param mixed $project
		 */
		public function setProject($project)
		{
			$this->project = $project;
		}

		/**
		 * @return mixed
		 */
		public function getTemplate()
		{
			return $this->template;
		}

		/**
		 * @param mixed $template
		 */
		public function setTemplate($template)
		{
			$this->template = $template;
		}


	}