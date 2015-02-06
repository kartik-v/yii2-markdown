<?php

/**
 * @package   yii2-markdown
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version   1.3.1
 */

namespace kartik\markdown;

use kartik\base\Config;
use yii\base\InvalidConfigException;

/**
 * Trait used for module validation
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.3.1
 */
trait ModuleTrait
{
    /**
     * Initializes and validates the module
     *
     * @return void
     *
     * @throws InvalidConfigException
     */
    protected function initModule()
    {
        $m = Module::MODULE;
        $this->_module = Config::fetchModule($m);
        if ($this->_module === null || !$this->_module instanceof \kartik\markdown\Module) {
            throw new InvalidConfigException("The '{$m}' module MUST be setup in your Yii configuration file and must be an instance of '\kartik\markdown\Module'");
        }
    }
}