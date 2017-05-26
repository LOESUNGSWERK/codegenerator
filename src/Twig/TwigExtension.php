<?php
/**
 * Created by PhpStorm.
 * User: renne
 * Date: 22.05.17
 * Time: 22:41
 */

namespace RkuCreator\Twig;


class TwigExtension extends \Twig_Extension
{

	public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ucf', array($this, 'ucfFilter')),
            new \Twig_SimpleFilter('lcf', array($this, 'lcfFilter')),
            new \Twig_SimpleFilter('printr', array($this, 'printRFilter'), array( 'needs_context'=>true )),
        );
    }

    public function printRFilter($context){
        return print_r($context);
    }

    public function ucfFilter($string){
        return ucfirst($this->cammelCaseStr($string));
    }

	public function lcfFilter($string){
        return lcfirst($this->cammelCaseStr($string));
    }

    public function cammelCaseStr($string){
		$string = $this->_cammelCaseStr('-',$string);
		$string = $this->_cammelCaseStr('_',$string);
		$string = $this->_cammelCaseStr(' ',$string);
		$string = $this->_cammelCaseStr(',',$string);
		return lcfirst($string);
	}

	private function _cammelCaseStr($seperator,$string){
		$return = '';
		$help = explode($seperator,$string);
		@reset($help);
		while (list($key,$val)=@each($help)){
			$return .= ucfirst($val);
		}
		return $return;
	}


}