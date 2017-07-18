/**
 * Created by bb on 2017/7/17.
 */

var cheerio=require('cheerio');
var get=require('./crawler');
var fs=require('fs');
var URL= require('url');
var iconv = require('iconv-lite');

var host='/home.php?mod=space&uid=45874&do=thread&view=me&order=dateline&from=space&page=1';
var url=URL.parse(host);
var hostname=url.hostname;
var path=url.pathname;

var cookie='k53Y_2132_saltkey=Qst18KK1; k53Y_2132_lastvisit=1500389762; k53Y_2132_sendmail=1; safedog-flow-item=6A82BB32C42A6D19FE8042974D569334; k53Y_2132_ulastactivity=da6d2UupMxqSXG8drwTyeuu3aFyh8x2r744cxjSsC76Rbv5FzQCT; k53Y_2132_auth=4943BZwrhdB1N0B0IpVE2lQVXF2DWoOFEYjyfS0%2Bc4ShGHx806U%2FZ%2FIJAPv4RWPtDN%2BgUHWZv3ZCVT8UCZOwkFe%2BUw; k53Y_2132_lastcheckfeed=29612%7C1500393378; k53Y_2132_checkfollow=1; k53Y_2132_nofavfid=1; k53Y_2132_onlineusernum=6596; k53Y_2132_checkpm=1; k53Y_2132_lip=219.147.88.207%2C1500393384; k53Y_2132_sid=Z36ccP; k53Y_2132_lastact=1500393399%09misc.php%09patch';

var options={
    hostname:hostname,
    port:80,
    path:"/home.php?mod=space&uid=45874&do=thread&view=me&from=space",
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


var cb=function (args,headers,data) {
    // console.log('==>header \n', headers);

    var data=iconv.decode(data,'gbk');

    var $=cheerio.load(data);

    var html=$("#ct th a");

    var str='';
    html.each(function (index,element) {

        var text=$(this).text();
        var href=$(this).attr('href');

        str+=text + "|||" +href+"\n";
    });



    fs.writeFile('test.txt',str,function () {
        console.log('save ok');
    })




};

get(options,null,'binary',cb);





var cb2=function (args,headers,data) {

    fs.writeFile('baidu.png',data,function () {
        console.log('save ok');
    })
};


// get("http://www.baidu.com/img/bd_logo1.png",null,'binary',null,cb2);
