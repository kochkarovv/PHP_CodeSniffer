<?php

// Test case with misaligned arrows
$array1 = [
    'key1' =>  'value1',
    'longer_key' =>     'value2',
    'k'               => 'value3',
];

// Nested arrays with misalignment
$array2 = [
    'nested'      => [
        'a'   => 'b',
        'long_key'  =>   'c',
    ],
    'another_key'           => [
        'nested_key'    => 'value',
    ],
];

// Multi-level nesting
$array3 = [
    'third_level'=> [
        'a'         => [
            'x' => 'y',
            'longer'=>'z',
        ],
    ],
];

// Mixed indentation and multi-level nesting
$array4 = [
    'first'             => 'value1',
    'second'         => [
        'nested1'               => 'nestedvalue1',
        'longer_nested'         =>'nestedvalue2',
        'n'             => [
            'deepnested'    => 'deepvalue',
            'deeper'   =>'deepervalue',
        ],
    ],
    'third'=>'value3',
]; 