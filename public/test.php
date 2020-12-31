<?php

$fh = fopen("/proc/meminfo", "r");
$content = '';

while($line = fgets($fh)){
    $content .= $line;
}

preg_match_all('/(\d+)/', $content, $matches);

$used = round(($matches[0][6] / $matches[0][0]), 2);

echo $used . "<br><br>";

echo $content;