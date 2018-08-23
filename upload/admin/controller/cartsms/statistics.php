<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsStatistics extends CartSms\Controller
{
    public function actionDefault()
    {
        $this->view('Statistics', 'Statistics', 'default', true);
    }
}