<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsModuleAbout extends CartSms\Controller
{
    public function actionDefault()
    {
        $this->view('About module', 'ModuleAbout', 'default', false);
    }


}