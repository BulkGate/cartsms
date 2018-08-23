<?php
require_once DIR_SYSTEM.'/library/cartsms/Controller.php';

use BulkGate\Extensions;

/**
 * @property \Registry $registry
 */
class ControllerCartsmsInbox extends CartSms\Controller
{
    public function actionList()
    {
        $this->view('Inbox', 'Inbox', 'list', true);
    }
}