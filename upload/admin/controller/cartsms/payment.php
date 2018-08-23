<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsPayment extends CartSms\Controller
{
    public function actionData()
    {
        $this->view('Payment Data', 'Payment', 'data', true);
    }

    public function actionList()
    {
        $this->view('Payment list', 'Payment', 'list', true);
    }
}