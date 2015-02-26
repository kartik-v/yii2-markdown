<?php

/**
 * @package   yii2-markdown
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2015
 * @version   1.3.1
 */

namespace kartik\markdown;

use kartik\base\Config;
use \Michelf\MarkdownExtra;
use \Michelf\SmartyPantsTypographer;
use yii\base\InvalidConfigException;

/**
 * Markdown provides concrete implementation for PHP Markdown Extra
 * and PHP SmartyPantsTypographer.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Markdown
{
    // SmartyPantsTypographer does nothing at all
    const SMARTYPANTS_ATTR_DO_NOTHING = 0;
    // "--" for em-dashes; no en-dash support
    const SMARTYPANTS_ATTR_EM_DASH = 1;
    // "---" for em-dashes; "--" for en-dashes
    const SMARTYPANTS_ATTR_LONG_EM_DASH_SHORT_EN = 2;
    // "--" for em-dashes; "---" for en-dashes
    const SMARTYPANTS_ATTR_SHORT_EM_DASH_LONG_EN = 3;

    /**
     * @var MarkdownExtra $markdown
     */
    protected static $markdown;

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param array  $config . Options to configure MarkdownExtra and smarty
     *  - markdown: array for MarkdownExtra configuration parameters
     *  - smarty: array for SmartyPantsTypographer configuration parameters
     *  - custom: array for Custom configuration parameters
     * @param int    $smartyMode the SmartyPantsTypographer processing mode
     *
     * @return string
     * @throws InvalidConfigException if module not set
     */
    public static function convert($content, $config = [], $smartyMode = self::SMARTYPANTS_ATTR_LONG_EM_DASH_SHORT_EN)
    {
        $module = Config::initModule(Module::classname());
        $output = $content;
        if (strlen($output) > 0) {
            $mdConfig = empty($config['markdown']) ? [] : $config['markdown'];
            $output = static::process($content, $mdConfig);
            if ($module->smartyPants) {
                $smConfig = empty($config['smarty']) ? [] : $config['smarty'];
                $smarty = new SmartyPantsTypographer($smartyMode);
                foreach ($smConfig as $name => $value) {
                    $smarty->{$name} = $value;
                }
                $output = $smarty->transform($output);
                $cuConfig = empty($config['custom']) ? $module->customConversion : $config['custom'];
                $output = static::customProcess($output, $cuConfig);
            }
        }
        return $output;
    }

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param array  $config
     *
     * @return string
     */
    public static function process($content, $config = [])
    {
        if (static::$markdown === null) {
            static::$markdown = new MarkdownExtra();
        }
        foreach ($config as $name => $value) {
            static::$markdown->{$name} = $value;
        }
        return static::$markdown->transform($content);
    }

    /**
     * Custom conversion of patterns
     *
     * @param string $content
     * @param array  $config . List of key value pairs to find and replace
     *
     * @return string
     */
    public static function customProcess($content, $config = [])
    {
        if (empty($config)) {
            return $content;
        }
        return strtr($content, $config);
    }

}