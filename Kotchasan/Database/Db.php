<?php
/**
 * @filesource Kotchasan/Database/Db.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Kotchasan\Database;

use Kotchasan\Database;

/**
 * Database base class
 *
 * @see https://www.kotchasan.com/
 */
abstract class Db extends \Kotchasan\KBase
{
    /**
     * database connection
     *
     * @var \Kotchasan\Database\Driver
     */
    protected $db;

    /**
     * Class constructor
     *
     * @param string $conn ชื่อของการเชื่อมต่อ ถ้าไม่ระบุจะไม่มีการเชื่อมต่อ database
     */
    public function __construct($conn)
    {
        $this->db = Database::create($conn);
    }

    /**
     * อ่าน database connection
     *
     * @return \Kotchasan\Database\Driver
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * อ่านค่ากำหนดของฐานข้อมูล
     *
     * @param string $key
     *
     * @return mixed
     */
    public function getSetting($key)
    {
        if (isset($this->db->settings->$key)) {
            return $this->db->settings->$key;
        }
    }

    /**
     * คืนค่ากำหนดทั้งหมดของฐานข้อมูล
     *
     * @return object
     */
    public function getAllSettings()
    {
        return $this->db->settings;
    }
}
