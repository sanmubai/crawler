<?php
/**
 * Created by PhpStorm.
 * User: bin
 * Date: 2017/7/22
 * Time: 18:35
 */


$arg=isset($_GET['arg'])?$_GET['arg']:'';

if($arg){
    $dir=$arg;


    if(!is_dir($dir) && !is_dir($dir.' ')){
        echo file_get_contents($dir);

        die;
    }else{

        if(!is_dir($dir)){
            $dir=$dir.' ';
            $arg=$dir;
        }

        $dir=scandir($dir);


    }
}else{
    $dir=scandir('./');
}


$html=getHtmlFromDir($dir,$arg);

echo $html;



function getHtmlFromDir($dir,$arg){
    if(!$dir){
        echo "invalid dir";
        return false;
    }

    if(!$arg){
        $arg='.';
    }

    $html='<style type="text/css">a{font-size: 35px;}</style>';
    foreach ($dir as $v){
        if($v=='.' || $v=='..'){
            continue;
        }

        if(substr($v,0,1)=='.') {
            continue;
        }

        if(substr($v,count($v)-4)=='jpg'){
            $html.='<a href="index.php?arg='.$arg.'/'.$v.'"><img src="'.$arg.'/'.$v.'"/></a>'.'<br>';
        }else{
            $html.='<a href="index.php?arg='.$arg.'/'.$v.'">'.$v.'</a>'.'<br>';
        }

    }

    return $html;
}