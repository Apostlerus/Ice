<?php
namespace ice\action;

use ice\core\action\Cli;
use ice\core\Action;
use ice\core\Action_Context;
use ice\core\Data_Mapping;
use ice\core\Data_Source;
use ice\core\model\Collection;
use ice\core\model\Defined;
use ice\core\Model;
use ice\Exception;

/**
 * Class sinchronizate defined models
 *
 * @package ice\action
 * @author dp
 */
class Model_Defined_Sync extends Action implements Cli
{
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
        $dataSource = Data_Source::getDefault();

        /** @var Model[] $modelClasses */
        $modelClasses = array_keys(Data_Mapping::getInstance()->getModelClasses());

        foreach ($modelClasses as $modelClass) {
            $modelClass = Model::getClass($modelClass);
            if (isset(class_parents($modelClass)[Defined::getClass()])) {
                /** @var Collection $rowCollection */
                $rowCollection = $modelClass::getQueryBuilder()
                    ->select('*')
                    ->execute($dataSource)
                    ->getCollection();

                $dataRows = $modelClass::getCollection()->getRows();

                if (!count($dataRows)) {
                    throw new Exception('Не определен конфиг Defined модели "' . $modelClass . '"');
                }

                foreach ($dataRows as $pk => $row) {
                    $query = null;
                    $model = $rowCollection->get($pk);
                    if ($model) {
                        $rowCollection->remove($pk)->update($row, null, $dataSource);
                        continue;
                    }
                    $modelClass::create($row)->insert($dataSource);
                }

                if ($rowCollection->getCount()) {
                    $modelClass::getQueryBuilder('delete')
                        ->in($modelClass::getPkName(), $rowCollection->getKeys())
                        ->execute($dataSource)->getSql();
                }
            }
        }
    }
}