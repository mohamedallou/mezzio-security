<?php

declare(strict_types=1);

use Laminas\Validator\EmailAddress;
use Laminas\Validator\StringLength;
use MezzioSecurity\Dto\UserDto;

return [
    UserDto::class. '_register' => [
        [
            'name' => 'username',
            'required' => true,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 5,
                        'max' => 20,
                    ]
                ],
            ],
            'allow_empty' => false,
            'continue_if_empty' => false,
        ],
        [
            'name' => 'email',
            'required' => true,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                ],
            ],
            'allow_empty' => false,
            'continue_if_empty' => false,
        ],
        [
            'name' => 'password',
            'required' => true,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 8,
                    ]
                ],
            ],
            'allow_empty' => false,
            'continue_if_empty' => false,
        ],
    ],
    UserDto::class => [
        [
            'name' => 'username',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 5,
                        'max' => 20,
                    ]
                ],
            ],
            'allow_empty' => true,
            'continue_if_empty' => true,
        ],
        [
            'name' => 'email',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => EmailAddress::class,
                ],
            ],
            'allow_empty' => true,
            'continue_if_empty' => true,
        ],
        [
            'name' => 'password',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 8,
                    ]
                ],
            ],
            'allow_empty' => true,
            'continue_if_empty' => true,
        ],
        [
            'name' => 'first_name',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 3,
                        'max' => 50,
                    ]
                ],
            ],
            'allow_empty' => true,
            'continue_if_empty' => true,
        ],
        [
            'name' => 'last_name',
            'required' => false,
            'filters' => [
                [
                    'name' => 'Laminas\Filter\StringTrim',
                    'options' => [],
                ],
                [
                    'name' => 'Laminas\Filter\ToNull',
                    'options' => [],
                ],
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 3,
                        'max' => 50,
                    ]
                ],
            ],
            'allow_empty' => true,
            'continue_if_empty' => true,
        ],
    ],
];