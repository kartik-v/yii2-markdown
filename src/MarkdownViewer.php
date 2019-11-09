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
class MarkdownViewer extends InputWidget
{
    /**
     * @var array the HTML attributes for the container holding all
     * elements together
     */
    public $containerOptions = ['class' => 'kv-md-container kv-md-preview'];

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
    }

    /**
     * Run the widget
     */
    public function run()
    {
        parent::run();
        echo $this->renderViewer();
    }

    /**
     * Render each button group in the toolbar
     *
     * @param array $group the button group configuration
     * @param boolean $header whether the button group is part of the header
     *
     * @return string
     */
    protected function renderViewer($group, $header = true)
    {
        echo Html::beginTag('div', $this->containerOptions);
        if ($this->hasModel()) {
            echo Markdown::convert(static::getAttributeValue($this->model, $this->attribute));
        } else {
            echo Markdown::convert(static::getAttributeValue($this->value));
        }
        echo Html::endTag('div');
    }

    /**
     * Generate HTML identifiers for elements
     */
    protected function generateId()
    {
        if (empty($this->containerOptions['id'])) {
            $this->containerOptions['id'] = $this->options['id'] . '-container';
        }
    }
}
