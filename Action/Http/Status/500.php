<?php
namespace ice\action;

use ice\core\Action;
use ice\core\action\View;
use ice\core\Action_Context;
use ice\Exception;
use ice\view\render\Php;

/**
 * Action of http status 500
 *
 * @package ice\action
 * @author dp
 */
class Http_Status_500 extends Action implements View
{
    /**
     * Initialization action context
     *
     * @return Action_Context
     */
    protected function init()
    {
        $actionContext = parent::init();
        $actionContext->setViewRenderClass(Php::VIEW_RENDER_PHP_CLASS);
        return $actionContext;
    }

    /**
     * Run action
     *
     * @param array $input
     * @param Action_Context $context
     * @throws Exception
     * @return array
     */
    protected function run(array $input, Action_Context &$context)
    {
        throw new Exception('Implement run() method.');
    }
}