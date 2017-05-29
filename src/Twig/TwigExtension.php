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
            new \Twig_SimpleFilter('cctu', array($this, 'cctuFilter')),
            new \Twig_SimpleFilter('cctm', array($this, 'cctmFilter')),
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

    public function cctuFilter($string){
        return $this->cammelCaseToUnderscore($string);
    }

    public function cctmFilter($string){
        return str_replace('_','-',$this->cammelCaseToUnderscore($string));
    }

    public function cammelCaseToUnderscore($string){
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $string, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
        }
        return implode('_', $ret);
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