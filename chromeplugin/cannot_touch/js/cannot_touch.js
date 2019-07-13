function getRandPos(min,max){

    var randPos=parseInt(-min+Math.random()*(max-min));

    while(Math.abs(randPos)<30){
        randPos=parseInt(min+Math.random()*(max-min));
    }
    return randPos;
}

$("#su").bind("mouseover",function(e){

    var randPos=getRandPos(-100,100);

    

    var x=e.pageX+randPos;
    var y=e.pageY+randPos;

    if(x>1000 || x <100) x=randPos;

    if(y>700 || y <100) y=randPos;


    console.log(e.pageX,e.pageY,randPos);
    $(this).css({"position":"fixed","left":x+"px","top":y+"px"});
    $(this).val("你是个SB!");
});

