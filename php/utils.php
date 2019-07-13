<?php
/**
 * Created by PhpStorm.
 * User: bbdc
 * Date: 15/12/29
 * Time: 上午11:57
 */

require_once "phpquery/phpQuery/phpQuery.php";
require_once './PHPExcel_1.8.0_doc/Classes/PHPExcel.php';
require_once './getID3-1.9.11/getid3/getid3.php';

/**
 * Curl版本
 * 使用方法：
 * $post_string = "app=request&version=beta";
 * request_by_curl('http://www.qianyunlai.com/restServer.php', $post_string);
 * $header=array('X-FORWARDED-FOR:202.114.63.251', 'CLIENT-IP:202.114.63.251')
 * $useragent=Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50
 * $referer="http://www.xx.com"
 */

//$header=array('CLIENT-IP:202.114.63.251','X-FORWARDED-FOR:202.114.63.251', 'Accept-Encoding:gzip, deflate, sdch','Accept-Language:zh-CN,zh;q=0.8,en;q=0.6');
//$useragent='Mozilla/5.0 (Windows; U; Windows NT 6.1; en-us) AppleWebKit/534.50 (KHTML, like Gecko) Version/5.1 Safari/534.50';
//$referer="http://dict.cn";
//$post_string="";
//$remote_server="http://audio.dict.cn/";

function requestByCurl($remote_server, $post_string,$header,$useragent,$referer)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $remote_server);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_REFERER, $referer);
	curl_setopt($ch, CURLOPT_ENCODING ,'gzip'); //加入gzip解析
//	curl_setopt($ch, CURLOPT_HEADER, 1);
	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

function dbug($data,$isContinue=0,$isall=0){
	echo "<pre>";

	if($isall){
		var_dump($data);
	}else{
		print_r($data);
	}

	echo "</pre>";

	if(!$isContinue) die;
}

/*
 * $path 生成的文件要存放的地址
 * $fileContent 文件的实体
 */
function generateFile($path,$fileContent){
	$tp = @fopen($path,"w");
	fwrite($tp, $fileContent);
	fclose($tp);
}

function addFileToZip($path, $zip,$subpath) {

	$filenames=scandir($path);

	foreach($filenames as $key=>$val){
		if($val != "." && $val != ".." && $val !='.DS_Store'){
			$zip->addFile($path . "/" . $val,$subpath."/".$val);
		}
	}

	@closedir($path);
}

/*
 * $dir='/Users/bbdc/Downloads/mp3_new/';
 * $dir_zip='/Users/bbdc/Downloads/mp3_new_zip/';
 */

function generateZip($dir,$dir_zip){
	$filenames=scandir($dir);

	$zip = new ZipArchive();

	foreach($filenames as $key=>$val){
		if($val != "." && $val != ".." && $val !='.DS_Store'){
			if ($zip->open($dir_zip.$val.'.zip', ZIPARCHIVE::CREATE) === TRUE) {
				addFileToZip($dir.$val, $zip,$val); //调用方法，对要打包的根目录进行操作，并将ZipArchive的对象传递给方法
				$zip->close(); //关闭处理的zip文件
			}
		}
	}
}

/*
 * 解压文件夹
 */

function unZip($dir){
	$zip=new ZipArchive();

	if(is_dir($dir)){
		$files=scandir($dir);
		foreach($files as $k=>$v){
			if($v != "." && $v != ".." && $v !='.DS_Store'){

				if($zip->open($dir.'/'.$v)===TRUE){
					$zip->extractTo($dir);
					$zip->close();
				}
			}
		}
	}
}

/**对excel里的日期进行格式转化*/
function getData($val){
	$jd = GregorianToJD(1, 1, 1970);
	$gregorian = JDToGregorian($jd+intval($val)-25569);
	return $gregorian;/**显示格式为 “月/日/年” */
}

/*
 * $filePath = './第一批中考例句.xls';
 * $index=0; 默认为第一个工作表
 * $begin='A' 开始列
 * $end='G' 结束列
 */

function readExcel($filePath,$begin,$end,$index=0){
	$PHPExcel = new PHPExcel();

	/**默认用excel2007读取excel，若格式不对，则用之前的版本进行读取*/
	$PHPReader = new PHPExcel_Reader_Excel2007();
	if(!$PHPReader->canRead($filePath)){
		$PHPReader = new PHPExcel_Reader_Excel5();
		if(!$PHPReader->canRead($filePath)){
			echo 'no Excel';
			return ;
		}
	}

	$PHPExcel = $PHPReader->load($filePath);
	/**读取excel文件中的第一个工作表*/
	$currentSheet = $PHPExcel->getSheet($index);
	/**取得最大的列号*/
	$allColumn = $currentSheet->getHighestColumn();
	/**取得一共有多少行*/
	$allRow = $currentSheet->getHighestRow();

	$data=array();
	/**从第二行开始输出，因为excel表中第一行为列名*/
	for($currentRow = 1;$currentRow <= $allRow;$currentRow++){
		$temp=array();

		/**从第A列开始输出*/
		for($currentColumn= $begin;$currentColumn<= $end; $currentColumn++){
			$val = $currentSheet->getCellByColumnAndRow(ord($currentColumn) - 65,$currentRow)->getValue();/**ord()将字符转为十进制数*/

			$temp[]=addslashes($val);
//			$temp[]=addslashes(iconv('utf-8','gb2312', $val));
		}
		$data[]=$temp;
	}

	return $data;
}

/*
 * 复制文件到新文件夹
 * $path 新文件所在文件夹。
 */
function cpFile($orifile,$path,$filename){
	//判断文件夹是否存在，不存在－》创建。存在，直接复制$file到$path

	if(is_dir($path)){
		system('cp '.$orifile.' '.$path.'/'.$filename);
	}else{
		if(mkdir($path)){
			system('cp '.$orifile.' '.$path.'/'.$filename);
		}
	}
}

//把unicode字符串拆分为单字符的数组
function str_split_unicode($str, $l = 0) {
	if ($l > 0) {
		$ret = array();
		$len = mb_strlen($str, "UTF-8");
		for ($i = 0; $i < $len; $i += $l) {
			$ret[] = mb_substr($str, $i, $l, "UTF-8");
		}
		return $ret;
	}
	return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
}

//获取字符的unicode编码，普通字符返回ascii码，中文返回unicode

function get_unicode($letter){
	$c=ord($letter);
	if($c<=127&&$c>=0){
		return $c;
	} else{
		$letter = iconv('UTF-8', 'UCS-2', $letter);

		return '\u'.sprintf("%02s", base_convert(ord($letter[0]), 10, 16)).sprintf("%02s", base_convert(ord($letter[1]), 10, 16));

	}

}

//将内容进行UNICODE编码，编码后的内容格式：YOKA\u738b （原始：YOKA王）
function unicode_encode($name)
{


	$name = iconv('UTF-8', 'UCS-2', $name);
	$len = strlen($name);
	$str = '';
	for ($i = 0; $i < $len - 1; $i = $i + 2)
	{
		$c = $name[$i];
		$c2 = $name[$i + 1];
		if (ord($c) > 0)
		{    // 两个字节的文字
			$str .= '\u'.sprintf("%02d", base_convert(ord($c), 10, 16)).sprintf("%02d", base_convert(ord($c2), 10, 16));
		}
		else
		{
			$str .= $c2;
		}
	}
	return $str;
}

// 将UNICODE编码后的内容进行解码，编码后的内容格式：YOKA\u738b （原始：YOKA王）
function unicode_decode($name)
{
	// 转换编码，将Unicode编码转换成可以浏览的utf-8编码
	$pattern = '/([\w]+)|(\\\u([\w]{4}))/i';
	preg_match_all($pattern, $name, $matches);
	if (!empty($matches))
	{
		$name = '';
		for ($j = 0; $j < count($matches[0]); $j++)
		{
			$str = $matches[0][$j];
			if (strpos($str, '\\u') === 0)
			{
				$code = base_convert(substr($str, 2, 2), 16, 10);
				$code2 = base_convert(substr($str, 4), 16, 10);
				$c = chr($code).chr($code2);
				$c = iconv('UCS-2', 'UTF-8', $c);
				$name .= $c;
			}
			else
			{
				$name .= $str;
			}
		}
	}
	return $name;
}

//将给定文件夹的文件名全命名为小写
function rename_to_low($path){

	$filenames=scandir($path);

	foreach($filenames as $key=>$val){
		if($val != "." && $val != ".." && $val !='.DS_Store'){
			if(is_dir($path."/".$val)){
				rename_to_low($path."/".$val);
			}

			rename($path."/".$val,$path."/".strtolower($val));
		}
	}
}

// 获取文件夹大小
function getDirSize($dir)
{
	$handle = opendir($dir);
	$sizeResult=0;
	while (false!==($FolderOrFile = readdir($handle)))
	{
		if($FolderOrFile != "." && $FolderOrFile != ".." && $FolderOrFile != ".DS_Store")
		{
			if(is_dir("$dir/$FolderOrFile"))
			{
				$sizeResult += getDirSize("$dir/$FolderOrFile");
			}
			else
			{
				$sizeResult += filesize("$dir/$FolderOrFile");
			}
		}
	}
	closedir($handle);
	return $sizeResult;
}

// 单位自动转换函数
function getRealSize($size)
{
	$kb = 1024;         // Kilobyte
	$mb = 1024 * $kb;   // Megabyte
	$gb = 1024 * $mb;   // Gigabyte
	$tb = 1024 * $gb;   // Terabyte

	if($size < $kb)
	{
		return $size." B";
	}
	else if($size < $mb)
	{
		return round($size/$kb,2)." KB";
	}
	else if($size < $gb)
	{
		return round($size/$mb,2)." MB";
	}
	else if($size < $tb)
	{
		return round($size/$gb,2)." GB";
	}
	else
	{
		return round($size/$tb,2)." TB";
	}
}

function generateXls($data,$name){

	header("Content-type:application/vnd.ms-excel");
	header("Content-Disposition:attachment;filename=".$name.".xls");
	echo  <<<eof
<html xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns="http://www.w3.org/TR/REC-html40">
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
 <html>
     <head>
        <meta http-equiv="Content-type" content="text/html;charset=UTF-8" />
         <style id="Classeur1_16681_Styles"></style>
     </head>
     <body>
         <div id="Classeur1_16681" align=center x:publishsource="Excel">
             <table x:str border=0 cellpadding=0 cellspacing=0 width=100% style="border-collapse: collapse">
eof;

	foreach ($data as $key => $value) {

		echo "<tr >";
	    echo "<td class=xl2216681 nowrap>id</td>";
	    foreach($data[0] as $k=>$v){
	        echo "<td class=xl2216681 nowrap>".$k."</td>";
	    }
	    echo "</tr>";

		# code...
		echo "<tr>";
		echo "<td class=xl2216681 nowrap>".($key+1)."</td>";
		foreach($value as $k=>$v){

			echo "<td class=xl2216681 nowrap>{$v}</td>";

		}

		echo '</tr>';

	}


	echo <<<eof
             </table>
         </div>
     </body>
 </html>
eof;

}

function getID3($file){
	$getID3 = new getID3();

	$info=$getID3->analyze($file);

	return $info;
}

//获取数组纬度

function array_depth($array) {
    if(!is_array($array)) return 0;
    $max_depth = 1;
    foreach ($array as $value) {
        if (is_array($value)) {
            $depth = array_depth($value) + 1;

            if ($depth > $max_depth) {
                $max_depth = $depth;
            }
        }
    }
    return $max_depth;
}


/*
 * 生成单元数的规则
 * $W 总词数
 * $Wnum 每关的单词数
 * $Unm 每单元的关数
 *
 * echo " 总词数 $W ， 总关卡数 $A ， 固定关卡数 ".($A-$Z)." ， 固定关卡词数 $X ， 浮动关卡数 $Z ， 浮动关卡词数 $Y ， 单元数 $U ";
 *
 * return Array('W'=>$W,
		'A'=>$A,
		'G'=>$A-$Z,固定关卡数
		'X'=>$X,
		'Z'=>$Z,
		'Y'=>$Y,
		'U'=>$U)
 */

function generateUnitNum($W,$Wnum){

	$A=0;$Z=0;$X=0;$Y=0;$U=0;

	$A=round($W/$Wnum);  //关卡数

	if($A<1) $A=1;

	$B=round($W/$A);  //关卡词数

//$X 固定关卡词数
//$Y 浮动关卡词数

	$X=$B;

	if($W-$B*$A>0) $Y=$X+1;
	if($W-$B*$A<0) $Y=$X-1;

	$Z=abs($W-$B*$A);   //浮动关卡个数


	return array(
		'W'=>$W,
		'A'=>$A,
		'G'=>$A-$Z,
		'X'=>$X,
		'Z'=>$Z,
		'Y'=>$Y
	);
}


/*
 * 把二维数组生成一个 table
 * 取二维数组的字断值为 table 的th
 * 自动加一列id
 *
 *
 * $data =array(
 *
 *      array("name"=>'xx','age'=>12),
 *      array("name"=>'xx','age'=>12)
 *
 * );
 */

function generateTable($data){
    echo "<meta charset='utf8'>";
    echo "<style type='text/css'>td{border:1px solid grey;margit:5px;padding:5px;}</style>";
    echo "<table>";

    echo "<tr >";
    echo "<td>id</td>";
    foreach($data[0] as $k=>$v){
        echo "<td>".$k."</td>";
    }
    echo "</tr>";

    foreach($data as $k=>$v){
        echo "<tr>";
        echo "<td>".($k+1)."</td>";
        foreach($v as $kk=>$vv){
            echo "<td>".$vv."</td>";
        }

        echo "</tr>";
    }
    echo "</table>";
}


