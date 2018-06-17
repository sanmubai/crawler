/**
 * Created by bb on 2017/7/17.
 */

var cheerio=require('cheerio');
var get=require('./crawler');
var fs=require('fs');
var URL= require('url');
var iconv = require('iconv-lite');

const config=JSON.parse(fs.readFileSync("./config.json"));

var folder="windRiver";
var endPage=6406;
var beginPage=0;

var timerInner=100;


var hostname=config.hostname;
var cookie=config.cookie;
var refer=config.refer;
var userAgent=config.userAgent;
var baseIndex=config.baseIndex;
var prePath=config.prePath;

if(!fs.existsSync('./'+folder)){
    fs.mkdirSync('./'+folder,0o777);
}

function PrefixInteger(num, n) {
    return (Array(n).join(0) + num).slice(-n);
}

var logErr=function (log) {
    // body...
    fs.appendFileSync('error.log', log+"\n");
}

var savePicCb=function (args,headers,data) {

    if(args==="error"){
        console.log("error",data,headers);
        logErr(data);
        return;
    }

    fs.writeFile(args,data,function (err) {
        if(err){
            console.log(err);
            return;
        }

        console.log(args+' save ok');
    })
};


function getTs(page) {
    var fileName="F0CZNK"+page+".ts";

    if(fs.existsSync("./windRiver/"+fileName)) return "exist";

    var path=prePath+fileName;
    var options={
        hostname:hostname,
        port:80,
        path:path,
        method:'GET',
        headers:{

            "Accept": "*/*",
            "Accept-Encoding": "gzip, deflate",
            "Accept-Language": "zh-CN,zh;q=0.9,en;q=0.8",
            "Cache-Control": "no-cache",
            "Connection": "keep-alive",
            "Cookie":cookie,
            "Host":hostname,
            "Pragma": "no-cache",
            "Referer": refer,
            "User-Agent": userAgent

        }
    };

    get(options,"./"+folder+"/"+fileName,'binary',savePicCb);

}

var i=beginPage;
var timer=function () {

    i++;

    if(i>endPage){

        console.log('timer finished : page='+i);

        return;
    }

    if(i<1000) var formatI=PrefixInteger(i,3);
    else var formatI=i;

    var exist=getTs(baseIndex+formatI);

    if(exist==="exist"){
        timer();
        return;
    }

    setTimeout(function () {
        timer();
    },timerInner);
};

timer();




