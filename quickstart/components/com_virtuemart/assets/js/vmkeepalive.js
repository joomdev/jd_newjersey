/** @author Max Milbers, copyright by the author and VirtueMart team , license MIT */
var vmKeepAlive = function() {
    jQuery(function($){
        var lastUpd = 0,kAlive = 0,minlps = 1,stopped = true;
        var sessMSec = 60 * 1000 * parseFloat(sessMin);
        var interval = (sessMSec - 40000) * 0.99;
        console.log('keepAlive each '+interval/60000+' minutes and maxlps '+maxlps);
        var tKeepAlive = function($) {
            if(stopped){
                kAlive = 1;
                //console.log('Start keep alive, kAlive '+kAlive+' '+parseFloat(sessMin) * 0.01 * 60);
                var loop = setInterval(function(){
                    var newTime = new Date().getTime();
                    if(kAlive >= minlps && newTime - lastUpd > sessMSec * (parseFloat(maxlps) + 0.99) ){
                        //console.log('Stop keep alive '+kAlive);
                        stopped = true;
                        clearInterval(loop);
                    }else{
                        console.log('keep alive '+kAlive+' newTime '+((newTime-lastUpd)/60000)+' < '+(sessMin*(parseFloat(maxlps) + 0.99)));
                        kAlive++;
                        $.ajax({
                            url: vmAliveUrl,
                            cache: false
                        });
                    }
                }, interval); //mins * 60 * 1000
                stopped = false;
            }
        };
        lastUpd = new Date().getTime();
        tKeepAlive($);
        //Editors like tinyMCE unbind any event. Using binds like focusin/click, update keep alive using the tool bar
        $(document).on('keyup click','body', function(e){
            lastUpd = new Date().getTime();
            //console.log('keepAlive body ', e.type);
            if(stopped){
                $.ajax({
                    url: vmAliveUrl,
                    cache: false
                });
                tKeepAlive($);
            }
        });
    });
};
vmKeepAlive();
