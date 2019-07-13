<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2017/7/30
 * Time: 11:05
 */

$base='windRiver';

rename1($base);

function rename1($dir){
if(!is_dir($dir)) {
        return ;
    }

    $subDirs=scandir($dir);

    foreach ($subDirs as $v){

        if($v=='.' || $v=='..') continue;

        $name=basename($v,".ts");

        if(strlen($name)<4) {
            // echo $name."\n";
            echo $dir."/".$v."  ".$dir."/0".$v."\n";
            rename($dir."/".$v,$dir."/0".$v);
        }
        

    }
}


function dirCheck($dir){

    if(!is_dir($dir)) {
        return ;
    }

    $newDir=$dir;

    $newDir=preg_replace('/[ +.,()【】?~]/','',$newDir);

    if($newDir!=$dir){
        rename($dir,$newDir);
    }

    $subDirs=scandir($newDir);

    foreach ($subDirs as $v){

        if($v=='.' || $v=='..') continue;

        if(is_dir($newDir.'/'.$v)) {
            dirCheck($newDir.'/'.$v);
        }

    }
}
