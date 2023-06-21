<?php
/**
 * @filesource Kotchasan/Http/NotFound.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Kotchasan\Http;

/**
 * Response Class
 *
 * @see https://www.kotchasan.com/
 */
class NotFound extends Response
{
    /**
     * Send HTTP Error 404
     *
     * @param string||null $message ถ้าไม่กำหนดจะใช้ข้อความจากระบบ
     * @param int          $code    Error Code (default 404)
     */
    public function __construct($message = null, $code = 404)
    {
        $message = empty($message) ? '404 Not Found' : $message;
        parent::__construct($code);
        $response = $this->withProtocolVersion('1.0');
        if ($message) {
            $response->withContent($message);
        }
        $response->send();
        exit;
    }
}
