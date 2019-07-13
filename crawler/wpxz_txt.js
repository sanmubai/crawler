/**
 * Created by bb on 2017/7/17.
 */

var cheerio=require('cheerio');
var get=require('./crawler');
var fs=require('fs');
var URL= require('url');
var iconv = require('iconv-lite');

const config=JSON.parse(fs.readFileSync("./config.json"));

var folder="wpxz";
var endPage=1000;
var beginPage=0;


if(!fs.existsSync('./pics/'+folder)){
    fs.mkdirSync('./pics/'+folder,0o777);
}

if(!fs.existsSync('./txt/'+folder)){
    fs.mkdirSync('./txt/'+folder,0o777);
}


var getTextCb=function (args,headers,data) {
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



function getText(page) {
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

}

var i=beginPage;
var timer=function () {

    i++;

    if(i>endPage){

        console.log('timer finished : page='+i);

        return;
    }

    var time=10;
    if(!fs.existsSync('./txt/'+folder+'/page'+i+'.txt')){
        getText(i);
        time=5000;
    }

    setTimeout(function () {
        timer();
    },time);


};

timer();




