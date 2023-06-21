<?php
/**
 * @filesource modules/index/models/world.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Index\World;

/**
 * คลาสสำหรับเชื่อมต่อกับฐานข้อมูลของ GCMS.
 *
 * @see https://www.kotchasan.com/
 */
class Model extends \Kotchasan\Orm\Field
{
    /**
     * table name.
     *
     * @var string
     */
    protected $table = 'world';
}
