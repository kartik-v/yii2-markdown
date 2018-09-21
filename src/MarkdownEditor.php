<?php

/**
 * @package   yii2-markdown
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015 - 2018
 * @version   1.3.1
 */

namespace kartik\markdown;

use Yii;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\web\View;
use kartik\base\Config;
use kartik\base\InputWidget;

/**
 * A Markdown editor that implements PHP Markdown extra and PHP SmartyPantsTypographer
 * styled using Bootstrap 3.0. Contains a lot of custom configuration
 * and conversion options.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class MarkdownEditor extends InputWidget
{
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
    const BTN_EXPORT = 51;
    const BTN_EXPORT_1 = 52;
    const BTN_EXPORT_2 = 53;

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
     *   - Array key is the button identifier. Should be one of the BTN constants.
     *   - Array values contain these special attributes
     *     - icon: string the name of the glyphicon to be embedded before the label
     *     - label: string the label for the button. By default is HTML encoded and supports localization.
     *     - encodeLabel: boolean whether the label is HTML encoded (if not set will use the
     *       global [[encodeLabels]] setting. If set to false will use the raw label as is.
     *     - items: an array for dropdown list of links for each button. Configuration is similar to buttons array.
     *     - options: HTML attributes for each button. If not set will use the global [[buttonOptions]]
     * - options: HTML attributes for the button group
     *
     * @see function [[setDefaultToolbar()]]
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
     * @var array the HTML attributes for the textarea input
     */
    public $options = ['class' => 'kv-md-input'];

    /**
     * @var array the HTML attributes for the container
     * holding the header, input, and footer
     */
    public $editorOptions = ['class' => 'kv-md-editor'];

    /**
     * @var array the HTML attributes for the header
     */
    public $headerOptions = ['class' => 'kv-md-header btn-toolbar'];

    /**
     * @var array the HTML attributes for the footer
     */
    public $footerOptions = ['class' => 'kv-md-footer'];

    /**
     * @var array the HTML attributes for the preview container which will display the converted HTML text
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
    public $buttonOptions = [];

    /**
     * @var array the HTML attributes for the container holding all
     * elements together
     */
    public $containerOptions = ['class' => 'kv-md-container'];

    /**
     * @var string the template to display the footer. The following
     * special variables will be replaced:
     * - {buttons}: array the configuration for footer toolbar buttons (see function [[setDefaultFooter()]])
     * - {message}: array the footer help message displayed (see [[footerMessage]])
     */
    public $footer = '<div class = "btn-toolbar pull-right float-right">{buttons}</div><div class="kv-md-hint">{message}</div><div class="clearfix"></div>';

    /**
     * @var array the footer toolbar configuration. List of button groups
     * to be setup for a Bootstrap styled toolbar. Each button group is an
     * array, which requires the following parameters:
     * - buttons: array of buttons to be setup.
     *   - the array key is the button identifier. Should be one of the BTN constants.
     *   - the array values contain these special attributes
     *       - icon: string the name of the glyphicon to be embedded before the label
     *       - label: string the label for the button. By default is HTML encoded and supports localization.
     *       - encodeLabel: boolean whether the label is HTML encoded (if not set will use the
     *         global [[encodeLabels]] setting. If set to false will use the raw label as is.
     *       - items: an array for dropdown list of links for each button. Configuration is similar to buttons array.
     *       - options: HTML attributes for each button. If not set will use the global [[buttonOptions]]
     * - options: HTML attributes for the button group
     *
     * @see function [[setDefaultFooter()]]
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
     * @var string the content shown in preview window
     * while loading / processing the preview
     */
    public $previewProgress;

    /**
     * @var string Deprecated and not used since v1.3.1.
     * (originally alert message displayed before saving output as Text)
     */
    public $exportTextAlert;

    /**
     * @var string Deprecated and not used since v1.3.1.
     * (originally alert message displayed before saving output as HTML)
     */
    public $exportHtmlAlert;

    /**
     * @var string the header message appended at the beginning of the
     * exported converted output
     */
    public $exportHeader;

    /**
     * @var string the export file name for download. Defaults to `markdown-export`.
     */
    public $exportFileName;

    /**
     * @var string the export meta content to be appended at the beginning
     * of the exported converted output
     */
    public $exportMeta = <<< EOT
<!DOCTYPE html>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8"/>
<meta http-equiv="X-UA-Compatible" content="IE=edge;chrome=1"/>
EOT;

    /**
     * @var string the CSS applied to the exported converted output
     */
    public $exportCss;

    /**
     * @var boolean show the export button - defaults to true
     */
    public $showExport = true;

    /**
     * @var boolean show the preview button - defaults to true
     */
    public $showPreview = true;

    /**
     * @var string|null set custom action to use for preview
     */
    public $previewAction;

    /**
     * @var boolean use Smarty templates
     */
    public $smarty = true;

    /**
     * @var string the bootstrap CSS file on CDN which
     * will be used to format the exported HTML
     */
    public $bootstrapCssFile = 'http://netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css';

    /**
     * @var Module the markdown module
     */
    private $_module;

    /**
     * Initialize the widget
     */
    public function init()
    {
        parent::init();
        if (!isset($this->buttonOptions['class'])) {
            $this->buttonOptions['class'] = 'btn ' . $this->getDefaultBtnCss();
        }
        $this->_module = Config::initModule(Module::classname());
        if (!isset($this->bsVersion) && isset($this->_module->bsVersion)) {
            $this->bsVersion = $this->_module->bsVersion;
        }
        $this->generateId();
        $this->generateMessages();
        $this->registerAssets();
        echo Html::beginTag('div', $this->containerOptions);
        echo Html::beginTag('div', $this->editorOptions);
        echo $this->renderHeader();
        echo $this->renderInput();
        echo $this->renderFooter();
    }

    /**
     * Run the widget
     */
    public function run()
    {
        parent::run();
        echo Html::endTag('div');
        echo Html::endTag('div');
    }

    /**
     * Generate HTML Identifier for a button
     *
     * @param int $btn the button identifier (one of the BTN constants)
     * @return string
     */
    protected function getButtonId($btn)
    {
        return $this->options['id'] . '-btn-' . $btn;
    }

    /**
     * Generate HTML identifiers for elements
     */
    protected function generateId()
    {
        if (empty($this->editorOptions['id'])) {
            $this->editorOptions['id'] = $this->options['id'] . '-editor';
        }
        if (empty($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->options['id'] . '-container';
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
        Html::addCssStyle($this->options, 'height:' . $this->height . 'px');
    }

    /**
     * Render each button group in the toolbar
     *
     * @param array $group the button group configuration
     * @param boolean $header whether the button group is part of the header
     *
     * @return string
     */
    protected function renderButtonGroup($group, $header = true)
    {
        $groupOptions = empty($group['options']) ? [] : $group['options'];
        $groupOptions = array_replace($this->buttonGroupOptions, $groupOptions);
        Html::addCssClass($groupOptions, 'btn-group ml-1');
        $output = Html::beginTag('div', $groupOptions) . "\n";
        foreach ($group['buttons'] as $btn => $options) {
            if ($header) {
                $markup = ($btn !== self::BTN_MAXIMIZE && $btn !== self::BTN_PREVIEW && empty($items));
                $output .= $this->renderButton($btn, $options, $markup);
            } else {
                $output .= $this->renderButton($btn, $options, false);
            }
        }
        $output .= Html::endTag('div') . "\n";
        return $output;
    }

    /**
     * Render each button in the toolbar
     *
     * @param int $btn the button identifier (one of the BTN constants)
     * @param array $options the HTML attributes for the button
     * @param boolean $markup identifies whether the button needs to
     * call the markdown markup javascript on click. Defaults to true.
     * @return string
     */
    protected function renderButton($btn, $options = [], $markup = true)
    {
        $icon = ArrayHelper::remove($options, 'icon', '');
        $label = ArrayHelper::remove($options, 'label', '');
        $encodeLabel = ArrayHelper::remove($options, 'encodeLabel', $this->encodeLabels);
        $options['type'] = 'button';
        $prefix = $this->getDefaultIconPrefix();
        if (strlen(trim($icon)) > 0) {
            $icon = "<i class='{$prefix}{$icon}'></i>";
        }
        if (strlen(trim($label)) > 0) {
            $icon .= ' ';
        }
        $label = $icon . ($encodeLabel ? Html::encode($label) : $label);
        $options = array_replace_recursive($this->buttonOptions, $options);
        $options['title'] = empty($options['title']) ? '' : $options['title'];
        $options['id'] = $this->getButtonId($btn);
        $items = ArrayHelper::remove($options, 'items', []);

        if (!empty($items)) {
            Html::addCssClass($options, 'dropdown-toggle');
            $options['data-toggle'] = 'dropdown';
            if (!$this->isBs4()) {
                $label .= ' <span class="caret"></span>';
            }
        }

        if ($markup) {
            $options['data-key'] = $btn;
            $options['data-tool'] = 'md-button';
        }

        $output = Html::button($label, $options) . "\n";

        if (!empty($items)) {
            $output .= "<ul class='dropdown-menu'>\n";
            $prefix = $this->getDefaultIconPrefix();
            foreach ($items as $key => $item) {
                if ($btn !== self::BTN_EXPORT) {
                    $item['options']['data-key'] = $key;
                    $item['options']['data-tool'] = 'md-button';
                }
                $item['options']['id'] = $this->getButtonId($key);

                $icon = empty($item['icon']) ? '' : '<i class="' . $prefix . $item['icon'] . '"></i> ';
                if ($this->isBs4()) {
                    Html::addCssClass($item['options'], 'dropdown-item');
                }
                $output .= "<li>" . Html::a($icon . $item['label'], '#', $item['options']) . "</li>";
            }
            $output .= "</ul>\n";
        }
        return $output;
    }

    /**
     * Generate default messages
     */
    public function generateMessages()
    {
        if (!isset($this->footerMessage)) {
            $this->footerMessage = $this->getFooterMessage();
        }
        if (!isset($this->emptyPreview)) {
            $this->emptyPreview = '<p class="help-block text-center">' . Yii::t('kvmarkdown',
                    'No content to display') . '</p>';
        }
        if (empty($this->exportFileName)) {
            $this->exportFileName = Yii::t('kvmarkdown', 'markdown-export');
        }
        if (!isset($this->exportHeader)) {
            $this->exportHeader = "> - - -\n> " . Yii::t('kvmarkdown',
                    "Markdown Export{line} *Generated {date} by {class}", [
                        'line' => "\n> ===============\n>",
                        'date' => date("d-M-Y H:i"),
                        'class' => "\\kartik\\markdown\\MarkdownEditor*\n> - - -\n\n",
                    ]);
        }
        if (!isset($this->exportCss)) {
            $this->exportCss = Html::cssFile($this->bootstrapCssFile) .
                "\n" .
                Html::style(
                    'body{margin:20px;padding:20px;border:1px solid #ddd;border-radius:5px;}' .
                    'th[align="right"]{text-align:right!important;}' .
                    'th[align="center"]{text-align:center!important;}'
                );
        }
        if (!isset($this->previewProgress)) {
            $this->previewProgress = '<div class="kv-loading">' . Yii::t('kvmarkdown',
                    'Loading Preview') . ' &hellip;</div>';
        }
    }

    /**
     * Generates the footer message
     */
    protected function getFooterMessage()
    {
        $bullet = '<i class="' . $this->getDefaultIconPrefix() .  'arrow-right"></i>';
        $link1 = '<a href="http://michelf.ca/projects/php-markdown/extra/" target="_blank">' . Yii::t('kvmarkdown',
                'PHP Markdown Extra') . '</a>';
        $link2 = '<a href="http://michelf.ca/projects/php-smartypants/typographer/" target="_blank">' . Yii::t('kvmarkdown',
                'PHP SmartyPants Typographer') . '</a>';
        $link = $this->_module->smartyPants ? $link1 . ' ' . Yii::t('kvmarkdown', 'and') . ' ' . $link2 : $link1;
        $msg1 = Yii::t('kvmarkdown', '{bullet} You may use {link} syntax.', [
            'bullet' => $bullet,
            'link' => $link,
        ]);
        $keys = '<kbd>' . Yii::t('kvmarkdown', 'CTRL-Z') . '</kbd> / <kbd>' . Yii::t('kvmarkdown', 'CTRL-Y') . '</kbd>';
        $msg2 = Yii::t('kvmarkdown',
            '{bullet} To undo / redo, press {keys}. You can also undo most button actions by clicking it again.', [
                'bullet' => $bullet,
                'keys' => $keys,
            ]);
        return $msg1 . '<br>' . $msg2;
    }

    /**
     * Register client assets
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        MarkdownEditorAsset::register($view);
        $previewAction = (array)($this->previewAction !== null ? $this->previewAction : $this->_module->previewAction);
        $downloadAction = (array)$this->_module->downloadAction;
        $this->pluginOptions += [
            'containerId' => $this->containerOptions['id'],
            'editorId' => $this->editorOptions['id'],
            'toolbarId' => $this->headerOptions['id'],
            'previewContainerId' => $this->previewOptions['id'],
            'previewButtonId' => $this->getButtonId(self::BTN_PREVIEW),
            'maximizeButtonId' => $this->getButtonId(self::BTN_MAXIMIZE),
            'export1ButtonId' => $this->getButtonId(self::BTN_EXPORT_1),
            'export2ButtonId' => $this->getButtonId(self::BTN_EXPORT_2),
            'progress' => $this->previewProgress,
            'previewAction' => Url::to($previewAction),
            'downloadAction' => Url::to($downloadAction),
            'smarty' => $this->smarty,
            'nullMsg' => Yii::t('kvmarkdown', $this->emptyPreview),
            'height' => $this->height,
            'exportHeader' => $this->exportHeader,
            'exportMeta' => $this->exportMeta . "\n",
            'exportCss' => $this->exportCss,
            'filename' => $this->exportFileName,
        ];
        $this->registerPlugin('kvMarkdown');
    }

    /**
     * Setup default header toolbar
     */
    protected function setDefaultHeader()
    {
        if (!empty($this->toolbar)) {
            return;
        }

        $heading = function ($n) {
            return [
                'label' => Yii::t('kvmarkdown', 'Heading {n}', ['n' => $n]),
                'options' => [
                    'class' => 'kv-heading-' . $n,
                    'title' => Yii::t('kvmarkdown', 'Heading {n} Style', ['n' => $n]),
                ],
            ];
        };
        $isBs4 = $this->isBs4();

        $this->toolbar = [
            [
                'buttons' => [
                    self::BTN_BOLD => ['icon' => 'bold', 'title' => Yii::t('kvmarkdown', 'Bold')],
                    self::BTN_ITALIC => ['icon' => 'italic', 'title' => Yii::t('kvmarkdown', 'Italic')],
                    self::BTN_PARAGRAPH => ['icon' => 'font', 'title' => Yii::t('kvmarkdown', 'Paragraph')],
                    self::BTN_NEW_LINE => [
                        'icon' => 'text-height',
                        'title' => Yii::t('kvmarkdown', 'Append Line Break'),
                    ],
                    self::BTN_HEADING => [
                        'icon' => $isBs4 ? 'heading' : 'header',
                        'title' => Yii::t('kvmarkdown', 'Heading'),
                        'items' => [
                            self::BTN_H1 => $heading(1),
                            self::BTN_H2 => $heading(2),
                            self::BTN_H3 => $heading(3),
                            self::BTN_H4 => $heading(4),
                            self::BTN_H5 => $heading(5),
                            self::BTN_H6 => $heading(6),
                        ],
                    ],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_LINK => ['icon' => 'link', 'title' => Yii::t('kvmarkdown', 'URL/Link')],
                    self::BTN_IMAGE => ['icon' => $isBs4 ? 'image' : 'picture', 'title' => Yii::t('kvmarkdown', 'Image')],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_INDENT_L => ['icon' => $isBs4 ? 'outdent' : 'indent-left', 'title' => Yii::t('kvmarkdown', 'Indent Text')],
                    self::BTN_INDENT_R => ['icon' => $isBs4 ? 'indent' : 'indent-right', 'title' => Yii::t('kvmarkdown', 'Unindent Text')],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_UL => ['icon' => 'list', 'title' => Yii::t('kvmarkdown', 'Bulleted List')],
                    self::BTN_OL => ['icon' => 'list-alt', 'title' => Yii::t('kvmarkdown', 'Numbered List')],
                    self::BTN_DL => ['icon' => 'th-list', 'title' => Yii::t('kvmarkdown', 'Definition List')],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_FOOTNOTE => ['icon' => 'edit', 'title' => Yii::t('kvmarkdown', 'Footnote')],
                    self::BTN_QUOTE => ['icon' => 'comment', 'title' => Yii::t('kvmarkdown', 'Block Quote')],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_CODE => [
                        'label' => $isBs4 ? null : self::ICON_CODE,
                        'icon' => $isBs4 ? 'code' : null,
                        'title' => Yii::t('kvmarkdown', 'Inline Code'),
                        'encodeLabel' => false,
                    ],
                    self::BTN_CODE_BLOCK => ['icon' => $isBs4 ? 'laptop-code' : 'sound-stereo', 'title' => Yii::t('kvmarkdown', 'Code Block')],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_HR => [
                        'icon' => 'minus',
                        'title' => Yii::t('kvmarkdown', 'Horizontal Line'),
                        'encodeLabel' => false,
                    ],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_MAXIMIZE => [
                        'icon' => $isBs4 ? 'expand-arrows-alt' : 'fullscreen',
                        'title' => Yii::t('kvmarkdown', 'Toggle full screen'),
                        'data-enabled' => true,
                    ],
                ],
                'options' => ['class' => 'pull-right float-right'],
            ],
        ];
    }

    /**
     * Setup default footer toolbar
     */
    protected function setDefaultFooter()
    {
        if (!empty($this->footerButtons)) {
            return;
        }
        $isBs4 = $this->isBs4();
        $this->footerButtons = [
            [
                'buttons' => [
                    self::BTN_EXPORT => [
                        'icon' => $isBs4 ? 'file-export' : 'floppy-disk',
                        'label' => Yii::t('kvmarkdown', 'Export'),
                        'title' => Yii::t('kvmarkdown', 'Export content'),
                        'class' => 'btn btn-sm btn-primary',
                        'data-enabled' => true,
                        'items' => [
                            self::BTN_EXPORT_1 => [
                                'icon' => $isBs4 ? 'file-alt' : 'floppy-save',
                                'label' => Yii::t('kvmarkdown', 'Text'),
                                'options' => ['title' => Yii::t('kvmarkdown', 'Save as text')],
                            ],
                            self::BTN_EXPORT_2 => [
                                'icon' => $isBs4 ? 'file-invoice' : 'floppy-saved',
                                'label' => Yii::t('kvmarkdown', 'HTML'),
                                'options' => ['title' => Yii::t('kvmarkdown', 'Save as HTML')],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'buttons' => [
                    self::BTN_PREVIEW => [
                        'icon' => 'search',
                        'label' => Yii::t('kvmarkdown', 'Preview'),
                        'title' => Yii::t('kvmarkdown', 'Preview formatted text'),
                    ],
                ],
            ],
        ];

        if (!$this->showExport) {
            unset($this->footerButtons[0]);
        }
        if (!$this->showPreview) {
            unset($this->footerButtons[1]);
        }
    }

    /**
     * Render the editor header content
     */
    protected function renderHeader()
    {
        $output = '';
        $this->setDefaultHeader();
        foreach ($this->toolbar as $group) {
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
    public function renderFooter()
    {
        $buttons = '';
        $this->setDefaultFooter();
        foreach ($this->footerButtons as $group) {
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
    protected function renderInput()
    {
        if ($this->hasModel()) {
            $input = Html::activeTextArea($this->model, $this->attribute, $this->options);
        } else {
            $input = Html::textArea($this->name, $this->value, $this->options);
        }
        Html::addCssClass($this->previewOptions, 'hidden');
        $preview = Html::tag('div', '', $this->previewOptions);
        return $input . "\n" . $preview;
    }

}
