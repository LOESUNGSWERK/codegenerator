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

class GenerateData extends Command
{

    const PATH_TO_PROJECTS = __DIR__ . '/../projects/';
    const PATH_TO_DEFAUTLS = __DIR__ . '/../data/defaultData/';


    protected function configure()
    {
        $this->setName('mysql')
             ->setDescription('Creates a new project.')
             ->setHelp('This command allows you to create a new project...')
             ->addArgument('database', InputArgument::OPTIONAL, '')
             ->addArgument('project', InputArgument::OPTIONAL, '')
             ->addArgument('table', InputArgument::OPTIONAL, '')
             ->addArgument('host', InputArgument::OPTIONAL, '')
             ->addArgument('user', InputArgument::OPTIONAL, '')
             ->addArgument('pw', InputArgument::OPTIONAL, '');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $oldStuff = json_decode(base64_decode(file_get_contents(__DIR__ . '/../data/lastTemp.dat')), true);

        $host = $input->getArgument('host');
        if (null === $host) {
            $host = $io->ask('Hostname ', $oldStuff['host']);
        }

        $user = $input->getArgument('user');
        if (null === $user) {
            $user = $io->ask('Username ', $oldStuff['user']);
        }

        $pw = $input->getArgument('pw');
        if (null === $pw) {
            $pw = $io->ask('Password ', $oldStuff['pw']);
        }

        if (null == $host ||
            null == $user ||
            null == $pw
        ) {
            $io->error('Bitte geben Sie einen Host, Username und Passwort an');

            return;
        }

        $database = $input->getArgument('database');
        if (null === $database) {
            $database = $io->ask('Datenbank ', $oldStuff['datenbank']);

            if (null === $database) {
                $io->error('keine Datenbank angegeben');
            }

        }

        $project = $input->getArgument('project');
        if (null === $project) {
            $project    = $oldStuff['project'];
            $projectDir = __DIR__ . '/../projects/';
            $projects   = [];
            $help       = array_diff(scandir($projectDir), ['..', '.']);
            $cnt        = 0;
            $projectNr  = 0;
            foreach ($help as $key => $file) {
                if (is_dir($projectDir . $file)) {
                    $projects[] = $file;
                    if ($project == $file) {
                        $projectNr = $cnt;
                    }
                    $cnt++;
                }
            }
            $helper   = $this->getHelper('question');
            $question = new ChoiceQuestion(
                'Please select your Project (defaults to ' . $project . ')',
                $projects,
                $projectNr
            );
            $question->setErrorMessage('Project %s is invalid.');
            $project = $helper->ask($input, $output, $question);
        }

        $io->clearScreen();

        $io->title('Mysql Database');
        $io->writeln(
            sprintf(
                'The tables of %s will be now rendered into %s',
                $database,
                $project
            )
        );

        $mysql = new MysqlCreator();
        $mysql->setProjectName($project);
        $mysql->setLocalhost($host);
        $mysql->setUser($user);
        $mysql->setPw($pw);
        $mysql->setDatenbank($database);
        $mysql->setCommandIo($io);
        //$mysql->setOverrideIfExists($overrideIfExists);
        $mysql->createDatenmodelle();

        file_put_contents(
            __DIR__ . '/../data/lastTemp.dat', base64_encode(
                json_encode(
                    [
                        'host'      => $host,
                        'user'      => $user,
                        'pw'        => $pw,
                        'datenbank' => $database,
                        'project'   => $project,
                    ]
                )
            )
        );

    }

}