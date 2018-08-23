<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsHistory extends CartSms\Controller
{
    public function actionList()
    {
        $this->view('History', 'History', 'list', true);
    }
}