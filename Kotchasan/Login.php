<?php
/**
 * @filesource Kotchasan/Login.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Kotchasan;

use Kotchasan\Http\Request;

/**
 * คลาสสำหรับตรวจสอบการ Login
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Login extends \Kotchasan\KBase
{
    /**
     * ตัวแปรบอกว่ามาจากการ submit
     * true มาจากการ submit
     * false default
     *
     * @var bool
     */
    public static $from_submit = false;
    /**
     * ชื่อ Input ที่ต้องการให้ active
     * login_username หรือ login_password
     *
     * @var string
     */
    public static $login_input;
    /**
     * ข้อความจาก Login Class
     *
     * @var string
     */
    public static $login_message;
    /**
     * ตัวแปรเก็บข้อมูลที่ส่งมา
     * เช่น username, password
     *
     * @var array
     */
    public static $login_params = array();

    /**
     * ตรวจสอบการ login เมื่อมีการเรียกใช้ class new Login
     * action=logout ออกจากระบบ
     * action=forgot ขอรหัสผ่านใหม่
     * ถ้าไม่มีทั้งสองส่วนด้านบน จะตรวจสอบการ login จาก session
     *
     * @param Request $request
     *
     * @return static
     */
    public static function create(Request $request)
    {
        try {
            // create class
            $obj = new static;
            // อ่านข้อมูลจากฟอร์ม login ฟิลด์ login_username
            self::$login_params['username'] = $request->post('login_username')->username();
            if (empty(self::$login_params['username'])) {
                if (isset($_SESSION['login'])) {
                    // session
                    if (isset($_SESSION['login']['username'])) {
                        self::$login_params['username'] = Text::username($_SESSION['login']['username']);
                    }
                    if (isset($_SESSION['login']['password'])) {
                        self::$login_params['password'] = Text::password($_SESSION['login']['password']);
                    }
                }
                // ตรวจสอบว่ามาจาก form login หรือไม่
                self::$from_submit = $request->post('login_username')->exists();
            } elseif ($request->post('login_password')->exists()) {
                // มีทั้ง username และ password จากการ submit
                self::$login_params['password'] = $request->post('login_password')->password();
                self::$from_submit = true;
            }
            // ค่าที่ส่งมา
            $action = $request->request('action')->toString();
            if ($action === 'logout' && !self::$from_submit) {
                // ออกจากระบบ
                $obj->logout($request);
            } elseif ($action === 'forgot') {
                // ขอรหัสผ่านใหม่
                $obj->forgot($request);
            } else {
                // เข้าระบบ ตรวจสอบค่าที่ส่งมา
                if (empty(self::$login_params['username']) && self::$from_submit) {
                    self::$login_message = Language::get('Please fill up this form');
                    self::$login_input = 'login_username';
                } elseif (empty(self::$login_params['password']) && self::$from_submit) {
                    self::$login_message = Language::get('Please fill up this form');
                    self::$login_input = 'login_password';
                } elseif (!self::$from_submit || (self::$from_submit && $request->isReferer())) {
                    // เข้าระบบ
                    $obj->login($request, self::$login_params);
                }
            }
        } catch (InputItemException $e) {
            self::$login_message = $e->getMessage();
        }
        return $obj;
    }

    /**
     * ฟังก์ชั่นออกจากระบบ
     *
     * @param Request $request
     */
    public function logout(Request $request)
    {
        // ลบ session และ cookie
        unset($_SESSION['login']);
        self::$login_message = Language::get('Logout successful');
        self::$login_params = array();
    }

    /**
     * ฟังก์ชั่นส่งอีเมลลืมรหัสผ่าน
     *
     * @param Request $request
     */
    public function forgot(Request $request)
    {

    }

    /**
     * ฟังก์ชั่นตรวจสอบการเข้าระบบ
     *
     * @param Request $request
     * @param array $params
     */
    public function login(Request $request, $params)
    {
        // ตรวจสอบการ login กับฐานข้อมูล
        $login_result = $this->checkLogin($params);
        if (is_array($login_result)) {
            // save login session
            $_SESSION['login'] = $login_result;
        } else {
            if (is_string($login_result)) {
                // ข้อความผิดพลาด
                self::$login_input = self::$login_input == 'password' ? 'login_password' : 'login_username';
                self::$login_message = $login_result;
            }
            // logout ลบ session และ cookie
            unset($_SESSION['login']);
        }
    }

    /**
     * ฟังก์ชั่นตรวจสอบการ login
     * เข้าระบบสำเร็จคืนค่าแอเรย์ข้อมูลสมาชิก, ไม่สำเร็จ คืนค่าข้อความผิดพลาด
     *
     * @param array $params ข้อมูลการ login ที่ส่งมา $params = array('username' => '', 'password' => '');
     *
     * @return string|array
     */
    public function checkLogin($params)
    {
        if ($params['username'] !== self::$cfg->get('username')) {
            self::$login_input = 'username';
            return 'not a registered user';
        } elseif ($params['password'] !== self::$cfg->get('password')) {
            self::$login_input = 'password';
            return 'password incorrect';
        }
        // คืนค่า user ที่ login
        return array(
            'username' => $params['username'],
            'password' => $params['password'],
            // สถานะ แอดมิน
            'status' => 1
        );
    }

    /**
     * ฟังก์ชั่นตรวจสอบสถานะแอดมิน
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าเป็นผู้ดูแลระบบและเข้าระบบแล้ว ไม่ใช่คืนค่า null
     *
     * @return array|null
     */
    public static function isAdmin()
    {
        $login = self::isMember();
        return isset($login['status']) && $login['status'] == 1 ? $login : null;
    }

    /**
     * ฟังก์ชั่นตรวจสอบการเข้าระบบ
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าเป็นสมาชิกและเข้าระบบแล้ว ไม่ใช่คืนค่า null
     *
     * @return array|null
     */
    public static function isMember()
    {
        return empty($_SESSION['login']) ? null : $_SESSION['login'];
    }

    /**
     * ตรวจสอบสถานะสมาชิก
     * แอดมินสูงสุด (status=1) ทำได้ทุกอย่าง
     * คืนค่าข้อมูลสมาชิก (แอเรย์) ถ้าไม่สามารถทำรายการได้คืนค่า null
     *
     * @param array        $login
     * @param array|int $statuses
     *
     * @return array|null
     */
    public static function checkStatus($login, $statuses)
    {
        if (!empty($login)) {
            if ($login['status'] == 1) {
                // แอดมิน
                return $login;
            } elseif (is_array($statuses)) {
                if (in_array($login['status'], $statuses)) {
                    return $login;
                }
            } elseif ($login['status'] == $statuses) {
                return $login;
            }
        }
        // ไม่มีสิทธิ
        return null;
    }
}
