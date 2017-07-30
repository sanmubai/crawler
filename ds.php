<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2017/7/16
 * Time: 20:40
 */

/*
 $str='';
for($i=1;$i<86;$i++){

    $content=file_get_contents('./page'.$i.'.txt');

    $str.=$content;

}

file_put_contents('srysj.txt',$str);

*/

$data=file_get_contents("./page1.html");

//$data=iconv("UTF-8","GBK//IGNORE",$data);

echo $data;



