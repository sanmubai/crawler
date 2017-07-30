<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2017/7/30
 * Time: 11:05
 */

$base='assets/pics';

dirCheck($base);


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
