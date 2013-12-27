<?php

namespace kartik\markdown;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\base\InvalidConfigException;

/**
 * A Markdown editor that implements PHP Markdown extra and PHP SmartyPants
 * styled using Twitter Bootstrap 3.0. Contains a lot of custom configuration
 * and conversion options.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class MarkdownEditor extends \yii\widgets\InputWidget {
	/**
	 * Header toolbar button constants
	 */
	const BTN_BOLD = 1;
	const BTN_ITALIC = 2;
	const BTN_PARAGRAPH = 3;
	const BTN_NEW_LINE = 4;
	const BTN_HEADING = 100;
	const BTN_LINK = 5;
	const BTN_IMAGE = 6;
	const BTN_INDENT_L = 7;
	const BTN_INDENT_R = 8;
	const BTN_UL = 9;
	const BTN_OL = 10;
	const BTN_DL = 11;
	const BTN_FOOTNOTE = 12;
	const BTN_QUOTE = 13;
	const BTN_CODE = 14;
	const BTN_CODE_BLOCK = 15;
	const BTN_HR = 16;
	const BTN_MAXIMIZE = 17;
	
	/**
	 * Heading dropdown items
	 */
	const BTN_H1 = 101;
	const BTN_H2 = 102;
	const BTN_H3 = 103;
	const BTN_H4 = 104;
	const BTN_H5 = 105;
	const BTN_H6 = 106;
	
	/**
	 * Footer toolbar button constants
	 */
	const BTN_PREVIEW = 50;
	const BTN_SAVE = 51;
	const BTN_SAVE_1 = 52;
	const BTN_SAVE_2 = 53;
	
	/**
	 * Custom icons to compensate for unavailable Bootstrap glyphicons
	 */	
	const ICON_CODE = <<< EOT
	<div style="margin-top: -4px; margin-bottom: -1px;">
		<span style="font-size: 1.3em;">&lsaquo;</span>/<span style="font-size: 1.3em;">&rsaquo;</span>
	</div>
EOT;
	const ICON_HR = <<< EOT
	<span style="color: #888; text-shadow: 0 4px 0 #ccc, 0 -4px 0 #ccc;">&mdash;</span>
EOT;

	/**
	 * @var array the header toolbar configuration. List of button groups
	 * to be setup for a Bootstrap styled toolbar. Each button group is an 
	 * array, which requires the following parameters:
	 * - buttons: array of buttons to be setup. 
	 *       - Array key is the button identifier. Should be one of the BTN constants.
	 *       - Array values contain these special attributes
	 * 		    - icon: string the name of the glyphicon to be embedded before the label
	 * 		    - label: string the label for the button. By default is HTML encoded and supports localization.
	 * 		    - encodeLabel: boolean whether the label is HTML encoded (if not set will use the
	 *		      global [[encodeLabels]] setting. If set to false will use the raw label as is.
	 *			- items: an array for dropdown list of links for each button. Configuration is similar to buttons array.
	 * 		    - options: HTML attributes for each button. If not set will use the global [[buttonOptions]]
	 * - options: HTML attributes for the button group
	 *
	 * @see [[_defaultToolbar]]
	 */	
	public $toolbar = [];
	/**
	 * @var int the default height of the textarea input in px
	 */	
	public $height = 260;
	/**
	 * @var boolean whether to encode the button labels
	 * Defaults to true.
	 */
	public $encodeLabels = true;
	/**
	 * @var array the HTML attributes for the container
	 * holding the header, input, and footer
	 */
	public $options = ['class' => 'kv-md-editor'];
	/**
	 * @var array the HTML attributes for the header
	 */
	public $headerOptions = ['class' => 'kv-md-header btn-toolbar'];
	/**
	 * @var array the HTML attributes for the textarea input
	 */
	public $inputOptions = ['class' => 'kv-md-input'];
	/**
	 * @var array the HTML attributes for the footer
	 */
	public $footerOptions = ['class' => 'kv-md-footer'];
	/**
	 * @var array the HTML attributes for the preview 
	 * container which will display the converted
	 * HTML text
	 */
	public $previewOptions = ['class' => 'kv-md-preview'];	
	/**
	 * @var array the HTML attributes for all toolbar button groups
	 * used in the header and footer
	 */
	public $buttonGroupOptions = [];
	/**
	 * @var array the HTML attributes for all toolbar buttons 
	 * used in the header and footer
	 */
	 public $buttonOptions = ['class' => 'btn btn-sm btn-default'];
	/**
	 * @var array the HTML attributes for the container holding all
	 * elements together
	 */
	public $containerOptions = ['class' => 'kv-md-container'];
	/**
	 * @var string the template to display the footer. The following
	 * special variables will be replaced:
	 * - {buttons}: array the configuration for footer toolbar buttons (see [[_defaultFooter]])
	 * - {message}: array the footer help message displayed (see [[footerMessage]])
	 */
	public $footer = '<div class = "btn-toolbar pull-right">{buttons}</div><div class="kv-md-hint">{message}</div><div class="clearfix"></div>';
	/**
	 * @var array the footer toolbar configuration. List of button groups
	 * to be setup for a Bootstrap styled toolbar. Each button group is an 
	 * array, which requires the following parameters:
	 * - buttons: array of buttons to be setup. 
	 *       - Array key is the button identifier. Should be one of the BTN constants.
	 *       - Array values contain these special attributes
	 * 		    - icon: string the name of the glyphicon to be embedded before the label
	 * 		    - label: string the label for the button. By default is HTML encoded and supports localization.
	 * 		    - encodeLabel: boolean whether the label is HTML encoded (if not set will use the
	 *		      global [[encodeLabels]] setting. If set to false will use the raw label as is.
	 *			- items: an array for dropdown list of links for each button. Configuration is similar to buttons array.
	 * 		    - options: HTML attributes for each button. If not set will use the global [[buttonOptions]]
	 * - options: HTML attributes for the button group
	 *
	 * @see [[_defaultFooter]]
	 */	
	public $footerButtons = [];	
	/**
	 * @var string help message displayed in footer
	 */
	public $footerMessage;
	/**
	 * @var string message displayed if preview is empty
	 */	
	public $emptyPreview;
	/**
	 * @var string alert message displayed before saving output as Text
	 */
	public $saveTextAlert;
	/**
	 * @var string alert message displayed before saving output as HTML
	 */
	public $saveHtmlAlert;
	/**
	 * @var string the header message appended at the beginning of the 
	 * saved converted output
	 */	
	public $saveHeader;
	/**
	 * @var string the CSS applied to the saved converted output
	 */	
	public $saveCss;
	/**
	 * @var Module
	 */
	private $_module;
	/**
	 * @var array default header toolbar configuration
	 */
	private $_defaultToolbar = [
		[
			'buttons' => [
				self::BTN_BOLD => ['icon'=>'bold', 'title' => 'Bold'],
				self::BTN_ITALIC => ['icon'=>'italic', 'title' => 'Italic'],
				self::BTN_PARAGRAPH => ['icon'=>'font', 'title' => 'Paragraph'],
				self::BTN_NEW_LINE => ['icon'=>'text-height', 'title' => 'Append Line Break'],
				self::BTN_HEADING => ['icon' => 'header', 'title' => 'Heading', 'items' => [
					self::BTN_H1 => ['label' => 'Heading 1', 'options' => ['class' => 'kv-heading-1', 'title'=>'Heading 1 Style']],
					self::BTN_H2 => ['label' => 'Heading 2', 'options' => ['class' => 'kv-heading-2', 'title'=>'Heading 2 Style']],
					self::BTN_H3 => ['label' => 'Heading 3', 'options' => ['class' => 'kv-heading-3', 'title'=>'Heading 3 Style']],
					self::BTN_H4 => ['label' => 'Heading 4', 'options' => ['class' => 'kv-heading-4', 'title'=>'Heading 4 Style']],
					self::BTN_H5 => ['label' => 'Heading 5', 'options' => ['class' => 'kv-heading-5', 'title'=>'Heading 5 Style']],
					self::BTN_H6 => ['label' => 'Heading 6', 'options' => ['class' => 'kv-heading-6', 'title'=>'Heading 6 Style']],
				]],
			],
		],
		[
			'buttons' => [
				self::BTN_LINK => ['icon' => 'link', 'title' => 'URL/Link'],
				self::BTN_IMAGE => ['icon' => 'picture', 'title' => 'Image'],
			],
		],
		[
			'buttons' => [
				self::BTN_INDENT_L => ['icon' => 'indent-left', 'title' => 'Indent Text'],
				self::BTN_INDENT_R => ['icon' => 'indent-right', 'title' => 'Unindent Text'],
			],
		],
		[
			'buttons' => [
				self::BTN_UL => ['icon' => 'list', 'title' => 'Bulleted List'],
				self::BTN_OL => ['icon' => 'list-alt', 'title' => 'Numbered List'],
				self::BTN_DL => ['icon' => 'th-list', 'title' => 'Definition List'],
			],
		],
		[
			'buttons' => [
				self::BTN_FOOTNOTE => ['icon' => 'edit', 'title' => 'Footnote'],
				self::BTN_QUOTE => ['icon' => 'comment', 'title' => 'Block Quote'],
			],
		],
		[
			'buttons' => [
				self::BTN_CODE => ['label' => self::ICON_CODE, 'title' => 'Inline Code', 'encodeLabel'=>false],
				self::BTN_CODE_BLOCK => ['icon' => 'sound-stereo', 'title' => 'Code Block'],
			],
		],
		[
			'buttons' => [
				self::BTN_HR => ['label' => self::ICON_HR, 'title' => 'Horizontal Line', 'encodeLabel'=>false],
			],
		],
		[
			'buttons' => [
				self::BTN_MAXIMIZE => ['icon' => 'fullscreen', 'title' => 'Toggle full screen', 'data-enabled' => true]
			],
			'options' => ['class'=>'pull-right']
		],
	];
	/**
	 * @var array default footer toolbar configuration
	 */	
	private $_defaultFooter = [
		[
			'buttons' => [
				self::BTN_SAVE => ['icon' => 'floppy-disk', 'label'=>'Save', 'title' => 'Save content', 'class' => 'btn btn-sm btn-primary', 'data-enabled' => true, 'items' => [
					self::BTN_SAVE_1 => ['icon' => 'floppy-save', 'label' => 'Text', 'options' => ['title'=>'Save as text']],
					self::BTN_SAVE_2 => ['icon' => 'floppy-saved', 'label' => 'HTML', 'options' => ['title'=>'Save as HTML']],				
				]],
			]
		],
		[
			'buttons' => [
				self::BTN_PREVIEW => ['icon' => 'search', 'label'=>'Preview', 'title' => 'Preview formatted text'],
			]
		],
	];

	/**
	 * Initialize the widget
	 */		
	public function init() {
		parent::init();
		$this->_module = Yii::$app->getModule('markdown');
		if ($this->_module === null) {
			throw new InvalidConfigException("The module 'markdown' was not found. Ensure you have setup the 'markdown' module in your Yii configuration file.");
		}
		$this->generateId();
		$this->generateMessages();
		$this->registerAssets();
		echo Html::beginTag('div', $this->containerOptions);
		echo Html::beginTag('div', $this->options);
		echo $this->renderHeader();
		echo $this->renderInput();
		echo $this->renderFooter();	
	}

	/**
	 * Run the widget
	 */		
	public function run() {
		parent::run();
		echo Html::endTag('div');
		echo Html::endTag('div');
	}

	/**
	 * Generate HTML Identifier for a button
	 * @param int $btn the button identifier (one of the BTN constants)
	 */	
	protected function getButtonId($btn) {
		return $this->inputOptions['id'] . '-btn-' . $btn;
	}
	
	/**
	 * Generate HTML identifiers for elements
	 */		
	protected function generateId() {
		if (empty($this->options['id'])) {
			$this->options['id'] = $this->getId();
		}
		if (empty($this->containerOptions['id'])) {
			$this->containerOptions['id'] = $this->getId() . '-container';
		}
		if (empty($this->headerOptions['id'])) {
			$this->headerOptions['id'] = $this->options['id'] . '-header';
		}
		if (empty($this->footerOptions['id'])) {
			$this->footerOptions['id'] = $this->options['id'] . '-footer';
		}
		if (empty($this->previewOptions['id'])) {
			$this->previewOptions['id'] = $this->options['id'] . '-preview';
		}
		if (empty($this->inputOptions['id'])) {
			$this->inputOptions['id'] = ($this->hasModel()) ? Html::getInputId($this->model, $this->attribute) : $this->options['id'] . '-input';
		}
	}
	
	/**
	 * Render each button group in the toolbar
	 * @param array $group the button group configuration
	 * @param boolean $header whether the button group is part of the header
	 */
	 protected function renderButtonGroup($group, $header = true) {
		$groupOptions = empty($group['options']) ? [] : $group['options'];
		$groupOptions = array_replace($this->buttonGroupOptions, $groupOptions);
		Html::addCssClass($groupOptions, 'btn-group');
		$output = Html::beginTag('div', $groupOptions) . "\n";
		foreach ($group['buttons'] as $btn => $options) {
			if ($header) {
				$markup = ($btn !== self::BTN_MAXIMIZE && $btn !== self::BTN_PREVIEW && empty($items));
				$output .= $this->renderButton($btn, $options, $markup);
			}
			else {
				$output .= $this->renderButton($btn, $options, false);
			}
		}
		$output .= Html::endTag('div') . "\n";	
		return $output;
	}
	
	/**
	 * Render each button in the toolbar
	 * @param int $btn the button identifier (one of the BTN constants)
	 * @param array $options the HTML attributes for the button
	 * @param boolean $markup identifies whether the button needs to 
	 * call the markdown markup javascript on click. Defaults to true.
	 */	
	protected function renderButton($btn, $options = [], $markup = true) {
		$icon = ArrayHelper::remove($options, 'icon', '');
		$label = ArrayHelper::remove($options, 'label', '');
		$encodeLabel = ArrayHelper::remove($options, 'encodeLabel', $this->encodeLabels);
		if (strlen(trim($icon)) > 0) {
			$icon = "<i class='glyphicon glyphicon-{$icon}'></i>";
		}
		if (strlen(trim($label)) > 0) {
			$icon .= ' ';
		}
		$label = $icon . ($encodeLabel ? Yii::t('app', Html::encode($label)) : $label);
		$options = array_replace($this->buttonOptions, $options);
		$options['title'] = empty($options['title']) ? '' : Yii::t('app', $options['title']);
		$options['id'] = $this->getButtonId($btn);
		$items = ArrayHelper::remove($options, 'items', []);
		
		if (!empty($items)) {
			Html::addCssClass($options, 'dropdown-toggle');
			$options['data-toggle'] = 'dropdown';
			$label = $label . ' <span class="caret"></span>';
		}
		
		if ($markup) {
			$options['onclick'] = 'markUp(' . $btn . ', "#' . $this->inputOptions['id'] . '")';
		}
		
		$output = Html::button($label, $options) . "\n";
		
		if (!empty($items)) {
			$output .= "<ul class='dropdown-menu'>\n";
			foreach ($items as $key => $item) {
				if ($btn !== self::BTN_SAVE) {
					$item['options']['onclick'] = 'markUp(' . $key . ', "#' . $this->inputOptions['id'] . '")';
				}
				$item['options']['id'] = $this->getButtonId($key);
				$icon = empty($item['icon']) ? '' : '<i class="glyphicon glyphicon-' . $item['icon'] . '"></i> ';
				$output .= "<li>" . Html::a($icon . $item['label'], '#', $item['options']) . "</li>";
			}
			$output .= "</ul>\n";
		}
		return $output;
	}
	
	/**
	 * Generate default messages
	 */	
	public function generateMessages() {
		if (!isset($this->footerMessage)) {
			$this->footerMessage = $this->getFooterMessage();
		}
		if (!isset($this->emptyPreview)) {
			$this->emptyPreview = '<p class="help-block text-center">' . Yii::t('app', 'No content to display') . '</p>';
		}
		if (!isset($this->saveTextAlert)) {
			$this->saveTextAlert = Yii::t('app', 'Your Text file will be generated. Save the file to your client ' .
				"with .txt extension in the accompanying dialog.\n\n" . 
				"Disable any popup blockers in your browser to ensure proper download.");
		}
		if (!isset($this->saveHtmlAlert)) {
			$this->saveHtmlAlert = Yii::t('app', 'Your HTML file will be generated. Save the file to your client ' .
				"with .htm/.html extension in the accompanying dialog.\n\n" . 
				"Disable any popup blockers in your browser to ensure proper download.");
		}
		if (!isset($this->saveHeader)) {
			$this->saveHeader = Yii::t('app', "> - - -\n> Markdown Export\n> ===============\n> *Generated " . 
				date("d-M-Y H:i") . 
				" by \\kartik\\markdown\\MarkdownEditor*\n> - - -\n\n");
		}
		if (!isset($this->saveCss)) {
			$this->saveCss = Html::cssFile('http://netdna.bootstrapcdn.com/bootstrap/3.0.3/css/bootstrap.min.css') . 
				"\n" .
				Html::style('body{margin:20px;padding:20px;border:1px solid #ddd;border-radius:5px;}' .
				'th[align="right"]{text-align:right!important;}' .
				'th[align="center"]{text-align:center!important;}');
		}
	}

	/**
	 * Generates the footer message
	 */	
	protected function getFooterMessage() {
		$bullet = '<i class="glyphicon glyphicon-arrow-right"></i>';
		$link1 =  '<a href="http://michelf.ca/projects/php-markdown/extra/" target="_blank">' . Yii::t('app', 'PHP Markdown Extra') . '</a>';
		$link2 =  '<a href="http://michelf.ca/projects/php-smartypants/" target="_blank">' . Yii::t('app', 'PHP SmartyPants') . '</a>';
		$msg1 = Yii::t('app', '{bullet} You may use {link1} and {link2} syntax.', [
			'bullet' => $bullet,
			'link1' => $link1,
			'link2' => $link2
		]);
		$msg2 = Yii::t('app', '{bullet}  To undo / redo, press CTRL-Z / CTRL-Y. You can also undo most button actions by clicking it again.', [
			'bullet' => $bullet,
		]);
		return $msg1 . '<br>' . $msg2;
	}
	
	/**
	 * Register client assets
	 */	
	protected function registerAssets() {
		$view = $this->getView();
		MarkdownEditorAsset::register($view);
		$params = [
			'container' => '#' . $this->containerOptions['id'],
			'editor' => '#' . $this->options['id'],
			'toolbar' => '#' . $this->headerOptions['id'],
			'source' => '#' . $this->inputOptions['id'],
			'target' => '#' . $this->previewOptions['id'],
			'preview' => '#' . $this->getButtonId(self::BTN_PREVIEW),
			'maximize' => '#' . $this->getButtonId(self::BTN_MAXIMIZE),
			'url' => Yii::$app->controller->createUrl($this->_module->previewAction),
			'save1' => '#' . $this->getButtonId(self::BTN_SAVE_1),
			'save2' => '#' . $this->getButtonId(self::BTN_SAVE_2),
			'nullMsg' => Yii::t('app', $this->emptyPreview),
			'height' => $this->height,
			'saveText' => $this->saveTextAlert,
			'saveHtml' => $this->saveHtmlAlert,
			'saveHeader' => $this->saveHeader,
			'saveCss' => $this->saveCss
		];
		
		$js = 'initEditor(' . Json::encode($params) . ')';
		$view->registerJs($js);
	}

	/**
	 * Render the editor header content
	 */
	protected function renderHeader() {
		$toolbar = array_replace($this->_defaultToolbar, $this->toolbar);
		$output = '';
		
		foreach ($toolbar as $group) {
			if (empty($group['buttons'])) {
				continue;
			}
			$output .= $this->renderButtonGroup($group, true);
		}
		return Html::tag('div', $output, $this->headerOptions);
	}
	
	/**
	 * Render the editor footer content
	 */	
	public function renderFooter() {
		$toolbar = array_replace($this->_defaultFooter, $this->footerButtons);
		$buttons = '';
		
		foreach ($toolbar as $group) {
			if (empty($group['buttons'])) {
				continue;
			}
			$buttons .= $this->renderButtonGroup($group, false);
		}
		
		$content = strtr($this->footer, [
			'{message}' => $this->footerMessage,
			'{buttons}' => $buttons,
		]);
		echo Html::tag('div', $content, $this->footerOptions);
	}
	
	/**
	 * Render the text area input
	 */	
	protected function renderInput() {
		if ($this->hasModel()) {
			$input = Html::activeTextArea($this->model, $this->attribute, $this->inputOptions);
		}
		else {
			$input = Html::textArea($this->name, $this->value, $this->inputOptions);
		}
		Html::addCssClass($this->previewOptions, 'hidden');
		$preview = Html::tag('div', '', $this->previewOptions);
		return $input . "\n" . $preview;
	}
	
}
