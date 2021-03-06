<?php
namespace ice\validator;

use ice\core\Validator;
use ice\helper\Phone;

/**
 * Class Data_Validator_Phone_Exists
 * Проверка в базе существования телефона
 */
class Phone_Exists extends Validator
{
    /**
     * Validate data by scheme
     *
     * @example:
     *  'user_name' => [
     *      [
     *          'validator' => 'Ice:Not_Empty',
     *          'message' => 'Введите имя пользователя.'
     *      ],
     *  ],
     *  'name' => 'Ice:Not_Null'
     *
     * @param $data
     * @param null $scheme
     * @return boolean
     */
    public function validate($data, $scheme = null)
    {
        return !Account::getQuery()
            ->where('phone LIKE ?', '%' . Phone::parse($data, true))
            ->limit(1)
            ->execute()
            ->asValue();
    }
}