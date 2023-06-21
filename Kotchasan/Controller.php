<?php
/**
 * @filesource Kotchasan/Controller.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya
 * @package Kotchasan
 */

namespace Kotchasan;

/**
 * Controller base class
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * create class
     *
     * @return static
     */
    public static function create()
    {
        return new static;
    }
}
