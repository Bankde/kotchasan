<?php
/**
 * @filesource modules/index/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Index\Index;

use Kotchasan\Http\Request;

/**
 * render HTML.
 *
 * @see https://www.kotchasan.com/
 */
class View extends \Kotchasan\View
{
    /**
     * แสดงผล.
     *
     * @param Request $request
     */
    public function render()
    {
        // เวลาปัจจุบัน
        $mktime = time();
        // init Curl
        $ch = new \Kotchasan\Curl();
        // call API Online
        $json = $ch->get('https://projects.kotchasan.com/api/api.php', array('method' => 'getTime', 'id' => $mktime));
        // JSON to Array
        $array = json_decode($json, true);
        // เตรียมข้อมูลสำหรับใส่ข้อมูลลงใน template
        $this->setContents(array(
            // เวลาปัจจุบัน ใส่ลงใน template
            '/{MKTIME}/' => $mktime,
            // ผลลัพท์ที่ได้จากการเรียก API
            '/{RESULT}/' => isset($array['result']) ? $array['result'] : ''
        ));
        // โหลด template index.html
        $template = file_get_contents('modules/index/views/index.html');
        // คืนค่า HTML template

        return $this->renderHTML($template);
    }
}
