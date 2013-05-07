<?php

/**
 * @property HtmlHelper $Html
 */
class AutoJavascriptHelper extends AppHelper {

/**
 * Settings for this helper.
 * path => Path from which the controller/action file path will be built
 *         from. This is relative to the 'WWW_ROOT/js' directory
 *
 * @var array
 */
	public $settings = array(
		'path' => 'autoload');

/** 
 * View helpers required by this helper 
 * 
 * @var array 
 */ 
	public $helpers = array('Html'); 

/** 
 * Before Render callback 
 * 
 * @return void 
 * @access public 
 */ 
	public function beforeRender() {
		$path = '';
		extract($this->settings);
		if (!empty($path)) { 
			$path .= DS; 
		}

		$files = array(
			'layouts' . DS . $this->_View->layout . '.js',
			$this->request->params['controller'] . '.js',
			$this->request->params['controller'] . DS . $this->request->params['action'] . '.js');

		foreach ($files as $file) { 
			$file = $path . $file; 
			$includeFile = WWW_ROOT . 'js' . DS . $file;
			if (file_exists($includeFile)) { 
				$file = str_replace('\\', '/', $file); 
				$this->Html->script($file, array('inline' => false)); 
			}
		}
	}
}