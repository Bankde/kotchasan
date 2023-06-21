<?php
/**
 * @filesource modules/index/controllers/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Index\Export;

use Kotchasan\Http\Request;

/**
 * default Controller
 *
 * @see https://www.kotchasan.com/
 */
class Controller extends \Kotchasan\Controller
{
    /**
     * ส่งออกเป็น PDF
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $pdf = new \Kotchasan\Pdf();
        $pdf->AddPage();
        $pdf->WriteHTML($request->post('content')->toString());
        $pdf->Output();
    }
}
