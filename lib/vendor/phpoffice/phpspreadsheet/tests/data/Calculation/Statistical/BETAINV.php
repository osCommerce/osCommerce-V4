<?php

return [
    [
        1.862243320728,
        0.52, 3, 4, 1, 3,
    ],
    [
        2.164759759129,
        0.3, 7.5, 9, 1, 4,
    ],
    [
        2.164759759129,
        0.3, 7.5, 9, 4, 1,
    ],
    [
        7.761240188783,
        0.75, 8, 9, 5, 10,
    ],
    [
        2.0,
        0.685470581055, 8, 10, 1, 3,
    ],
    [
        0.303225844664,
        0.2, 4, 5, 0, 1,
    ],
    [
        0.303225844664,
        0.2, 4, 5, null, null,
    ],
    [
        '#VALUE!',
        'NAN', 4, 5, 0, 1,
    ],
    [
        '#VALUE!',
        0.2, 'NAN', 5, 0, 1,
    ],
    [
        '#VALUE!',
        0.2, 4, 'NAN', 0, 1,
    ],
    [
        '#VALUE!',
        0.2, 4, 5, 'NAN', 1,
    ],
    [
        '#VALUE!',
        0.2, 4, 5, 0, 'NAN',
    ],
    'alpha < 0' => [
        '#NUM!',
        0.2, -4, 5, 0, 1,
    ],
    'alpha = 0' => [
        '#NUM!',
        0.2, 0, 5, 0, 1,
    ],
    'beta < 0' => [
        '#NUM!',
        0.2, 4, -5, 0, 1,
    ],
    'beta = 0' => [
        '#NUM!',
        0.2, 4, 0, 0, 1,
    ],
    'Probability < 0' => [
        '#NUM!',
        -0.5, 4, 5, 1, 3,
    ],
    'Probability = 0' => [
        '#NUM!',
        0.0, 4, 5, 1, 3,
    ],
    'Probability > 1' => [
        '#NUM!',
        1.5, 4, 5, 1, 3,
    ],
    'Min = Max' => [
        '#NUM!',
        1, 4, 5, 1, 1,
    ],
];