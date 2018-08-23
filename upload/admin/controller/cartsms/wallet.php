<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsWallet extends CartSms\Controller
{
    public function actionDetail()
    {
        $this->view('Payment Data', 'Wallet', 'detail', true);
    }
}