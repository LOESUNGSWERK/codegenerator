<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 17.05.17
 * Time: 21:25
 */

namespace RkuCreator;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

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

        $taskListe = $templateDaten['tasks'];

        file_put_contents($this->generatePathToProject($project) . '/data/tableData.json', json_encode($projectDaten, JSON_PRETTY_PRINT));

        $this->commandIo->section('Starte Rendervorgang');
        $this->commandIo->block(
            'Es werden im Projekt >>' . $project . '<< ' . count($taskListe) . ' Templates mit ' . count($projectDaten['tables']) . ' tabellen gerendert'
        );

        $bar1 = new ProgressBar($this->commandIo->getOutput(), count($taskListe));
        $bar1->start(count($taskListe));
        $bar1->setFormat('%current:3s%/%max:3s% [%bar%] %percent:3s%% %message%');

        foreach ($taskListe as $taskData) {
            $bar1->setMessage($taskData['caption']);
            $bar1->advance();

            $this->commandIo->newLine();
            $task = new TaskControler();
            $task->setTemplateRoot($this->generatePathToTemplate($template));
            $task->setProjectRoot($this->generatePathToProject($project));
            $task->setTask($taskData);
            $task->setProjectData($projectDaten);
            $task->setTemplateData($templateDaten);
            $task->setCommandIo($this->commandIo);
            $task->run();
            $this->commandIo->write("\033[1A");

        }

        $bar1->setMessage('');
        $bar1->finish();

        $this->commandIo->block(
            'fertig nach ' .
            number_format((microtime(true) - $startTime), 3, ',', '.') . ' sek'
        );

        $fs = new Filesystem();
        $fs->remove($this->generatePathToProject($project) . 'data/temp');

    }


    protected function loadTempateDaten($template)
    {

        $templatePath = $this->generatePathToTemplate($template);

        $return = json_decode(
            file_get_contents($templatePath . 'creator.json'),
            true
        );

        $finder = new Finder();
        $finder->files()->name('*.json');
        foreach ($finder->in($templatePath . 'templates') as $file) {
            $help = json_decode(
                file_get_contents($file->getRealPath()),
                true
            );

            if (empty($help['caption'])) {
                foreach ($help as $task) {
                    $return['tasks'][] = $task;
                }
            } else {
                $return['tasks'][] = $help;
            }

        }

        return $return;
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
                    $table           = json_decode(file_get_contents($tablePath . '/' . $file), true);
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
                    $references            = json_decode(file_get_contents($referencesPath . '/' . $file), true);
                    foreach ($references as $reference) {
                        if (trim($reference['masterField']) != '') {

                            $help = $daten['tables'][$reference['masterTable']];
                            unset($help['parents'], $help['children']);
                            $reference['master']['table'] = $help;
                            $reference['master']['field'] = $daten['tables'][$reference['masterTable']]['fields'][$reference['masterField']];

                            $help = $daten['tables'][$reference['childrenTable']];
                            unset($help['parents'], $help['children']);
                            $reference['child']['table'] = $help;
                            $reference['child']['field'] = $daten['tables'][$reference['childrenTable']]['fields'][$reference['childrenField']];

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