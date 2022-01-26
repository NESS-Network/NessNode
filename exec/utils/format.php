<?php

function formatPrint(array $format=[],string $text = '') {
    $codes=[
      'b'=>1, 'i'=>3, 'u'=>4, 's'=>9,
      'black'=>30, 'red'=>31, 'green'=>32, 'yellow'=>33,'blue'=>34, 'magenta'=>35, 'cyan'=>36, 'white'=>37,
      'blackbg'=>40, 'redbg'=>41, 'greenbg'=>42, 'yellowbg'=>44,'bluebg'=>44, 'magentabg'=>45, 'cyanbg'=>46, 'lightgreybg'=>47
    ];

    $formatMap = array_map(function ($v) use ($codes) { return $codes[$v]; }, $format);

    echo "\e[".implode(';',$formatMap).'m'.$text."\e[0m";
}

function formatPrintLn(array $format=[], string $text='') {
    formatPrint($format, $text); echo "\r\n";
}
