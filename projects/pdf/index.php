<?php
/**
 * @filesource projects/pdf/index.php.
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */
/**
 * 0 (default )บันทึกข้อผิดพลาดร้ายแรงลง error_log .php
 * 1 บันทึกข้อผิดพลาดและคำเตือนลง error_log .php
 * 2 แสดงผลข้อผิดพลาดและคำเตือนออกทางหน้าจอ (ใช้เฉพาะตอนออกแบบเท่านั้น).
 */
define('DEBUG', 0);

/*
 * false (default)
 * true บันทึกการ query ฐานข้อมูลลง log (ใช้เฉพาะตอนออกแบบเท่านั้น)
 */
define('DB_LOG', false);

// Site root
define('ROOT_PATH', str_replace(array('\\', '/projects/pdf/index.php'), array('/', ''), __FILE__).'/');
// load Kotchasan
include '../../Kotchasan/load.php';
// Initial Kotchasan Framework
Kotchasan::createWebApplication()->run();
