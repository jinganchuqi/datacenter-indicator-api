<?php

return [
    'product' => [
        // 根据展期窗口 [-3,5] 逾期前3天——逾期后5天 设置展期状态
        'rollover_window_day' => [
            'start' => \Hyperf\Support\env('ROLLOVER_WINDOW_START_DAY', -3),
            'end' => \Hyperf\Support\env('ROLLOVER_WINDOW_END_DAY', 5),
        ],
    ],
];
