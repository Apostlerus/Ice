<?php
/**
 * Created by PhpStorm.
 * User: dp
 * Date: 17.03.14
 * Time: 20:25
 */

namespace ice\data\provider;

use ice\core\Data_Provider;
use ice\Exception;
use ice\helper\Dir;
use ice\helper\Json;

class File extends Data_Provider
{
    public static $connections = [];

    /**
     * @param $connection
     * @return boolean
     */
    protected function switchScheme(&$connection)
    {
        return true;
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function connect(&$connection)
    {
        $connection = $this->getOption('path');
        return (bool) Dir::get($connection);
    }

    /**
     * @param $connection
     * @return boolean
     */
    protected function close(&$connection)
    {
        return true;
    }

    private function getFileName($key) {
        return $this->getConnection() . $this->getKey($key) . '.php';
    }

    public function get($key = null)
    {
        $fileName = $this->getFileName($key);

        if (!file_exists($fileName)) {
            return null;
        }

        return include $fileName;
    }

    public function set($key, $value, $ttl = 3600)
    {
        $fileName = $this->getFileName($key);
        Dir::get(dirname($fileName));
        return file_put_contents($fileName, '<?php' . "\n" . 'return ' . var_export($value, true) . ';');
    }

    public function delete($key)
    {
        throw new Exception('Implement delete() method.');
    }

    public function inc($key, $step = 1)
    {
        throw new Exception('Implement inc() method.');
    }

    public function dec($key, $step = 1)
    {
        throw new Exception('Implement dec() method.');
    }

    public function flushAll()
    {
        throw new Exception('Implement flushAll() method.');
    }
}