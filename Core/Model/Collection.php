<?php
namespace ice\core\model;

use ice\core\Data;
use ice\core\Data_Source;
use ice\core\model\collection\Iterator;
use ice\core\Model;
use ice\core\Query;
use ice\Exception;
use IteratorAggregate;
use Traversable;

class Collection implements IteratorAggregate
{
    /** @var Data */
    private $_data = null;

    /** @var Query */
    private $_queryBuilder = null;

    /** @var Model */
    private $_modelClass = null;

    private function __construct($modelClass)
    {
        $this->_modelClass = $modelClass;
    }

    public static function byQuery(Query $query, Data_Source $dataSource = null)
    {
        /** @var Model $modelClass */
        $modelClass = $query->getModelClass();

        if (isset(class_parents($modelClass)[Factory::getClass()])) {
            $query->select('/delegate_name');
        }

        if (!$dataSource) {
            $dataSource = $modelClass::getDataSource();
        }

        return $query->execute($dataSource)->getCollection();
    }

    public static function create($modelClass)
    {
        return new Collection($modelClass);
    }

    public function getCount()
    {
        return count($this->getData());
    }

    /**
     * @return Data
     */
    public function getData()
    {
        if ($this->_data !== null) {
            return $this->_data;
        }

        $this->_data = $this->getQueryBuilder()->execute();

        return $this->_data;
    }

    /**
     * @param Data $data
     * @throws Exception
     */
    public function setData(Data $data)
    {
        $this->_data = $data;
    }

    public function getQueryBuilder()
    {
        if ($this->_queryBuilder !== null) {
            return $this->_queryBuilder;
        }

        if ($this->_data !== null) {
            return null;
        }

        /** @var Model $modelClass */
        $modelClass = $this->_modelClass;

        $this->_queryBuilder = $modelClass::getQueryBuilder();
        return $this->_queryBuilder;
    }

    public function first()
    {
        $row = $this->getRow();

        if (!$row) {
            return null;
        }

        /** @var Model $modelClass */
        $modelClass = $this->_modelClass;

        return $modelClass::create($row);
    }

    public function getRow($pk = null)
    {
        return $this->getData()->getRow($pk);
    }

    /**
     * @param Model $model
     * @return Collection
     * @throws Exception
     */
    public function add(Model $model)
    {
        if ($this->_queryBuilder !== null) {
            throw new Exception('В коллекцию, созданную запросом нельзя добавить свой элемент');
        }

        if ($this->_data === null) {
            $this->setData(new Data([DATA::RESULT_MODEL_CLASS => $this->_modelClass]));
        }

        $this->getData()->setRow($model->getPk(), $model->get());

        return $this;
    }

    public function insert(Data_Source $dataSource = null)
    {
        $modelClass = $this->_modelClass;

        $this->setData($modelClass::getQueryBuilder('insert')->values($this->getData()->getRows())->execute($dataSource));

        return $this;
    }

    public function update($updates, Data_Source $dataSource = null)
    {
        if (empty($updates)) {
            return $this;
        }

        $modelClass = $this->_modelClass;
        $keys = $this->getKeys();

        /** @var Query $query */
        $query = $modelClass::getQueryBuilder('update')->set($updates);

        if (count($keys) == 1) {
            $query->eq('/pk', reset($keys));
        } else {
            $query->in('/pk', $keys);
        }

        $this->setData($query->execute($dataSource));

        return $this;
    }

    public function getKeys()
    {
        return $this->getData()->getKeys();
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator()
    {
        return new Iterator($this->getData());
    }

    public function get($pk)
    {
        $row = $this->getRow($pk);

        if (!$row) {
            return null;
        }

        /** @var Model $modelClass */
        $modelClass = $this->_modelClass;

        return $modelClass::create($row);
    }

    public function remove($pk)
    {
        /** @var Model $modelClass */
        $modelClass = $this->_modelClass;

        return $modelClass::create($this->getData()->delete($pk));
    }

    /**
     * Filter model collection by filterScheme
     *
     *  $filterScheme = [
     *      ['name', 'Petya', '='],
     *      ['age', 18, '>'],
     *      ['surname', 'Iv%', 'like']
     *  ];
     *
     * @code
     *  ->filter('name', 'Petya')
     *  ->filter(['age', 18, '>'])
     *  ->filter([['surname', 'Iv%', 'like']])
     * @endcode
     *
     * @see \ice\helper\Arrays::filter
     *
     * @param $fieldScheme
     * @param null $value
     * @param string $comparsion
     * @return Collection
     */
    public function filter($fieldScheme, $value = null, $comparsion = '=')
    {
        if (!is_array($fieldScheme)) {
            return $this->filter([[$fieldScheme, $value, $comparsion]]);
        }

        if (!is_array(reset($fieldScheme))) {
            return $this->filter([$fieldScheme]);
        }

        /** @var Model $modelClass */
        $modelClass = $this->_modelClass;
        $collection = $modelClass::getCollection();
        $collection->setData($this->getData()->filter($fieldScheme));
        return $collection;
    }
}