<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2013
 * @package yii2-markdown
 * @version 1.0.0
 */

namespace kartik\markdown;

use Yii;

/**
 * A Markdown conversion and editing module for Yii Framework 2.0
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Module extends \yii\base\Module
{

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

	/**
	 * @var array the the internalization configuration for
	 * this module
	 */
	public $i18n = [];

	public function init()
	{
		parent::init();
		Yii::setAlias('@markdown', dirname(__FILE__));
		if (empty($this->i18n)) {
			$this->i18n = [
				'class' => 'yii\i18n\PhpMessageSource',
				'basePath' => '@markdown/messages',
				'forceTranslation' => true
			];
		}
		Yii::$app->i18n->translations['markdown'] = $this->i18n;
	}

}