<?php
use Phalcon\Dispatcher;
use Phalcon\Mvc\Dispatcher as MvcDispatcher;
use Phalcon\Events\Event;
use Phalcon\Events\Manager as EventsManager;
use Common\Listener\BeforeRouterListener;
use Phalcon\Mvc\Dispatcher\Exception as DispatchException;

$di->set(
    "dispatcher",
    function () {
        // Create an EventsManager
        $eventsManager = new EventsManager();
        // Attach a listener
        $eventsManager->attach(
            "dispatch:beforeExecuteRoute",
            function (Event $event, $dispatcher) {
                (new BeforeRouterListener())->run();
            }
        );

        $eventsManager->attach(
            "dispatch:beforeException",
            function (Event $event, $dispatcher, Exception $exception) {
                // Handle 404 exceptions
                if ($exception instanceof DispatchException) {
                    $dispatcher->forward(
                        [
                            "controller" => "index",
                            "action" => "notfound",
                        ]
                    );

                    return false;
                }

                // Alternative way, controller or action doesn't exist
                switch ($exception->getCode()) {
                    case Dispatcher::EXCEPTION_HANDLER_NOT_FOUND:
                    case Dispatcher::EXCEPTION_ACTION_NOT_FOUND:
                        $dispatcher->forward(
                            [
                                "controller" => "index",
                                "action" => "notfound",
                            ]
                        );

                        return false;
                    default:
                        break;
                }

            }
        );


        $dispatcher = new MvcDispatcher();

        // Bind the EventsManager to the dispatcher
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;
    }
);