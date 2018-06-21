// 提示框 
function $alert(content){
    $mask = $('<div class="alert-mask animated fadeIn"><span>' + content + '</span></div>');
    $("body").append($mask);

    var timer = setTimeout(function(){
        $(".alert-mask").removeClass("fadeIn").addClass("fadeOut");
        clearTimeout(timer);
        timer = null;
        
        var timer = setTimeout(function(){
            $(".alert-mask").remove();
            clearTimeout(timer);
            timer = null;
        },800);         
    },1000)
} 


// 加载中loading 显示 
function showLoading(){
    $loading = $('<div class="loading-mask animated fadeIn"><div class="loading"><span></span><span></span><span></span><span></span><span></span></div></div>');
    $("body").append($loading).css("overflow" , "hidden");
}


// 加载中loading 隐藏 
function hideLoading(){
    $(".loading-mask").addClass('fadeOut');
    var timer = setTimeout(function(){
        $(".loading-mask").remove();
        $("body").css("overflow" , "auto"); 
        clearTimeout(timer);
        timer = null;  
    },1000);
}


// $ajax 二次封装 
function $ajaxCustom(_url, _data, _succ) {
    $.ajax({
        url: _url,
        type: "POST",
        data: _data,
        dataType: "json",
        success: _succ,
        error: function(xhr) {
            if(422 == xhr.status){
                var resp = JSON.parse(xhr.responseText);
                $alert(resp.message);
            }else{
                // $alert("系统错误！");
            }
            return false;
        }
    });
    return false;
}

//token过期处理
function refreshTocken(callback){
    var storage = window.localStorage; 
    var token = storage.token;
    var url = config.api.base + config.api.refreshTocken;
    $ajaxCustom(url, {_tk : token}, function(res){
        if(res.code == 0){
            storage.token = res.data.token;
            storage.expire = res.data.expire;
            callback();
        }else if(res.code == 30004){
            storage.removeItem("token");
            window.location.href = './login.html';
        }else{
            $alert(res.message);
        }
    });
}

function getQueryString(name) {
    var reg = new RegExp('(^|&)' + name + '=([^&]*)(&|$)', 'i');
    var r = window.location.search.substr(1).match(reg);
    if (r != null) {
        return unescape(r[2]);
    }
    return null;
}



function  sinaAjax( code, callback ){
    $.ajax({
        url:"http://hq.sinajs.cn/list=" + code,
        dataType: "script",
        cache: "false",
        type: "GET",
        success: function(){
            var res = {};
            res.state = true;
            res.data = new Array();
            var codeArray = code.split(",");
            for(var key in codeArray){
                var codeInfo = eval( "hq_str_" + codeArray[key] );
                 codeInfo = codeInfo.split(",");
                var dataObj = {};
                dataObj.code = codeArray[key].slice(2);
                dataObj.prod_name = codeInfo[0];
                dataObj.last_px = (parseFloat( codeInfo[3] )).toFixed(2);
                dataObj.open_px = (parseFloat( codeInfo[1] )).toFixed(2);
                dataObj.preclose_px = (parseFloat( codeInfo[2] )).toFixed(2);
                dataObj.high_px = (parseFloat( codeInfo[4] )).toFixed(2);
                dataObj.low_px = (parseFloat( codeInfo[5] )).toFixed(2);
                dataObj.px_change = (codeInfo[3] - codeInfo[2] ).toFixed(2);
                dataObj.px_change_rate = ( (codeInfo[3] - codeInfo[2] ) / codeInfo[2] * 100 ).toFixed(2);
                dataObj.buy_px = (parseFloat( codeInfo[6] )).toFixed(2);
                dataObj.sell_px = (parseFloat( codeInfo[7] )).toFixed(2);
                dataObj.business_amount = (parseFloat( codeInfo[8] )).toFixed(2);
                dataObj.business_balance = (parseFloat( codeInfo[9] )).toFixed(2);
                dataObj.amplitude = (parseFloat( codeInfo[3] )).toFixed(2);
                res.data.push( dataObj );
                
                callback( res );

            }
        }
    });
}















