<?php
/**
 * @filesource modules/index/models/world.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\World;

/**
 * คลาสสำหรับเชื่อมต่อกับฐานข้อมูลของ GCMS.
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
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
