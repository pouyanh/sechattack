<?php
/**
 * Created by IntelliJ IDEA.
 * User: pouyan
 * Date: 8/5/14
 * Time: 12:23 AM
 */

namespace Sechattack;

use Pattack\Config;
use Phalcon\Config\Adapter\Json as JsonConfig;
use Phalcon\DI\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Http\Response;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Url;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\View\Engine\Volt;


class Bootstrap
{
    const DEFAULT_MODE = 'development';

    public function render()
    {
        try {
            return $this->getApplication()->handle()->getContent();
        } catch (\Exception $e) {
            //  TODO: Perform a recursive error tracer + render it in template
            echo "Error occured: " . $e->getMessage();
        }
    }

    protected function getApplication()
    {
        $di = new FactoryDefault();

        // TODO: Move it to somewhere else
        $di->set(
            'config',
            function () {
                $config = new Config();
                $config->setVariable('ROOT_PATH', getenv('ROOT_PATH'));
                $config->setVariable('MODE', getenv('MODE') ? getenv('MODE') : self::DEFAULT_MODE);
                $config->merge(new JsonConfig(__DIR__ . '/Config/' . $config->getVariable('MODE') . '.json'));

                return $config;
            }
        );

        $di->set(
            'volt',
            function ($view, $di) {
                $volt = new Volt($view, $di);

                $volt->setOptions(
                    [
                        'compiledPath' => $di->getConfig()->get('volt.path') . '/'
                    ]
                );

                return $volt;
            }
        );

        $di->set(
            'view',
            function () {
                $view = new View();
                $view->setViewsDir(__DIR__ . '/View');

                $view->registerEngines(
                    [
                        '.volt' => 'volt',
                        '.phtml' => 'Phalcon\Mvc\View\Engine\Php'
                    ]
                );

                return $view;
            }
        );

        $di->set(
            'url',
            function () {
                $url = new Url();
                $url->setBaseUri('/');

                return $url;
            }
        );

        $di->set(
            'dispatcher',
            function () {
                $eventsManager = new EventsManager();
                $eventsManager->attach(
                    'dispatch:beforeException',
                    function ($event, Dispatcher $dispatcher, \Exception $exception) {
                        if ($exception instanceof DispatchException) {
                            $forwardOptions = [
                                'action' => 'index',
                                'params' => array_merge([$dispatcher->getActionName()], $dispatcher->getParams())
                            ];

                            if ('index' == $dispatcher->getActionName()) {
                                $forwardOptions['controller'] = 'index';
                            }

                            /** @var Response $responce */
                            $responce = $dispatcher->getDI()->getResponse();
                            $responce->resetHeaders();

                            $dispatcher->forward($forwardOptions);

                            return false;
                        }
                    }
                );

                $dispatcher = new Dispatcher();
                $dispatcher->setDefaultNamespace('Sechattack\Controller');
                $dispatcher->setEventsManager($eventsManager);

                return $dispatcher;
            }
        );

        return new Application($di);
    }
}
