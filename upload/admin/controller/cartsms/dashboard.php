<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsDashboard extends CartSms\Controller
{
    public function actionDefault()
    {
        $this->view('Dashboard', 'Dashboard', 'default', false);
    }
}