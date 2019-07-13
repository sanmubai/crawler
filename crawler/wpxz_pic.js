/**
 * Created by bb on 2017/7/17.
 */

var cheerio=require('cheerio');
var get=require('./crawler');
var fs=require('fs');
var URL= require('url');
var iconv = require('iconv-lite');
const readline = require('readline');
const path=require('path');

const config=JSON.parse(fs.readFileSync("./config.json"));

var baseDir=config.saveBaseDir;
var folder="wpxz";
var fid=90;
var endPage=1000;
var beginPage=0;


if(!fs.existsSync(baseDir+'pics/'+folder)){
    fs.mkdirSync(baseDir+'pics/'+folder,0o777);
}

if(!fs.existsSync('./txt/'+folder)){
    fs.mkdirSync('./txt/'+folder,0o777);
}

var i=beginPage;


var timer=function () {

    i++;

    if(i>endPage){

        console.log('timer finished : page='+i);

        return;
    }

    var page='page'+i;

    if(!fs.existsSync('./txt/'+folder+'/'+page+'.txt')){
        getText(i);

        i--;

        setTimeout(function () {
            timer();
        },5000);

        return;
    }

    const rl = readline.createInterface({
        input: fs.createReadStream('./txt/'+folder+'/'+page+'.txt')
    });

    var x=0;
    rl.on('line', (line) => {

        console.log(`文件的单行内容：${line}`);

        var info=line.split('|||');
        var text=info[0];
        var href=info[1];

        var host=config.host+href;
        var url=URL.parse(host);
        var hostname=url.hostname;
        var path=host.substr(host.indexOf(config.hostPath));

        var cookie=config.cookie;

        var options={
            hostname:hostname,
            port:80,
            path:path,
            method:'GET',
            headers:{
                "Accept":"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
                "Accept-Encoding":"gzip, deflate",
                "Accept-Language":"zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4",
                "Cache-Control":"no-cache",
                "Connection":"keep-alive",
                "Cookie":cookie,
                "Host":hostname,
                "Pragma":"no-cache",
                "Upgrade-Insecure-Requests":"1",
                "User-Agent":"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3141.7 Safari/537.36"
            }
        };

        setTimeout(function () {

            var dir1='./pics/'+folder+'/'+page;

            var extDir=text.replace(/[\[\]【】\/\\]/g,'');

            var picTxt=dir1+'/'+extDir+'/pic.txt';

            if(!fs.existsSync(picTxt)){
                get(options,[page,text],'binary',getPicCb);
            }else{
                getPicArrFromTxt(picTxt);
            }


        },5000*x);

        x++;

    });

    rl.on('close',()=>{

        console.log('page end :'+page);

        setTimeout(function () {
            timer();
        },5000*x);

    });

};

var getPicCb=function (args,headers,data) {
    // console.log('==>header \n', headers);

    var data=iconv.decode(data,'gbk');

    var $=cheerio.load(data);

    var html=$("#postlist ignore_js_op img");

    // console.log(html);

    var str='';
    var picArr={};
    html.each(function (index,element) {

        var aid=$(this).attr('aid');
        var src=$(this).attr('file');

        str+=aid + "|||" +src+"\n";

        picArr[aid]=src;
    });


    var dir1=baseDir+'pics/'+folder+'/'+args[0];

    if(!fs.existsSync(dir1)){
        fs.mkdirSync(dir1,0o777);
    }

    var extDir=args[1].replace(/[\[\]【】\/\\]/g,'');

    // var extDir=args[1].substr(0,args[1].indexOf('['));
    //
    // if(!extDir){
    //
    //     var extDir=args[1].substr(0,args[1].indexOf('【'));
    //
    //     if(!extDir){
    //         var extDir=(new Date()).getTime();
    //     }
    // }

    var dir=dir1+'/'+extDir;

    if(!fs.existsSync(dir)){
        fs.mkdirSync(dir,0o777);
    }


    if(!fs.existsSync(dir)){

        dir=dir1+'/'+ (new Date()).getTime();

        if(!fs.existsSync(dir)){
            fs.mkdirSync(dir,0o777);
        }

        if(!fs.existsSync(dir)){
            return;
        }
    }

    var infoSrc=dir+'/info.html';
    var picSrc=dir+'/pic.txt';

    if(!fs.existsSync(infoSrc)){
        fs.writeFile(infoSrc,data,function (err) {

            if(err){
                console.log(err);
                return;
            }

            console.log(dir+'/info.html save ok');
        });
    }

    if(!fs.existsSync(picSrc)){
        fs.writeFile(picSrc,str,function (err) {

            if(err){
                console.log(err);
                return;
            }


            console.log(dir+'/pic.txt save ok');
        });
    }


    //download pics

    downPicFromPicArr(dir,picArr);



};
var savePicCb=function (args,headers,data) {

    fs.writeFile(args,data,function (err) {
        if(err){
            console.log(err);
            return;
        }

        console.log(args+' pic save ok');
    })
};

var downPicFromPicArr=function (dir,picArr) {
    for(var i in picArr){
        var imgSrc=dir+'/'+i+'.jpg'
        if(fs.existsSync(imgSrc)){
            continue;
        }
        get(config.host+picArr[i],imgSrc,'binary',savePicCb);
    }
}

var getText=function (page) {
    var host=config.host+'forum-46-'+page+'.html';
    var url=URL.parse(host);
    var hostname=url.hostname;
    var path=host.substr(host.indexOf('/forum-46'));

    var cookie=config.cookie;

    var options={
        hostname:hostname,
        port:80,
        path:path,
        method:'GET',
        headers:{
            "Accept":"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
            "Accept-Encoding":"gzip, deflate",
            "Accept-Language":"zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4",
            "Cache-Control":"no-cache",
            "Connection":"keep-alive",
            "Cookie":cookie,
            "Host":hostname,
            "Pragma":"no-cache",
            "Upgrade-Insecure-Requests":"1",
            "User-Agent":"Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/61.0.3141.7 Safari/537.36"
        }
    };

    get(options,page,'binary',getTextCb);
};

var getTextCb=function(args,headers,data) {
    // console.log('==>header \n', headers);

    var data=iconv.decode(data,'gbk');

    var $=cheerio.load(data);

    var html=$("#ct th a.s");

    // console.log(html);

    var str='';
    html.each(function (index,element) {

        var text=$(this).text();
        var href=$(this).attr('href');

        str+=text + "|||" +href+"\n";
    });

    var file_src='./txt/'+folder+'/page'+args+'.txt';
    fs.writeFile(file_src,str,function (err) {

        if(err){
            console.log(file_src,err);

            return;
        }
        console.log(file_src+' save ok');
    })

};

var getPicArrFromTxt=function (src) {

    var dir=path.dirname(src);

    const rl = readline.createInterface({
        input: fs.createReadStream(src)
    });

    var picArr=[];

    rl.on('line',(line)=>{
        console.log(`文件的单行内容：${line}`);

        var info=line.split('|||');
        var text=info[0];
        var href=info[1];

        picArr[text]=href;
    });

    rl.on('close',()=>{
        downPicFromPicArr(dir,picArr);
    });

};


timer();


