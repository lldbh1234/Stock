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




/* mock */


var upColor = '#ff0200';
var downColor = '#008002';
var myChart = echarts.init(document.getElementById('chart'));


function splitData(rawData) {
    var categoryData = [];
    var values = [];
    var volumes = [];
    for (var i = 0; i < rawData.length; i++) {
        // categoryData.push(rawData[i].splice(0, 1)[0]);
        values.push(rawData[i]);
        volumes.push([i, rawData[i][4], rawData[i][0] > rawData[i][1] ? 1 : -1]);
    }

    // full = 242;
    // for (var i = 0; i < full - categoryData.length; i++) {
    //     categoryData.push("-");
    //     values.push("-");
    //     volumes.push([full - categoryData.length + i,"-","-"]);
    // }

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





    console.log(values);




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
            sum += data.values[i - j][1];
        }
        result.push(+(sum / dayCount).toFixed(3));
    }
    return result;
}

$(function (rawData) {
    rawData = [["2004-01-02",10452.74,10409.85,10367.41,10554.96,168890000],["2004-01-05",10411.85,10544.07,10411.85,10575.92,221290000],["2004-01-06",10543.85,10538.66,10454.37,10584.07,191460000],["2004-01-07",10535.46,10529.03,10432,10587.55,225490000],["2004-01-08",10530.07,10592.44,10480.59,10651.99,237770000],["2004-01-09",10589.25,10458.89,10420.52,10603.48,223250000],["2004-01-12",10461.55,10485.18,10389.85,10543.03,197960000],["2004-01-13",10485.18,10427.18,10341.19,10539.25,197310000],["2004-01-14",10428.67,10538.37,10426.89,10573.85,186280000],["2004-01-15",10534.52,10553.85,10454.52,10639.03,260090000],["2004-01-16",10556.37,10600.51,10503.7,10666.88,254170000],["2004-01-20",10601.4,10528.66,10447.92,10676.96,224300000],["2004-01-21",10522.77,10623.62,10453.11,10665.7,214920000],["2004-01-22",10624.22,10623.18,10545.03,10717.4,219720000],["2004-01-23",10625.25,10568.29,10490.14,10691.77,234260000],["2004-01-26",10568,10702.51,10510.44,10725.18,186170000],["2004-01-27",10701.1,10609.92,10579.33,10748.81,206560000],["2004-01-28",10610.07,10468.37,10412.44,10703.25,247660000],["2004-01-29",10467.41,10510.29,10369.92,10611.56,273970000],["2004-01-30",10510.22,10488.07,10385.56,10551.03,208990000],["2004-02-02",10487.78,10499.18,10395.55,10614.44,224800000],["2004-02-03",10499.48,10505.18,10414.15,10571.48,183810000],["2004-02-04",10503.11,10470.74,10394.81,10567.85,227760000],["2004-02-05",10469.33,10495.55,10399.92,10566.37,187810000],["2004-02-06",10494.89,10593.03,10433.7,10634.81,182880000],["2004-02-09",10592,10579.03,10433.7,10634.81,160720000],["2004-02-10",10578.74,10613.85,10511.18,10667.03,160590000],["2004-02-11",10605.48,10737.7,10561.55,10779.4,277850000],["2004-02-12",10735.18,10694.07,10636.44,10775.03,197560000],["2004-02-13",10696.22,10627.85,10578.66,10755.47,208340000],["2004-02-17",10628.88,10714.88,10628.88,10762.07,169730000],["2004-02-18",10706.68,10671.99,10623.62,10764.36,164370000],["2004-02-19",10674.59,10664.73,10626.44,10794.95,219890000],["2004-02-20",10666.29,10619.03,10559.11,10722.77,220560000],["2004-02-23",10619.55,10609.62,10508.89,10711.84,229950000],["2004-02-24",10609.55,10566.37,10479.33,10681.4,225670000],["2004-02-25",10566.59,10601.62,10509.4,10660.73,192420000],["2004-02-26",10598.14,10580.14,10493.7,10652.96,223230000],["2004-02-27",10581.55,10583.92,10519.03,10689.55,200050000],["2004-03-01",10582.25,10678.14,10568.74,10720.14,185030000],["2004-03-02",10678.36,10591.48,10539.4,10713.92,215580000],["2004-03-03",10588.59,10593.11,10506.66,10651.03,188800000],["2004-03-04",10593.48,10588,10522.59,10645.33,161050000],["2004-03-05",10582.59,10595.55,10497.11,10681.4,223550000],["2004-03-08",10595.37,10529.48,10505.85,10677.85,199300000],["2004-03-09",10529.52,10456.96,10391.48,10567.03,246270000],["2004-03-10",10457.59,10296.89,10259.34,10523.11,259000000],["2004-03-11",10288.85,10128.38,10102.75,10356.22,292050000],["2004-03-12",10130.67,10240.08,10097.04,10281.63,223350000],["2004-03-15",10238.45,10102.89,10066.08,10252.68,219150000],["2004-03-16",10103.41,10184.67,10085.34,10253.26,194560000],["2004-03-17",10184.3,10300.3,10184.3,10356.59,181210000],["2004-03-18",10298.96,10295.78,10187.78,10355.04,218820000],["2004-03-19",10295.85,10186.6,10163.71,10355.41,261590000],["2004-03-22",10185.93,10064.75,9985.19,10185.93,248930000],["2004-03-23",10066.67,10063.64,10020.75,10177.04,215260000],["2004-03-24",10065.41,10048.23,9975.86,10140.23,224310000],["2004-03-25",10049.56,10218.82,10049.56,10246.15,216420000],["2004-03-26",10218.37,10212.97,10145.63,10306.22,198830000],["2004-03-29",10212.91,10329.63,10212.91,10389.93,197150000],["2004-03-30",10327.63,10381.7,10264.15,10411.41,189060000],["2004-03-31",10380.89,10357.7,10287.11,10428.59,207400000],["2004-04-01",10357.52,10373.33,10299.48,10449.33,218660000],["2004-04-02",10375.33,10470.59,10375.33,10548.74,243070000],["2004-04-05",10470.59,10558.37,10423.33,10582.22,182130000],["2004-04-06",10553.76,10570.81,10467.26,10596.37,175720000],["2004-04-07",10569.26,10480.15,10422.74,10580.51,218040000],["2004-04-08",10482.77,10442.03,10383.84,10590.15,187730000],["2004-04-12",10444.38,10515.56,10439.27,10559.28,142190000],["2004-04-13",10516.05,10381.28,10343.17,10572.13,202540000],["2004-04-14",10378.1,10377.95,10259.35,10453.39,230460000],["2004-04-15",10377.95,10397.46,10279.37,10481.21,262880000],["2004-04-16",10398.32,10451.97,10343.74,10500.57,234660000],["2004-04-19",10451.62,10437.85,10351.97,10501.79,173340000],["2004-04-20",10437.85,10314.5,10297.39,10530.61,204710000],["2004-04-21",10311.87,10317.27,10200.38,10398.53,232630000],["2004-04-22",10314.99,10461.2,10255.88,10529.12,265740000],["2004-04-23",10463.11,10472.84,10362.97,10543.95,277070000],["2004-04-26",10472.91,10444.73,10396.75,10540.26,183040000],["2004-04-27",10445.38,10478.16,10410.52,10570.92,213410000],["2004-04-28",10476.67,10342.6,10301.65,10479.58,232090000]];
    var data = splitData(rawData);

    var kOption = {
        backgroundColor: '#fff',
        tooltip: {
            trigger: 'axis',
            axisPointer: {
                type: 'cross'
            },
            formatter:function(data){
                var dom='开盘价'+data[0].value[1]+'</br>';
                dom+='收盘价'+data[0].value[2]+'</br>';
                dom+='最低价'+data[0].value[3]+'</br>';
                dom+='最高价'+data[0].value[4];
                return dom
            }
        },
        axisPointer: {
            link: {xAxisIndex: 'all'},
            label: {
                backgroundColor: '#fff'
            }
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
                    }
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
        dataZoom: [
            {
                type: 'inside',
                xAxisIndex: [0, 1],
                start: 97,
                end: 100
            },
            {
                show: false,
                xAxisIndex: [0, 1],
                type: 'slider',
                top: '85%',
                start: 97,
                end: 100
            }
        ],
        series: [
            {
                name: '日K',
                type: 'candlestick',
                data: data.values,
                itemStyle: {
                    normal: {
                        color: 'rgb(255, 48, 48)',
                        color0: 'rgb(10, 185, 43)',
                        borderColor: 'rgb(255, 48, 48)',
                        borderColor0: 'rgb(10, 185, 43)'
                    }
                },
            },
            {
                name: 'MA5',
                type: 'line',
                data: calculateMA(5, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 0.5},
                },
                symbol: 'none',
                smooth: true,
                itemStyle: {
                    normal: {
                        color: '#e6262e',
                    }
                },
            },
            {
                name: 'MA10',
                type: 'line',
                data: calculateMA(10, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 0.5}
                }
            },
            {
                name: 'MA20',
                type: 'line',
                data: calculateMA(20, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 0.5}
                }
            },
            {
                name: 'MA30',
                type: 'line',
                data: calculateMA(30, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 0.5}
                }
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
            {
                position: 'right',
                min:-1,
                max:1,
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
                        color: 'transparent',
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
        ],
        // dataZoom: [
        //     {
        //         type: 'inside',
        //         xAxisIndex: [0, 1],
        //         start: 0,
        //         end: 100
        //     },
        //     {
        //         show: false,
        //         xAxisIndex: [0, 1],
        //         type: 'slider',
        //         top: '85%',
        //         start: 0,
        //         end: 100
        //     }
        // ],
        series: [
            {
                name: '分时图',
                type: 'line',
                data: calculateMA(5, data),
                smooth: true,
                lineStyle: {
                    normal: {opacity: 0.5}
                },
                symbol: 'none',
                smooth: true,
                itemStyle: {
                    normal: {
                        areaStyle: { type: 'default' },
                        color: "#d5e1f2",
                        borderColor: "#3b98d3",
                        lineStyle: { width: 1, color: ['#3b98d3'] },
                    }
                },
                // markLine : {
                //     symbol: ['arrow', 'none'],
                //     symbolSize: [10, 10],
                //     data:[{
                //         yAxis:data.values[data.values.length - 1][1],
                //         value:data.values[data.values.length - 1][1]
                //     }],
                //     itemStyle : {
                //         normal: {
                //             lineStyle: {color:'#C23531'},
                //             barBorderColor:'#C23531',
                //             label:{
                //                 position:'left',
                //                 formatter:function(params){
                //                     return "\t" + params.value;
                //                 },
                //                 textStyle:{color:'#C23531'}
                //             }
                //         }
                //     }
                // }
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


    $(".switch_btn").on("click" , "span" , function(){
        $(this).addClass('active').siblings('.active').removeClass('active');
        if ( $(this).hasClass('arealine') ){
            myChart.setOption(areaOption, true);
        }else{
            myChart.setOption(kOption, true);
        }
    });

});















