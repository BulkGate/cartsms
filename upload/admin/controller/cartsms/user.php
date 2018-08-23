<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsUser extends CartSms\Controller
{
    public function actionProfile()
    {
        $this->view('User profile', 'User', 'profile', false);
    }
}