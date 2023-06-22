<?php
/**
 * @filesource modules/index/views/forgot.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 * @author Goragod Wiriya <admin@goragod.com>
 * @package Kotchasan
 */

namespace Index\Forgot;

use Kotchasan\Html;
use Kotchasan\Language;
use Kotchasan\Login;

/**
 * Forgot Form.
 *
 * @see https://www.kotchasan.com/
 */
class View extends \Kotchasan\View
{
    /**
     * แสดงผล.
     */
    public function render()
    {
        // แสดงผล
        $form = Html::create('form', array(
            'id' => 'forgot_frm',
            'class' => 'login',
            'autocomplete' => 'off',
            'gform' => false
        ));
        // h1
        $form->add('h1', array(
            'class' => 'icon-password',
            'innerHTML' => Language::get('Request new password')
        ));
        // message
        if (!empty(Login::$login_message)) {
            $form->add('p', array(
                'class' => empty(Login::$login_input) ? 'message' : 'error',
                'innerHTML' => Login::$login_message
            ));
        }
        // fieldset
        $fieldset = $form->add('fieldset');
        // email
        $fieldset->add('email', array(
            'id' => 'email',
            'labelClass' => 'g-input icon-email',
            'placeholder' => Language::get('Email'),
            'value' => isset(Login::$login_params['username']) ? Login::$login_params['username'] : '',
            'autofocus',
            'required',
            'accesskey' => 'e',
            'maxlength' => 255,
            'comment' => Language::get('New password will be sent to the email address registered. If you do not remember or do not receive emails. Please contact your system administrator (Please check in the Junk Box)')
        ));
        // input-groups (div สำหรับจัดกลุ่ม input)
        $group = $fieldset->add('groups');
        // a
        $group->add('a', array(
            'href' => self::$request->getUri()->withParams(array('action' => 'login'), true),
            'class' => 'td',
            'title' => Language::get('Administrator area'),
            'innerHTML' => ''.Language::get('Sign in').' ?'
        ));
        // submit
        $fieldset->add('submit', array(
            'class' => 'button ok large wide',
            'value' => Language::get('Get new password')
        ));
        $fieldset->add('hidden', array(
            'id' => 'action',
            'value' => 'forgot'
        ));
        // คืนค่า HTML
        return $form->render();
    }

    /**
     * title bar
     */
    public function title()
    {
        return Language::get('Request new password');
    }
}
