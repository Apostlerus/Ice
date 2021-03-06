<?php

return [
    'password' => [
        [
            'validator' => 'Ice:Not_Empty',
            'message' => 'Введите пароль.'
        ],
        [
            'validator' => 'Ice:Length_Min',
            'params' => ['minLength' => 3],
            'message' => 'Минимальная длина пароля {$minLength} символа.'
        ],
        [
            'validator' => 'Ice:Length_Max',
            'params' => ['maxLength' => 30],
            'message' => 'Максимальная длина пароля {$maxLength} символов.'
        ]
    ]
];