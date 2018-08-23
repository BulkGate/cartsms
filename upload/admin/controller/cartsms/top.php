<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsTop extends CartSms\Controller
{
    public function actionUp()
    {
        $this->view('Campaign', 'Top', 'up', true);
    }
}