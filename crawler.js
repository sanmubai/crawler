/**
 * Created by bb on 2017/7/17.
 */

var http=require('http');
var zlib=require('zlib');

module.exports=function (options,args,dataType,callback) {
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

        res.on('end',()=> {

            var data;
            var buffer=Buffer.concat(chunks);

            if (encoding == 'gzip') {

                zlib.gunzip(buffer, function (err, decoded) {
                    if(err){
                        console.log(err);
                        return;
                    }


                    if(dataType!='binary')
                        data = decoded.toString();
                    else
                        data=decoded;

                    callback( args, res.headers, data);
                });
            } else if (encoding == 'deflate') {
                zlib.inflate(buffer, function (err, decoded) {
                    if(err){
                        console.log(err);
                        return;
                    }

                    if(dataType!='binary')
                        data = decoded.toString();
                    else
                        data=decoded;

                    callback( args, res.headers, data);
                });
            } else {

                if(dataType!='binary')
                    data = buffer.toString();
                else
                    data = buffer;

                callback( args, res.headers, data);
            }
        });

        res.on("error",(err)=>{
            // console.log("res_error:",err);
            callback('error',err,args);
        });

    });

    req.on('error',function(err){
        // console.log("req_error:",err);
        callback('error',err,args);
    });
    req.end();
};