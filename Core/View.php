<?php
namespace ice\core;

use ice\Exception;
use ice\Ice;

/**
 * Core view class
 *
 * @package ice\core
 * @author dp
 */
class View
{
    private $_viewData = [];
    private $_result = null;

    public function __construct(array $viewData)
    {
        $this->_viewData = $viewData;
    }

    /**
     * @return string
     */
    public function getLayout()
    {
        if (!isset($this->_viewData['layout'])) {
            $this->_viewData['layout'] = 'div#' . $this->_viewData['actionName'] . '{$view}';
        }

        return $this->_viewData['layout'];
    }

    public function display()
    {
        echo $this->fetch();
        return;
    }

    public function fetch()
    {
        if ($this->_result != null) {
            return $this->_result;
        }

        $template = $this->getTemplate();

        if (empty($template)) {
            $this->_result = '';
            return $this->_result;
        }

        $hash = crc32(serialize($this->_viewData['data']));

        $dataProvider = Data_Provider::getInstance(Ice::getEnvironment()->get('dataProviderKeys/' . __CLASS__));

        $this->_result = $dataProvider->get($hash);

        if ($this->_result) {
            return $this->_result;
        }

        /** @var View_Render $viewRenderClass */
        $viewRenderClass = $this->getViewRenderClass();

        $ext = $viewRenderClass::TEMPLATE_EXTENTION;

        array_unshift(View_Render::$templates, $template . $ext);

        try {
            $this->_result = $viewRenderClass::getInstance()->fetch($template, $this->_viewData['data'], $ext);
            $dataProvider->set($hash, $this->_result);
        } catch (\Exception $e) {
            $this->_result = Logger::getMessageView(new Exception('Fetch template "' . $template . $ext . '" failed', [], $e));
        }

        array_shift(View_Render::$templates);

        return $this->_result;
    }

    public function getTemplate()
    {
        if (!isset($this->_viewData['template'])) {
            $this->_viewData['template'] = $this->_viewData['actionName'];
        }

        return str_replace(array('_', '::'), '/', $this->_viewData['template']);
    }

    /**
     * @return string
     */
    public function getViewRenderClass()
    {
        if (isset($this->_viewData['viewRenderClass'])) {
            return $this->_viewData['viewRenderClass'];
        }

        return Ice::getConfig()->get('defaultViewRenderClass');
    }

    public function __toString()
    {
        return $this->fetch();
    }
}