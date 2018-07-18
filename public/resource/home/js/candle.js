/**
 * 判断当前时间是否在9:30-11:30, 13:00-15:00（交易时间）
 */
function isTradingTime(){
    var date = new Date();
    //判断是不是周末
    var dt=date.getDay();
    if(dt=='6'||dt=='7'){
        return false;
    }
    //判断当前时间是否在9:30-11:30, 13:00-15:00
    var h = date.getHours();
    var mi = date.getMinutes();
    var s = date.getSeconds();
    if(h < 10){
        h = "0" + h;
    }
    if(mi < 10){
        mi = "0"+ mi;
    }
    if(s < 10){
        s = "0" + s;
    }
    var curTime = h + ":" + mi + ":" + s;
//  console.log(curTime);
    if( curTime >= "09:30:00" && curTime <= "11:30:00" || curTime >= "13:00:00" && curTime <= "15:00:00" ){
        return true;
    }
    return false;
}
//date
Date.prototype.format = function (format) {
    var o = {
        "M+": this.getMonth() + 1, //month 
        "d+": this.getDate(), //day 
        "h+": this.getHours(), //hour 
        "m+": this.getMinutes(), //minute 
        "s+": this.getSeconds(), //second 
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter 
        "S": this.getMilliseconds() //millisecond 
    }

    if (/(y+)/.test(format)) {
        format = format.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    }

    for (var k in o) {
        if (new RegExp("(" + k + ")").test(format)) {
            format = format.replace(RegExp.$1, RegExp.$1.length == 1 ? o[k] : ("00" + o[k]).substr(("" + o[k]).length));
        }
    }
    return format;
};


var lineData = new Array(); // 保存请求回来的行情数据
var upColor = '#ff0200';
var downColor = '#008002';
var myChart = echarts.init(document.getElementById('chart'));
var fullCode = $("#guName").data("fullcode");


function splitData(rawData) {
    var categoryData = [];
    var values = [];
    var volumes = [];
    for (var i = 0; i < rawData.length; i++) {
        var _data = rawData[i].split(" ");
        values.push([_data[1], _data[1], _data[1]]);
        var _count = _data[2];
        var _count = "-";
        if( i != 0 ){
            _count = parseFloat ((_data[2] - rawData[i - 1].split(" ")[2])) / 10;
        }
        volumes.push([i, _count]);
    }
    for (var i = 0930; i <= 1130; i++) {

        var arr = (i < 1000 ? "0" + i + "" : "" + i + "").split(" ");

        var time1 = arr[0].substring(0, 2);

        var time2 = arr[0].substring(2, 4);

        if (parseInt(time2) <= 59) {

            categoryData.push(time1 + ":" + time2);

        }

    }

    for (var i = 1300; i <= 1500; i++) {

        var arr = i.toString().split(" ");

        var time1 = arr[0].substring(0, 2);

        var time2 = arr[0].substring(2, 4);

        if (parseInt(time2) <= 59) {

            categoryData.push(time1 + ":" + time2);

        }

    }


    for (var i = values.length; i < categoryData.length; i++) {

        values.push(["-", "-", "-", "-", "-"]);

        volumes.push([i, "-", "-"]);

    }

    return {
        categoryData: categoryData,
        values: values,
        volumes: volumes
    };
}

function calculateMA(dayCount, data) {
    var result = [];
    for (var i = 0, len = data.values.length; i < len; i++) {
        if (i < dayCount) {
            result.push('-');
            continue;
        }
        var sum = 0;
        for (var j = 0; j < dayCount; j++) {  
            sum += parseFloat( data.values[i - j][1] );
        }
        result.push(+(sum / dayCount).toFixed(3));
    }
    return result;
}

/**** 点击事件绑定 ****/
$(".koptions_nav").on("click", "p", function(){
    $(this).addClass("active").siblings(".active").removeClass("active");
    var type = $(this).find("a").data("type");
    if( type == 0 ){
        //加载分时图
        initMin();
    }else if( type == 6 ){
        //加载k线
        initDay( type );
    }else if( type == 7 ){
        initWeek();
    }else if( type == 8 ){
        initMonth();
    }
});

function initMin(){
    $.ajax({
        url:"http://web.ifzq.gtimg.cn/appstock/app/minute/query?_var=min_data_" + fullCode + "&code=" + fullCode + "&r=0.040982807166606294",
        dataType:"script",
        cache:"false",
        type:"get",
        success:function(a){
            var minData = eval("min_data_" + fullCode);
            var minDataFormat = splitData( minData.data[fullCode].data.data );

            drawAreaLine( minDataFormat );
        }
    });
}

function initDay(){
    $.ajax({
        url:"http://web.ifzq.gtimg.cn/appstock/app/kline/kline?_var=kline_day&param=" + fullCode + ",day,,,60,&r=0.6762027715074195",
        dataType:"script",
        cache:"false",
        type:"get",
        success:function(a){
            var dayData = kline_day.data[fullCode].day;
            var dayDataFormat = splieKNum(dayData);
            drawKchart( dayDataFormat );
        }
    });
}

function initWeek(){
    $.ajax({
        url:"http://web.ifzq.gtimg.cn/appstock/app/kline/kline?_var=kline_week&param=" + fullCode + ",week,,,60,&r=0.28570913281575394",
        dataType:"script",
        cache:"false",
        type:"get",
        success:function(a){
            var dayData = kline_week.data[fullCode].week;
            var dayDataFormat = splieKNum(dayData);
            drawKchart( dayDataFormat );
        }
    });
}

function initMonth(){
    $.ajax({
        url:"http://web.ifzq.gtimg.cn/appstock/app/kline/kline?_var=kline_month&param=" + fullCode + ",month,,,60,&r=0.6762027715074195",
        dataType:"script",
        cache:"false",
        type:"get",
        success:function(a){
            var dayData = kline_month.data[fullCode].month;
            var dayDataFormat = splieKNum(dayData);
            drawKchart( dayDataFormat );
        }
    });
}





function numberFormat(number){
    var b=1000;
    var c=10000;
    var d=100000000;
    number = parseFloat(number);
    if (number>=b && number<c){
        return Math.round(number/b, 2) + '千';
    }else if (number>=c && number<d){
        return Math.round(number/c, 2) + '万';
    }else {
        return Math.round(number/d, 2) + '亿';
    }
}

var freshInterval = null;
function refreshTimeLine(){
    if( !isTradingTime() ){
        return false;
    }
    if( $(".koptions_nav .active a").data("type") == 0 ){
        initMin();
    }

    $.ajax({
        url:"http://web.ifzq.gtimg.cn/appstock/app/kline/kline?_var=kline_day&param=" + fullCode + ",day,,,1,&r=0.6762027715074195",
        dataType:"script",
        cache:"false",
        type:"get",
        success:function(a){
            var dayData = kline_day.data[fullCode].qt[fullCode];
            console.log( dayData );
            var html = '<ul class="sell mui-col-xs-6 mui-row clear_fl">\
                            <li class=""><em>卖⑤</em><b class="red">' + dayData[27] + '</b><i>' + dayData[28] + '</i></li>\
                            <li class=""><em>卖④</em><b class="red">' + dayData[25] + '</b><i>' + dayData[26] + '</i></li>\
                            <li class=""><em>卖③</em><b class="red">' + dayData[23] + '</b><i>' + dayData[24] + '</i></li>\
                            <li class=""><em>卖②</em><b class="red">' + dayData[21] + '</b><i>' + dayData[22] + '</i></li>\
                            <li class=""><em>卖①</em><b class="red">' + dayData[19] + '</b><i>' + dayData[20] + '</i></li>\
                        </ul>\
                        <ul class="buy mui-col-xs-6 mui-row clear_fl">\
                            <li><em>买①</em><b class="red">' + dayData[9] + '</b><i>' + dayData[10] + '</i></li>\
                            <li><em>买②</em><b class="red">' + dayData[11] + '</b><i>' + dayData[12] + '</i></li>\
                            <li><em>买③</em><b class="red">' + dayData[13] + '</b><i>' + dayData[14] + '</i></li>\
                            <li><em>买④</em><b class="red">' + dayData[15] + '</b><i>' + dayData[16] + '</i></li>\
                            <li><em>买⑤</em><b class="red">' + dayData[17] + '</b><i>' + dayData[18] + '</i></li>\
                        </ul>';

                        console.log( html );
            $("#stock-price").html( html );

            var now_price = dayData[3];
            var className = "";
            if( dayData[31] >= 0 ){
                className = "red";
            }else{
                className = "green;"
            }
            $(".r_price").html(dayData[3]).removeClass("red green").addClass( className );
            var _html = '<span class="lf ' + className + '">' + dayData[31] + '</span>\
                        <span class="lf ' + className + '">' + dayData[32] + '%</span>';
            $(".r_rate").html( _html );
        }
    });
}
$(function(){
    // 页面加载完成就加载分时图
    initMin();
    /*** 没两秒请求数据， 更新页面 ****/
    freshInterval = setInterval(refreshTimeLine, 4000);
});

/*** init分时图 ****/
function initAreaLine(){
    if( !lineData[0] ){ //第一次加载
        var _url = areaLineUrl,
            _code = $("#guName").data("code"),
            _oData = {code:_code};
        $ajaxCustom(_url, _oData, function(res){
            if(res.state){ 
                var _data = res.data.trend;
                lineData[-1] = _data;
                lineData[0] = splitData(_data);
                lineData[-2] = {}; //存放trend_crc， min_time
                lineData[-2].trend_crc = res.data.trend_crc;
                lineData[-2].min_time = _data[_data.length - 1].min_time;
                drawAreaLine(lineData[0]);
            }else{
                $alert(res.info);
            }
        });
    }else{
        drawAreaLine(lineData[0]);
    }
}

/**** 绘制分时图 ******/
function drawAreaLine( data ){
    var areaOption = {
        backgroundColor: '#fff',
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross'
            },
            // formatter:function(data){
            //     var dom='开盘价'+data[0].value[1]+'</br>';
            //     dom+='收盘价'+data[0].value[2]+'</br>';
            //     dom+='最低价'+data[0].value[3]+'</br>';
            //     dom+='最高价'+data[0].value[4];
            //     return dom
            // }
        },
        grid: [
            {
                left: '10%',
                right: '8%',
                height: '56%',
                top:"10%"
            },
            {
                left: '10%',
                right: '8%',
                top: '78%',
                height: '16%'
            },
            {
                left: '10%',
                right: '8%',
                height: '56%',
                top:"10%"
            }
        ],
        xAxis: [
            {
                type: 'category',
                data: data.categoryData,
                scale: true,
                boundaryGap : false,
                axisLine: {onZero: false},
                splitLine: {
                    show: true,
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
                splitNumber: 5,
                min: 'dataMin',
                max: 'dataMax',
                axisLine: {
                    lineStyle: {
                        color: '#a3a3a3',
                        width: 0.6,
                    },
                },
                axisLabel: {
                    interval: function(index, value) {

                        if (value == "09:30") return true;

                        if (value == "10:30") return true;

                        if (value == "11:30") return true;

                        if (value == "14:00") return true;

                        if (value == "15:00") return true;

                        return false;

                    },

                    formatter: function(value, index) {

                        if (value == "11:30") return "11:30/13:00";

                        return value;

                    },

                    textStyle: {
                        color: '#0f0f0f',
                        fontSize: "8"
                    },

                    color: "#fff"

                },
            },
            {
                type: 'category',
                gridIndex: 1,
                data: data.categoryData,
                scale: true,
                boundaryGap : false,
                axisLine: {onZero: false},
                axisTick: {show: false},
                // splitLine: {show: false},
                axisLabel: {show: false},
                splitNumber: 5,
                min: 'dataMin',
                max: 'dataMax',
                axisLine: {
                    lineStyle: {
                        color: '#a3a3a3',
                        width: 0.6,
                    },
                },
                splitLine: {
                    show: true,
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
            }
        ],
        yAxis: [
            {
                position: 'left',
                scale: true,
                splitArea: {
                    show: false
                },
                axisLabel: {
                    inside: false,
                    margin: 0,
                    formatter: function (value, index) {
                        return value + "\n";
                    }
                },
                axisLine: {
                    lineStyle: {
                        color: '#a3a3a3',
                        width: 0.6,
                    },
                },
                splitLine: {
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#0f0f0f',
                        fontSize: "8"
                    }
                },
                // minInterval: 1,
                splitNumber: 4,
            },
            {
                position: 'left',
                scale: true,
                gridIndex: 1,
                splitNumber: 2,
                // axisLabel: {show: false},
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#0f0f0f',
                        fontSize: "8"
                    },
                    formatter: function(value, index) {

                        if (index == 0) return "(万)";

                        return value;

                    },
                },
                axisLine: {show: false},
                axisTick: {show: false},
                // splitLine: {show: false},
                axisLine: {
                    lineStyle: {
                        color: '#999',
                        width: 1,
                    },
                },
                splitLine: {
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
                splitNumber: 2,
            },
            // {
            //     position: 'right',
            //     min:-1,
            //     max:1,
            //     scale: true,
            //     splitArea: {
            //         show: false
            //     },
            //     axisLabel: {
            //         inside: false,
            //         margin: 0,
            //         formatter: function (value, index) {
            //             return value + "\n";
            //         }
            //     },
            //     axisLine: {
            //         lineStyle: {
            //             color: 'transparent',
            //             width: 0.6,
            //         },
            //     },
            //     splitLine: {
            //         lineStyle: {
            //             color: ['#f2f2f2']
            //         }
            //     },
            //     axisLabel: {
            //         show: true,
            //         textStyle: {
            //             color: '#0f0f0f',
            //             fontSize: "8"
            //         }
            //     },
            //     splitNumber: 4,
            // },
        ],
        series: [
            {
                name: '分时图',
                type: 'line',
                data: calculateMA(1, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 0.5}
                },
                symbol: 'none',
                smooth: true,
                itemStyle: {
                    normal: {
                        areaStyle: { type: 'default' },
                        color: "rgba(0,0,0,0)",
                        borderColor: "#3b98d3",
                        lineStyle: { width: 1, color: ['#3b98d3'] },
                    }
                },
            },
            {
                name: 'Volume',
                type: 'bar',
                xAxisIndex: 1,
                yAxisIndex: 1,
                data: data.volumes,

                itemStyle: {
                    normal: {
                        color: '#e2d194'
                    },
                    emphasis: {
                        color: '#140'
                    }
                },
            }

        ]
    }


    myChart.setOption(areaOption, true);


}
/***** k线 ******/
function initKline( type ){
    // 如果不是第一次请求， 直接使用已有数据绘制
    if( lineData[type] ){
        drawKchart( lineData[type] );
    }else{ //第一次加载， 请求数据
        /****---- 初始化分时图 -----***/
        var _url = kLineUrl,
            _code = $("#guName").data("code"),
            _oData = {code:_code, period:type, count: 60};
        $ajaxCustom(_url, _oData, function(res){
            if(res.state){ 
                var _data = res.data;
                lineData[type] = splieKNum(_data);
                drawKchart( lineData[type] );
            }else{
                $alert(res.info);
            }
        });
    }
}
/**** 分割k线数据 ***/
function splieKNum(rawData){
    var categoryData = [];
    var values = [];
    var volumes = [];
    for (var i = 0; i < rawData.length; i++) {
        categoryData.push( rawData[i][0] );
        values.push([rawData[i][1], rawData[i][2],  rawData[i][4], rawData[i][3], rawData[i][5], rawData[i][5]]);
        var _count = "-";
        if(i != 0){
            _count = rawData[i][5] / 10000;
        }
        volumes.push([i, _count, rawData[i][1] > rawData[i][2] ? 1 : -1]);
    }

    return {
        categoryData: categoryData,
        values: values,
        volumes: volumes
    };
}

/**** 绘制k线 ***/
function drawKchart(data){
    var kOption = {
        backgroundColor: '#fff',
        tooltip: {
            transitionDuration: 0,
            confine: true,
            bordeRadius: 4,
            borderWidth: 1,
            borderColor: '#999',
            backgroundColor: 'rgba(255,255,255,0.9)',
            trigger: 'axis',
            textStyle: {
                fontSize: 10,
                color: '#333'
            },
            position: function (pos, params, el, elRect, size) {
                var obj = {
                    top: 10
                };
                obj[['left', 'right'][+(pos[0] < size.viewSize[0] / 2)]] = 5;
                return obj;
            },
            axisPointer: {
                type: 'cross'
            },
            // formatter:function(data){
            //     var dom='开盘价'+data[0].value[1]+'</br>';
            //     dom+='收盘价'+data[0].value[2]+'</br>';
            //     dom+='最低价'+data[0].value[3]+'</br>';
            //     dom+='最高价'+data[0].value[4]+'</br>';
            //     dom+='成交量'+data[0].value[6]+'</br>';
            //     dom+='成交额'+data[0].value[5];
            //     return dom
            // }
        },
        // axisPointer: {
        //     link: {xAxisIndex: 'all'},
        //     label: {
        //         backgroundColor: '#fff'
        //     }
        // },
        grid: [
            {
                left: '10%',
                right: '8%',
                height: '56%',
                top:"10%"
            },
            {
                left: '10%',
                right: '8%',
                top: '78%',
                height: '16%'
            }
        ],
        visualMap: {
            show: false,
            seriesIndex: 5,
            dimension: 2,
            pieces: [{
                value: 1,
                color: downColor
            }, {
                value: -1,
                color: upColor
            }]
        },
        xAxis: [
            {
                type: 'category',
                data: data.categoryData,
                scale: true,
                boundaryGap : false,
                axisLine: {onZero: false},
                splitLine: {
                    show: true,
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
                splitNumber: 5,
                min: 'dataMin',
                max: 'dataMax',
                axisLine: {
                    lineStyle: {
                        color: '#a3a3a3',
                        width: 0.6,
                    },
                },
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#0f0f0f',
                        fontSize: "8"
                    }
                }
            },
            {
                type: 'category',
                gridIndex: 1,
                data: data.categoryData,
                scale: true,
                boundaryGap : false,
                axisLine: {onZero: false},
                axisTick: {show: false},
                // splitLine: {show: false},
                axisLabel: {show: false},
                splitNumber: 5,
                min: 'dataMin',
                max: 'dataMax',
                axisLine: {
                    lineStyle: {
                        color: '#a3a3a3',
                        width: 0.6,
                    },
                },
                splitLine: {
                    show: true,
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
            }
        ],
        yAxis: [
            {
                scale: true,
                splitArea: {
                    show: false
                },
                axisLabel: {
                    inside: false,
                    margin: 0,
                    formatter: function (value, index) {
                        return value + "\n";
                    }
                },
                axisLine: {
                    lineStyle: {
                        color: '#a3a3a3',
                        width: 0.6,
                    },
                },
                splitLine: {
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#0f0f0f',
                        fontSize: "8"
                    }
                },
                // minInterval: 1,
                splitNumber: 4,
            },
            {
                scale: true,
                gridIndex: 1,
                splitNumber: 2,
                // axisLabel: {show: false},
                axisLabel: {
                    show: true,
                    textStyle: {
                        color: '#0f0f0f',
                        fontSize: "8"
                    },
                    formatter: function(value, index) {

                        if (index == 0) return "(百万)";

                        return value;

                    },
                },
                axisLine: {show: false},
                axisTick: {show: false},
                // splitLine: {show: false},
                axisLine: {
                    lineStyle: {
                        color: '#999',
                        width: 1,
                    },
                },
                splitLine: {
                    lineStyle: {
                        color: ['#f2f2f2']
                    }
                },
                splitNumber: 2,
            },
        ],
        series: [
            {
                name: '日K',
                type: 'candlestick',
                data: data.values,
                itemStyle: {
                    normal: {
                        color: upColor,
                        color0: downColor,
                        borderColor: null,
                        borderColor0: null
                    }
                },
            },
            {
                name: 'MA5',
                type: 'line',
                data: calculateMA(5, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 1},
                },
                symbol: 'none',
                smooth: true,
                itemStyle: {
                    normal: {
                        color: '#e6262e',
                        lineStyle:{
                            width:0.5,//折线宽度
                            color:"#FF0000"//折线颜色
                        }
                    }
                },
            },
            {
                name: 'MA10',
                type: 'line',
                data: calculateMA(10, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 1},
                },
                itemStyle: {
                    normal: {
                        color: '#e6262e',
                        lineStyle:{
                            width:0.5,//折线宽度
                            // color:"#FF0000"//折线颜色
                        }
                    }
                },
            },
            {
                name: 'MA20',
                type: 'line',
                data: calculateMA(20, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 1},
                },
                itemStyle: {
                    normal: {
                        color: '#e6262e',
                        lineStyle:{
                            width:0.5,//折线宽度
                            // color:"#FF0000"//折线颜色
                        }
                    }
                },
            },
            {
                name: 'MA30',
                type: 'line',
                data: calculateMA(30, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 1},
                },
                itemStyle: {
                    normal: {
                        color: '#e6262e',
                        lineStyle:{
                            width:0.5
                        }
                    }
                },
            },
            {
                name: 'Volume',
                type: 'bar',
                xAxisIndex: 1,
                yAxisIndex: 1,
                data: data.volumes
            }
        ]
    }
    myChart.setOption(kOption, true);
}

















