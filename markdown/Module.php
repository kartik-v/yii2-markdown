<?php

namespace kartik\markdown;

class Module extends \yii\base\Module {
	/**
	 * @var string the namespace of the module's controller classes
	 */
    public $controllerNamespace = 'kartik\markdown\controllers';
	
	/**
	 * @var string the controller action route used
	 * for markdown editor preview
	 */
	public $previewAction = '/markdown/parse/preview';	

	/**
	 * @var array the list of custom conversion patterns
	 * - array key is the pattern to search
	 * - array value is the pattern to replace
	 */
	 public $customConversion = [
		'<table>' => '<table class="table table-bordered table-striped">'
	];
	
	/**
	 * @var boolean whether to use PHP SmartyPants
	 * to process the markdown output.
	 */
	public $smartyPants = true;
	
    public function init() {
        parent::init();
        // custom initialization code goes here
    }

}
