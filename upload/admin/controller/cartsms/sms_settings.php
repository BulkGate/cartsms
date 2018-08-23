<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsSmsSettings extends CartSms\Controller
{
    public function actionDefault()
    {
        $this->view('Sender ID Settings', 'SmsSettings', 'default', true);
    }
}