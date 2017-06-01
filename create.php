<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 17.05.17
 * Time: 14:30
 */

$loader = include __DIR__.'/vendor/autoload.php';
error_reporting(E_ALL & ~E_NOTICE );
$creator = new \RkuCreator\Creator();
$creator->run();

exec('cp -rvf  /home/rkuehle/project/www_root/codegenerator/projects/xresSettings/dist/zendFramework/module/SettingsUi /home/rkuehle/project/tutorial/module/');