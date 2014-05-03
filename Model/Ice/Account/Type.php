<?php
namespace ice\model\ice;

use ice\core\model\Factory;
use ice\core\Validator;

abstract class Account_Type extends Factory
{
    /**
     * Регистрация пользователя
     *
     * @param array $data
     * @return mixed
     */
    abstract public function register(array $data);

    /**
     * Авторизация пользователя
     *
     * @param array $data
     * @return Account
     */
    abstract public function login(array $data);

    /**
     * @param array $data
     * @param $validatorName
     * @return User
     */
    public function check(array $data, $validatorName)
    {
        $dataValidatorName = 'ice\validator\\' . $this->getAccountTypeName() . '_' . $validatorName;
        return Validator::create($dataValidatorName)
            ->setData($data)
            ->validate();
    }

    public function getAccountTypeName()
    {
        return substr(get_class($this), strlen(__CLASS__ . '_'));
    }
}