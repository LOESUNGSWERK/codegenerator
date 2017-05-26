<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 17.05.17
 * Time: 23:01
 */

namespace RkuCreator;

use RkuCreator\Twig\TwigExtension;

class TaskControler
{

	/**
	 * @var array
	 */
	private $task;
	private $projectRoot;
	private $templateRoot;
	private $defaults;
	private $projectData = array();


	public function run(){
	    $startTime = microtime(true);
		Log::writeLog($this->task['caption'].': ');
		reset($this->projectData['tables']);
		foreach ($this->projectData['tables'] as $table) {
			$templateVars = $table;

			$templateVars['project']=$this->projectData['project'];
			$templateVars['templates']=$this->projectData['templates'];
			$templateVars['module']=$this->projectData['module'];

			$destination = $this->getDesinationFile($templateVars);
            Log::writeLog(pathinfo($destination,PATHINFO_FILENAME).'[');
            if (file_exists($destination)) {
            	if (empty($this->task['updateTemplateFile'])){
					$template = $this->task['insertTemplateFile'];
				}else{
            		$template = $this->task['updateTemplateFile'];
            	}
                $code = $this->getTemplater()->render($template, $templateVars);
                file_put_contents($destination, $code);
                Log::writeLog('u');
            } else {
                $dir = pathinfo($destination, PATHINFO_DIRNAME);
                if (!is_dir($dir)) { mkdir($dir, 0777, true); }
                $code = $this->getTemplater()->render($this->task['insertTemplateFile'], $templateVars);
                file_put_contents($destination, $code);
                Log::writeLog('i');
            }
            Log::writeLog('] ');
        }
        Log::writeLogLn(number_format((microtime(true)-$startTime),3,',','.' ).'sek');
	}


	/**
	 * @return \Twig_Environment
	 */
	private function getTemplater(){
		$loader = new \Twig_Loader_Filesystem(array(
			$this->templateRoot.'templates/',
			$this->getProjectRoot().'data/temp/'
		));
		$twig = new \Twig_Environment($loader, array(
			'cache' => $this->getCacheDir(),
			'debug' => true
		));
		$twig->addExtension(new \Twig_Extension_Debug());
		$twig->addExtension(new TwigExtension() );
		return $twig;
	}


	private function getDesinationFile($templateVars){
			$templateFile = $this->getCacheDir().md5($this->task['destinationFile']).'.html';
            file_put_contents($templateFile,$this->task['destinationFile']);
			return $this->getProjectRoot().'dist/'.$templateVars['templates']['target'].$this->getTemplater()->render(md5($this->task['destinationFile']).'.html', $templateVars);
	}


	private function getCacheDir(){
	    $return = $this->projectRoot.'data/temp/';
		if (!is_dir($return)){ mkdir($return,0777,true); }
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
	public function getDefaults()
	{
		return $this->defaults;
	}

	/**
	 * @param mixed $defaults
	 */
	public function setDefaults($defaults)
	{
		$this->defaults = $defaults;
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



}