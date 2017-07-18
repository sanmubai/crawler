<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2017/7/16
 * Time: 20:40
 */
$output=file_get_contents('./test.html');

$contents = mb_convert_encoding($output, 'utf-8', 'GBK,UTF-8,ASCII');

echo $contents;



