<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsSmsPrice extends CartSms\Controller
{
    public function actionList()
    {
        $this->view('Price list', 'SmsPrice', 'list', false);
    }
}