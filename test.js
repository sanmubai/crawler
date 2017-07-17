/**
 * Created by bb on 2017/7/17.
 */

var cheerio=require('cheerio');
var get=require('./crawler');
var fs=require('fs');

var options={
    hostname:'blog.sanmubai.com',
    port:80,
    path:'/',
    method:'GET',
    headers:{
        "Accept":"text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8",
        "Accept-Encoding":"gzip, deflate",
        "Accept-Language":"zh-CN,zh;q=0.8,en;q=0.6,ja;q=0.4",
        "Cache-Control":"no-cache",
        "Connection":"keep-alive",
        "Cookie":"",
        "Host":"blog.sanmubai.com",
        "Pragma":"no-cache",
        "Upgrade-Insecure-Requests":"1",
        "User-Agent":"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36"
    }
};


var cb=function (args,headers,data) {
    // console.log('==>header \n', headers);

    var $=cheerio.load(data);

    console.log($('h1').text());
};

get(options,null,null,cb);





var cb2=function (args,headers,data) {

    fs.writeFile('baidu.png',data,function () {
        console.log('save ok');
    })
};


get("http://www.baidu.com/img/bd_logo1.png",null,'binary',cb2);
