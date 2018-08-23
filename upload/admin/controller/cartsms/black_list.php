<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsBlackList extends CartSms\Controller
{
    public function actionDefault()
    {
        $this->view('Black list', 'BlackList', 'default', true);
    }

    public function actionImport()
    {
        $this->view('Black list', 'BlackList', 'import', true);
    }
}