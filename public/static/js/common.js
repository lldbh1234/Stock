function _ajaxPost(_url, _data, _func)
{
    $.ajax({
        type : "POST",
        url  : _url,
        data : _data,
        dataType : 'json',
        error: function(request) {
            layer.msg("服务器繁忙, 请联系管理员!");
        },
        success: _func
    });
}