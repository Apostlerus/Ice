<?php

namespace ice\action;

use Controller_Manager;
use ice\core\action\Cli;
use ice\core\Action;
use ice\core\Action_Context;

/**
 * Call legacy contoller action
 *
 * @package ice\action
 * @author dp
 */
class Controller_Call extends Action implements Cli
{
    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        $controllerAction = explode('/', $input['name']);
        unset($input['name']);

        Controller_Manager::call($controllerAction[0], $controllerAction[1], $input);
    }
}