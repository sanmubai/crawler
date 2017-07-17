/**
 * Created by bb on 2017/7/17.
 */

var http=require('http');
var zlib=require('zlib');

module.exports=function (options,args,callback) {
    var req=http.get(options, (res) => {
        // 对响应进行处理
        var encoding = res.headers['content-encoding'];
        var chunks=[];

        if( encoding === 'undefined'){
            res.setEncoding('utf-8');
        }

        res.on('data', function (chunk) {
            chunks.push(chunk);
        });


        res.on('end',()=>{

            var data;
            var buffer=Buffer.concat(chunks);

            if (encoding == 'gzip') {

                zlib.gunzip(buffer, function (err, decoded) {

                    data = decoded.toString();
                    callback( err, args, res.headers, data);
                });
            } else if (encoding == 'deflate') {
                zlib.inflate(buffer, function (err, decoded) {
                    data = decoded.toString();
                    callback( err, args, res.headers, data);
                });
            } else {
                data = buffer.toString();
                callback( null, args, res.headers, data);
            }
        });

    });

    req.on('error',function(err){
        console.error(err);
    });
    req.end();
};