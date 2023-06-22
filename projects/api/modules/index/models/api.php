<?php
/**
 * @filesource modules/index/models/api.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Index\Api;

use Kotchasan\Http\Request;

/**
 * API Model.
 *
 * @see https://www.kotchasan.com/
 */
class Model
{
    /**
     * ฟังก์ชั่นแปลง id เป็นเวลา.
     *
     * @param Request $request
     *
     * @return string
     */
    public static function getTime(Request $request)
    {
        return \Kotchasan\Date::format($request->get('id')->toInt());
    }
}
