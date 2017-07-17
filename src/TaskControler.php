<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 17.05.17
 * Time: 23:01
 */

namespace RkuCreator;

use RkuCreator\Twig\TwigTokenParserSwitch;
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


    public function run()
    {
        $startTime = microtime(true);
        Log::writeLog($this->task['caption'] . ': ');
        reset($this->projectData['tables']);
        foreach ($this->projectData['tables'] as $table) {
            $templateVars = $table;

            $templateVars['project'] = $this->projectData['project'];
            $templateVars['templates'] = $this->projectData['templates'];
            $templateVars['module'] = $this->projectData['module'];
            $templateVars['today'] = date('d.m.Y');
            $templateVars['now'] = date('d.m.Y H:i:s');

            $destination = $this->getDesinationFile($templateVars);
            Log::writeLog(pathinfo($destination, PATHINFO_FILENAME) . '[');
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
                        Log::writeLog('x');
                        break;
                }


            } else {
                $dir = pathinfo($destination, PATHINFO_DIRNAME);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                $code = $this->getTemplater([pathinfo($this->task['insertTemplateFile'], PATHINFO_DIRNAME)])->render($this->task['insertTemplateFile'], $templateVars);
                file_put_contents($destination, $code);
                Log::writeLog('i');
                if (strtoupper($this->task['onUpdate']) == 'REPLACE') {
                    $this->replaceQuellcode($destination, $templateVars);
                }
            }
            Log::writeLog('] ');
        }
        Log::writeLogLn(number_format((microtime(true) - $startTime), 3, ',', '.') . 'sek');
    }

    private function renderMe($string, $templateVars)
    {
        $possibleTemplateFile = $this->getTemplateRoot() . 'templates/' . $string;
        if (file_exists($possibleTemplateFile)) {
            return $this->getTemplater()->render($string, $templateVars);
        } else {
            return $this->renderString($string, $templateVars);
        }
    }

    private function replaceQuellcode($destination, $templateVars)
    {
        $aktuellerQuellcode = file_get_contents($destination);
        $newQuellcode = $aktuellerQuellcode;
        $replacetasks = $this->task['replaceTasks'];
        foreach ($replacetasks as $rtask) {
            $neddle = $this->renderMe($rtask['detect'], $templateVars);
            if (strpos($newQuellcode, $neddle) === false) {
                $replaceAfter = $this->renderMe($rtask['replaceAfter'], $templateVars);
                if (strpos($newQuellcode, $replaceAfter) !== false) {
                    $newQuellcode = str_replace(
                        $replaceAfter,
                        $replaceAfter . $this->getTemplater([pathinfo($rtask['templateFile'], PATHINFO_DIRNAME)])
                            ->render($rtask['templateFile'], $templateVars),
                        $newQuellcode
                    );
                }
            }
        }
        if ($aktuellerQuellcode != $newQuellcode) {
            file_put_contents($destination, $newQuellcode);
            Log::writeLog('r');
        }
    }

    private function updateQuellcode($destination, $templateVars)
    {
        if (empty($this->task['updateTemplateFile'])) {
            $template = $this->task['insertTemplateFile'];
        } else {
            $template = $this->task['updateTemplateFile'];
        }
        $code = $this->getTemplater([pathinfo($template, PATHINFO_DIRNAME)])->render($template, $templateVars);
        file_put_contents($destination, $code);
        Log::writeLog('u');
    }

    /**
     * @param string[] $additionalTemplatePath
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
        $twig = new \Twig_Environment($loader, array(
            'cache' => $this->getCacheDir(),
            'debug' => true
        ));
        $twig->addExtension(new \Twig_Extension_Debug());
        $twig->addExtension(new TwigExtension());
        //$twig->addExtension(new TwigTokenParserSwitch() );
        return $twig;
    }


    private function getDesinationFile($templateVars)
    {
        return $this->getProjectRoot() . 'dist/' . $templateVars['templates']['target'] . $this->renderString($this->task['destinationFile'],
                $templateVars);
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