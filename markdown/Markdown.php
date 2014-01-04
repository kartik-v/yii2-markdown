<?php

/**
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2013
 * @package yii2-markdown
 * @version 1.0.0
 */

namespace kartik\markdown;

use \Michelf\SmartyPants;
use yii\base\InvalidConfigException;

/**
 * Markdown provides concrete implementation for PHP Markdown Extra
 * and PHP SmartyPants.
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.0
 */
class Markdown extends \yii\helpers\Markdown {

    // SmartyPants does nothing at all
    const SMARTYPANTS_ATTR_DO_NOTHING = 0;
    // "--" for em-dashes; no en-dash support  
    const SMARTYPANTS_ATTR_EM_DASH = 1;
    // "---" for em-dashes; "--" for en-dashes  
    const SMARTYPANTS_ATTR_LONG_EM_DASH_SHORT_EN = 2;
    // "--" for em-dashes; "---" for en-dashes  
    const SMARTYPANTS_ATTR_SHORT_EM_DASH_LONG_EN = 3;

    /**
     * Converts markdown into HTML
     *
     * @param string $content
     * @param array $config. Options to configure MarkdownExtra and smarty
     * - markdown: array for MarkdownExtra configuration parameters
     * - smarty: array for SmartyPants configuration parameters
     * - custom: array for Custom configuration parameters
     * @param int $smartyMode the SmartyPants processing mode
     * @return string
     * @throws InvalidConfigException if module not set
     */
    public static function convert($content, $config = [], $smartyMode = self::SMARTYPANTS_ATTR_LONG_EM_DASH_SHORT_EN) {
        $module = \Yii::$app->getModule('markdown');
        if ($module === null) {
            throw new InvalidConfigException("The module 'markdown' was not found. Ensure you have setup the 'markdown' module in your Yii configuration file.");
        }
        $output = $content;
        if (strlen($output) > 0) {
            $mdConfig = empty($config['markdown']) ? [] : $config['markdown'];
            $output = parent::process($content, $mdConfig);
            if ($module->smartyPants) {
                $smConfig = empty($config['smarty']) ? [] : $config['smarty'];
                $smarty = new SmartyPants($smartyMode);
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
     * Custom conversion of patterns
     * @param string $content
     * @param array $config. List of key value pairs to find and replace
     * @return string
     */
    public static function customProcess($content, $config = []) {
        if (empty($config)) {
            return $content;
        }
        return strtr($content, $config);
    }

}
