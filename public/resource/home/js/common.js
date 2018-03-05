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