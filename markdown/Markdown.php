<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2013
 * @package yii2-markdown
 * @version 1.0.0
 */

namespace kartik\markdown;

use \Michelf\MarkdownExtra;
use \Michelf\SmartyPantsTypographer;
use yii\base\InvalidConfigException;
use yii\base\Component;

/**
 * Markdown provides concrete implementation for PHP Markdown Extra
 * and PHP SmartyPantsTypographer.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Markdown extends Component
{

    /**
     * @var MarkdownExtra
     */
    protected $markdown;

    protected $mdConfig;
    protected $smConfig;
    protected $cuConfig;

    public $config;
    public $module;

    // SmartyPantsTypographer does nothing at all
    const SMARTYPANTS_ATTR_DO_NOTHING = 0;
    // "--" for em-dashes; no en-dash support
    const SMARTYPANTS_ATTR_EM_DASH = 1;
    // "---" for em-dashes; "--" for en-dashes
    const SMARTYPANTS_ATTR_LONG_EM_DASH_SHORT_EN = 2;
    // "--" for em-dashes; "---" for en-dashes
    const SMARTYPANTS_ATTR_SHORT_EM_DASH_LONG_EN = 3;

    public function init()
    {
        $this->module = \Yii::$app->getModule('markdown');
        if ($this->module === null) {
            throw new InvalidConfigException("The module 'markdown' was not found. Ensure you have setup the 'markdown' module in your Yii configuration file.");
        }

        $this->mdConfig = empty($config['markdown']) ? [] : $config['markdown'];
        $this->smConfig = empty($config['smarty']) ? [] : $config['smarty'];
        $this->cuConfig = empty($config['custom']) ? $this->module->customConversion : $config['custom'];
    }

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param array $config . Options to configure MarkdownExtra and smarty
     * - markdown: array for MarkdownExtra configuration parameters
     * - smarty: array for SmartyPantsTypographer configuration parameters
     * - custom: array for Custom configuration parameters
     * @param int $smartyMode the SmartyPantsTypographer processing mode
     * @return string
     * @throws InvalidConfigException if module not set
     */
    public function convert($content, $smartyMode = self::SMARTYPANTS_ATTR_LONG_EM_DASH_SHORT_EN)
    {
        $output = $content;
        if (strlen($output) > 0) {
            $output = $this->beforeProcess($output);
            $output = $this->process($output);
            $output = $this->afterProcess($output);
            if ($this->module->smartyPants) {
                $smarty = new SmartyPantsTypographer($smartyMode);
                foreach ($this->smConfig as $name => $value) {
                    $smarty->{$name} = $value;
                }
                $output = $smarty->transform($output);
                $output = $this->customProcess($output);
            }
        }
        return $output;
    }

    public function beforeProcess($content)
    {
       return $content; 
    }

    public function afterProcess($content)
    {
        return $content;
    }

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param array $config
     * @return string
     */
    public function process($content)
    {
        if ($this->markdown === null) {
            $this->markdown = new MarkdownExtra();
        }
        foreach ($this->mdConfig as $name => $value) {
            $this->markdown->{$name} = $value;
        }
        return $this->markdown->transform($content);
    }

    /**
     * Custom conversion of patterns
     *
     * @param string $content
     * @param array $config . List of key value pairs to find and replace
     * @return string
     */
    public function customProcess($content)
    {
        if (empty($this->cuConfig)) {
            return $content;
        }
        return strtr($content, $this->cuConfig);
    }

}