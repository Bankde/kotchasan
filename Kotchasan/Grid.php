<?php
/**
 * @filesource Kotchasan/Grid.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Kotchasan;

/**
 * Grid System
 *
 * @see https://www.kotchasan.com/
 */
class Grid extends \Kotchasan\Template
{
    /**
     * Construct
     */
    public function __construct()
    {
        $this->cols = 1;
    }

    /**
     * คืนค่าจำนวนคอลัมน์ของกริด
     *
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * กำหนดจำนวนกอลัมน์ของกริด
     *
     * @param int $cols จำนวนคอลัมน์ มากกว่า 0
     *
     * @return static
     */
    public function setCols($cols)
    {
        $this->cols = max(1, (int) $cols);
        $this->num = $this->cols;
        return $this;
    }
}
