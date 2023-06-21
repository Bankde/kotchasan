<?php
/**
 * @filesource modules/index/controllers/menu.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Index\Menu;

/**
 * default Controller.
 *
 * @see https://www.kotchasan.com/
 */
class Controller extends \Kotchasan\Controller
{
    /*
     * Initial Controller.
     *
     * @param array $modules
     *
     * @return string
     */

    /**
     * @param $module
     */
    public function render($module)
    {
        // สร้างเมนู
        return \Index\Menu\View::create()->render($module);
    }
}
