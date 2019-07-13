<?php
$dir=scandir(".");
foreach ($dir as $v) {
	if($v=="." || $v==".."){
		continue;
	}
	if(is_dir($v)){
		continue;
	}
	$arr=preg_split("/[_\.]/",  $v);
	if(count($arr)<=2){
		continue;	
	}
	$newDir=$arr[1];
	if(!is_dir($newDir)){
		mkdir($newDir);	
	}
	rename($v, $newDir."/".$v);
}
