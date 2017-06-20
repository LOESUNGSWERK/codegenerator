<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 17.05.17
 * Time: 21:25
 */

namespace RkuCreator;


class Creator extends AbstractCreator
{


	private $data;
	private $project;
	private $template;

	public function run(){
	    Log::writeLogLn('Start');
		$this->executeProject();
		Log::writeLogLn('fertig');
	}

	private function executeProject(){
		$data = array();
        $startTime = microtime(true);
		$template 		= $this->template;
		$project 		= $this->project;
		$templatePath 	= $this->generatePathToTemplate($template);
		$projectPath 	= $this->generatePathToProject($project);
		$templateDaten	= array_merge(
                                array('target'=>'./'),
		                        json_decode( file_get_contents( $templatePath.'creator.json'),true)
                          );
		$this->data['templates'] = $templateDaten;
		unset($this->data['templates']['tasks']);
		unset($this->data['templates']['defaults']);

		if (!is_array($data['defaults'])){ $data['defaults'] = array(); }
		if (!is_array($templateDaten['defaults'])){ $templateDaten['defaults'] = array(); }
        $defaults 		= array_merge($templateDaten['defaults'],$data['defaults']);
        $taskListe 		= $templateDaten['tasks'];
		$this->loadProjectData($projectPath.'/data/',$defaults);
		Log::writeLogLn('project: '.$project);
		Log::writeLogLn( str_pad('',strlen($project)+15,'='));
		while (list($key,$taskData)=@each($taskListe)){
			$task = new TaskControler();
			$task->setTemplateRoot($templatePath);
			$task->setProjectRoot($projectPath.'/');
			$task->setTask($taskData);
			$task->setDefaults($defaults);
			$task->setProjectData($this->data);
			$task->run();
		}
		file_put_contents($projectPath.'/data/tableData.json',json_encode($this->data,JSON_PRETTY_PRINT));
		Log::writeLogLn( str_pad('',strlen($project)+15,'-'));
		Log::writeLogLn('fertig nach '.
                        number_format((microtime(true)-$startTime),3,',','.' ).'sek' );
	}


	private function loadProjectData($path,$defaults){
		$tablePath = $path.'Table/';
		$referencesPath = $path.'References/';
        if (!is_array($defaults['field'])){ $defaults['field'] = array(); }
		$files = array_diff(scandir($tablePath), array('..', '.'));

        while (list($key,$file)=@each($files)){
			if (!is_array($defaults['table'])){$defaults['table'] = array(); }
		    $table = array_merge(
				$defaults['table'],
				json_decode( file_get_contents( $tablePath.'/'.$file), true)
			);
			$fields = $table['fields'];
			$table['fields'] = array();
			while (list($key,$val)=@each($fields)){
				$tableField = array_merge($defaults['field'],$val);
				$table['fields'][$tableField['fieldName']] = $tableField;
				$table['fieldsByTypes'][$tableField['fieldType']][$tableField['fieldName']] = $tableField;
				if($val['isPrimaryKey']){ 	$table['primaryFields'][$tableField['fieldName']] = $tableField; }
				if($val['isIndex']){ 		$table['indexFields'][$tableField['fieldName']] = $tableField; }
			}
			$this->data['tables'][$table['tableName']]=$table;
			$this->data['module'][$table['modulName']][]=$table['tableName'];

		}

		$files = array_diff(scandir($referencesPath), array('..', '.'));
		while (list($key,$file)=@each($files)){
		    if (!is_array($defaults['reference'])){$defaults['reference'] = array(); }
			$references = array_merge(
				$defaults['reference'],
				json_decode( file_get_contents( $referencesPath.'/'.$file), true)
			);
            foreach ($references as $reference) {
                if (trim($reference['masterField'])!=''){
                    $reference['masterField'] = $this->data['tables'][$reference['masterTable']]['fields'][$reference['masterField']];
                    $reference['childrenField'] = $this->data['tables'][$reference['childrenTable']]['fields'][$reference['childrenField']];
                    if (key_exists($reference['masterTable'],$this->data['tables'])){
                        $this->data['tables'][$reference['masterTable']]['children'][] = $reference;
                    }
                    if (key_exists($reference['childrenTable'],$this->data['tables'])) {
                        $this->data['tables'][$reference['childrenTable']]['parents'][] = $reference;
                    }
                }
            }
		}

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