<?php
/**
 * @filesource Kotchasan/KBase.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya
 * @package Kotchasan
 */

namespace Kotchasan;

use Kotchasan\Http\Request;

/**
 * Kotchasan base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
#[\AllowDynamicProperties]
class KBase
{
    /**
     * Config class
     *
     * @var object
     */
    protected static $cfg;
    /**
     * Server request class
     *
     * @var Request
     */
    protected static $request;
}
