<?php

/**
 * @package   yii2-markdown
 * @author    Kartik Visweswaran <kartikv2@gmail.com>
 * @copyright Copyright &copy; Kartik Visweswaran, Krajee.com, 2014
 * @version   1.7.4
 */

namespace kartik\markdown;

use kartik\base\Config;
use yii\base\InvalidConfigException;

/**
 * Trait used for module validation
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @since 1.6.0
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
        if ($this->_module === null) {
            throw new InvalidConfigException("The module '{$m}' was not found. Ensure you have setup the '{$m}' module in your Yii configuration file.");
        }
    }
}