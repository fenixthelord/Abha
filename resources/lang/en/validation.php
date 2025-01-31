<?php


return [
    'custom' => [
        'department_uuid' => [
            'required' => 'The department UUID field is required.',
            'exists' => 'The selected department UUID is invalid.',
        ],
        'name' => [
            'required' => 'The name field is required.',
        ],
        'name.en' => [
            'required' => 'The English name field is required.',
            'string' => 'The English name must be a string.',
            'min' => 'The English name must be at least :min characters.',
            'max' => 'The English name may not exceed :max characters.',
            'unique' => 'This English name already exists in this department.',
        ],
        'name.ar' => [
            'required' => 'The Arabic name field is required.',
            'string' => 'The Arabic name must be a string.',
            'min' => 'The Arabic name must be at least :min characters.',
            'max' => 'The Arabic name may not exceed :max characters.',
            'unique' => 'This Arabic name already exists in this department.',
        ],
        'chields' => [
            'required' => 'The children field is required.',
        ],
        'uuid' => [
            'required' => 'The UUID field is required.',
            'exists' => 'The selected UUID is invalid or has been deleted.',
        ]
    ],

    'attributes' => [
        'department_uuid' => 'Department UUID',
        'name.en' => 'English Name',
        'name.ar' => 'Arabic Name',
        'chields' => 'Children',
        'uuid' => 'UUID'
    ],
];
