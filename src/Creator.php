<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 17.05.17
 * Time: 21:25
 */

namespace RkuCreator;


class Creator
{


	private $data;

	public function run(){
		$projets = json_decode( file_get_contents( __DIR__ .'/../creator.json'), true);
		while (list($key,$val)=@each($projets['projects'])){
			$this->executeProject($val);
		}
	}

	private function executeProject($data){
	    $startTime = microtime(true);
		$this->data = array('project'=>$data);
		unset($this->data['project']['tasks']);
		unset($this->data['project']['defaults']);
		$template 		= $data['template'];
		$project 		= $data['project'];
		$templatePath 	= __DIR__ .'/../templates/'.$template.'/';
		$projectPath 	= __DIR__ .'/../projects/'.$project;
		$templateDaten	= json_decode( file_get_contents( $templatePath.'creator.json'),true);
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

		$files = array_diff(scandir($tablePath), array('..', '.'));
		while (list($key,$file)=@each($files)){
			$table = array_merge(
				$defaults['table'],
				json_decode( file_get_contents( $tablePath.'/'.$file), true)
			);
			$fields = $table['fields'];
			$table['fields'] = array();
			while (list($key,$val)=@each($fields)){
				$tableField = array_merge($defaults['field'],$val);
				$table['fields'][$tableField['fieldName']] = $tableField;
				if($val['isPrimaryKey']){ 	$table['primaryFields'][$tableField['fieldName']] = $tableField; }
				if($val['isIndex']){ 		$table['indexFields'][$tableField['fieldName']] = $tableField; }
			}
			$this->data['tables'][$table['tableName']]=$table;
		}

		$files = array_diff(scandir($referencesPath), array('..', '.'));
		while (list($key,$file)=@each($files)){
			$references = array_merge(
				$defaults['reference'],
				json_decode( file_get_contents( $referencesPath.'/'.$file), true)
			);
            foreach ($references as $reference) {
                if (trim($reference['masterField'])!=''){
                    $reference['masterField'] = $this->data['tables'][$reference['masterTable']]['fields'][$reference['masterField']];
                    $reference['childrenField'] = $this->data['tables'][$reference['childrenTable']]['fields'][$reference['childrenField']];
                    $this->data['tables'][$reference['masterTable']]['children'][] = $reference;
                    $this->data['tables'][$reference['childrenTable']]['parents'][] = $reference;
                }
            }
		}

	}


}