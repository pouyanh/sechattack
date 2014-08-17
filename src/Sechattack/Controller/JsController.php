<?php
/**
 * Created by IntelliJ IDEA.
 * User: pouyan
 * Date: 8/5/14
 * Time: 10:19 PM
 */

namespace Sechattack\Controller;

use Phalcon\Mvc\Controller;
use Phalcon\Mvc\View;


class JsController extends Controller
{
    public function indexAction($param)
    {
        $filename = $this->di->getConfig()->get('assets.js.path') . '/' . $param;

        if (!is_readable($filename)) {
            return false;
        }

        $this->view->setRenderLevel(View::LEVEL_ACTION_VIEW);
        $this->view->setVar('content', file_get_contents($filename));
        $this->response->setContentType('text/javascript');
    }
}
