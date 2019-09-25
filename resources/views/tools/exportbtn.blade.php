{{--导出按钮--}}
<div class="btn-group pull-right" style="margin-right: 10px">
    <a class="btn btn-sm btn-twitter" title="导出"><i class="fa fa-download"></i><span class="hidden-xs"> 导出</span></a>
    <button type="button" class="btn btn-sm btn-twitter dropdown-toggle" data-toggle="dropdown">
        <span class="caret"></span>
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li class="{{isset($_prefix) ? $_prefix : ""}}wen-create-btn" ><a href="javascript:void(0);" data-url="{{isset($url) ? $url : ''}}" data-option="all">全部</a></li>
        <li class="{{isset($_prefix) ? $_prefix : ""}}wen-create-btn" ><a href="javascript:void(0);" data-url="{{isset($url) ? $url : ''}}" data-option="page">当前页</a></li>
        <li class="{{isset($_prefix) ? $_prefix : ""}}wen-create-btn" ><a href="javascript:void(0);" class="export-selected" data-url="{{isset($url) ? $url : ''}}" data-option="select">选择的行</a></li>
        <li class="{{isset($_prefix) ? $_prefix : ""}}wen-create-btn" ><a href="javascript:void(0);" data-url="{{isset($url) ? $url : ''}}" data-option="range">指定页</a></li>
    </ul>
</div>
{{--导出数据加载过程显示框--}}
<div id="{{isset($_prefix) ? $_prefix : ""}}wen-show-exporter-box" style="display: none;">
    <div style="position: fixed;left: 0px;top: 0px;z-index: 2000;width: 100vw;height: 100vh;background: black;opacity: 0.5;"></div>
    <div style="position: fixed;left: 0px;top:0px;z-index: 2001;width: 100vw;height: 100vh;">
        <div style="width: 200px;height: 200px;background: white;margin: 30vh auto;border-radius: 5px;">
            <div style="height: 80%;text-align: center;">
                <img id="{{isset($_prefix) ? $_prefix : ""}}wen-change" style="" src="/images/static/loading.png" alt="">
                <div>正在导出</div>
            </div>
            <div style="text-align: center;">
                <span class="btn-warning btn" style="height: 30px;line-height: 18px;" onclick="cancelExporter()">取消</span>
            </div>
        </div>
    </div>
</div>
{{--<table>--}}
    {{--<tr>--}}
        {{--<td colspan="" rowspan=""></td>--}}
    {{--</tr>--}}
{{--</table>--}}
<style>
    #{{isset($_prefix) ? $_prefix : ""}}wen-change{
        width: 40%;margin-top: 20%;
        -webkit-animation:mymove 2s infinite;
        animation: mymove 2s infinite;
    }

    @keyframes mymove
    {
        0% {-webkit-transform:rotate(0deg);}
        50% {-webkit-transform:rotate(180deg);}
        100% {-webkit-transform:rotate(360deg);}
    }
    @-webkit-keyframes mymove /*Safari and Chrome*/
    {
        0% {-webkit-transform:rotate(0deg);}
        50% {-webkit-transform:rotate(180deg);}
        100% {-webkit-transform:rotate(360deg);}
    }

    /*@-webkit-keyframes mymove{*/
    /*　　0%{-webkit-transform:rotate(0deg);}*/
    /*　　50%{-webkit-transform:rotate(180deg);}*/
    /*　　100%{-webkit-transform:rotate(360deg);}*/
    /*}*/
</style>
<script>
    {!! isset($script) ? $script : '' !!}
    // ajax请求对象
    let ajaxRequest = null;
    // 显示提示框
    function showTips(){
        let shotTipsBox = document.querySelector('#{{isset($_prefix) ? $_prefix : ""}}wen-show-exporter-box');
        shotTipsBox.style.display = 'block';
    }
    // 隐藏提示框
    function hideTips(){
        let shotTipsBox = document.querySelector('#{{isset($_prefix) ? $_prefix : ""}}wen-show-exporter-box');
        shotTipsBox.style.display = 'none';
    }
    // 取消导出
    function cancelExporter()
    {
        if(ajaxRequest){
            ajaxRequest.abort();
        }
        hideTips();
    }

    document.querySelectorAll('.{{isset($_prefix) ? $_prefix : ""}}wen-create-btn').forEach(function (item, index) {
        item.addEventListener('click', function (event) {
            event = event || window.event;
            event.preventDefault();
            let trans = {!! $translations !!}; // 需要转义的字段，格式：{ 字段1:{索引1:值1，索引2:值2，索引3:值3}，字段2:{索引1:值1，索引2:值2，索引3:值3}}
            let toString = {!! $toString !!}; // 需要在前面添加单引号“’”的字段（）
            let head = {!! $head !!}; // excel头部第一行集合
            let body = {!! $body !!}; // 导出的字段集合
            let special = {!! $specialField !!}; // 特别字段
            let callback = {!! $callback !!}; // 回调处理
            let statistic = {!! $statistic !!}; // 统计字段

            // console.log('statistic ==> ',statistic);
            // console.log(trans, toString, head, body);
            let excelData = new Array(); // 需要导出excel的数据集合
            let excelPage = 0; // 导出页数
            getExcel(event);
            // 请求导出数据
            function getExcel(event, pageRange=null){
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': '{{$token}}'
                    }
                });
                let export_url = event.target.dataset.url; // 请求路由
                let rangePage = event.target.dataset.option; // 导出范围（全部，当前页，选择行，指定页）
                if(!export_url){
                    export_url = window.location.href;
                }
                let ids = new Array();
                // console.log(pageRange);

                switch (rangePage) {
                    case 'all':// 全部
                        break;
                    case 'page': // 当前页
                        ids = selectRows(false);
                        break;
                    case 'select': // 选择行
                        ids = selectRows();
                        if (ids.length <= 0) {
                            Swal.fire({
                                title: '请选择导出的行！', //标题
                                type: 'warning', // 弹框类型
                                html: '', // HTML
                                // html: '<iframe width="100%" height="100%" frameborder="0" src="http://192.168.1.180/admin/face_sign_modification"></iframe>', // HTML
                                confirmButtonColor: '#3085d6',// 确定按钮的 颜色
                                // showConfirmButton:false,
                                confirmButtonText: '知道了',// 确定按钮的 文字
                                allowOutsideClick:false,
                                // showCancelButton:true,
                                // cancelButtonText: '取消',
                                // focusCancel: true, // 是否聚焦 取消按钮
                                // reverseButtons: true  // 是否 反转 两个按钮的位置 默认是  左边 确定  右边 取消
                            }).then((isConfirm) => {

                            });
                           return false;
                        }
                        break;
                    case 'range': // 指定页
                        // console.log('range');
                        if(!pageRange) {
                            // 显示页数选择
                            rangeFunc(event);
                            return false;
                        }
                        break;
                    default:
                }
                showTips();
                // 获取当前列表的每页行数
                let gridPager = event.target.closest('body').querySelector('.grid-per-pager');
                let form = event.target.closest('body').querySelector('.form-horizontal');
                let data = {};
                if('{{$method}}' == 'POST'){
                    data = {
                        model: '{{$model}}',
                        exporter: '{{$exporter}}',
                        page: excelPage,
                        ids: ids,
                        range: pageRange,
                        perPage: gridPager.options[gridPager.selectedIndex].text,
                    }
                    // 获取筛选参数
                    let formData = new FormData(form);
                    formData.forEach(function (item, index) {
                        let reg = /(.+)\[(.*)\]/;
                        // console.log(index , reg.test(index));
                        if(reg.test(index)){
                            index = reg.exec(index);
                            // console.log('===>', index);
                            if(index[1] in special){
                                index[1] = special[index[1]];
                            }
                            if(typeof data[index[1]] != 'object'){
                                data[index[1]] = new Array();
                            }
                            if (index[2]){
                                data[index[1]+'['+index[2]+']'] = item;
                            }else{
                                data[index[1]].push(item);
                            }
                        }else{
                            if (index in special) {
                                index = special[index];
                            }
                            data[index] = item;
                        }
                    })
                }
                // console.log('data => ',data);
                // 发送ajax请求
                ajaxRequest = $.ajax({
                    url: export_url+'?_export='+rangePage,
                    method: '{{$method}}',
                    // contentType:"application/json",
                    responseType: 'json',
                    data: data,
                    success: function(res) {
                        if(res.length == 0) return true;
                        res = JSON.parse(res)
                        excelData.push(res.data);
                        // 判断数据获取完成
                        if(res.end == true) {
                            excelPage = 0;
                            hideTips();
                            // 导出excel
                            download();
                        } else {
                            // 轮询查询数据
                            excelPage++;
                            getExcel(event);
                        }
                    },
                    fail: function(err) {
                        console.warn(err)
                    }
                });
            }
            // 选择导出页数
            function rangeFunc(event) {
                Swal.fire({
                    title: '请选择导出页数范围', //标题
                    // type: 'info', // 弹框类型
                    html: '<div>第&nbsp;<input type="text" id="rangePage-start" style="width:80px;line-height: 25px;"> — <input type="text" id="rangePage-end" style="width:80px;line-height: 25px;">&nbsp;&nbsp;页<\/div>', // HTML
                    // html: '<iframe width="100%" height="100%" frameborder="0" src="http://192.168.1.180/admin/face_sign_modification"></iframe>', // HTML
                    confirmButtonColor: '#3085d6',// 确定按钮的 颜色
                    // showConfirmButton:false,
                    confirmButtonText: '确定',// 确定按钮的 文字
                    allowOutsideClick:false,
                    showCancelButton:true,
                    cancelButtonText: '取消',
                    // focusCancel: true, // 是否聚焦 取消按钮
                    // reverseButtons: true  // 是否 反转 两个按钮的位置 默认是  左边 确定  右边 取消
                }).then((isConfirm) => {
                    try {
                        //判断 是否 点击的 确定按钮
                        if (isConfirm.value) {
                            let startPage = $("#rangePage-start").val();
                            let endPage = $("#rangePage-end").val();
                            // console.log(startPage, endPage)
                            let pageRange = {
                                start: startPage,
                                end: endPage,
                            }
                            getExcel(event, pageRange);
                        }
                    } catch (e) {
                        alert(e);
                    }
                });
            }
            // 下载excel文件
            function download(){
                // 生成excel文件内容
                let str = makeHtml();
                //
                let blob = new Blob([str], {
                    type: 'application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                })
                if (window.navigator.msSaveOrOpenBlob) {
                    navigator.msSaveBlob(blob);
                } else {
                    let elink = document.createElement('a');
                    elink.download = "{{$filename}}.{{$type}}";
                    elink.style.display = 'none';
                    elink._target = 'blank';
                    elink.href = URL.createObjectURL(blob);
                    document.body.appendChild(elink);
                    elink.click();
                    document.body.removeChild(elink);
                }
            }

            // 统计信息
            function makeStatistic(){
                // console.log(statistic, head.length);
                if(!statistic.length){
                    return '';
                }
                let str = '';
                for (var i in statistic) {
                    str += statistic[i].text + '：' + (Math.round(statistic[i].sum * 100)/100) + '&nbsp;&nbsp;&nbsp;&nbsp;';
                }
                return '<tr><td colspan="'+ head.length +'" style="border-right: 0.11em solid gainsboro;border-bottom: 0.11em solid gainsboro;height: 28px;">'+ str +'</td></tr>';
            }

            // 生成excel文件内容
            function makeHtml(){
                <!--console.log(head);-->
                let str = '<table><thead><tr>';
                for (var i in head) {
                    str += '<th style="border-right: 0.11em solid gainsboro;border-bottom: 0.11em solid gainsboro;text-align: center;height: 34px;">'+head[i]+'</th>';
                }
                str += '</tr></thead><tbody>';
                for (var i in excelData) {
                    str = makeBody(excelData[i], str);
                }
                str += makeStatistic();
                str += '</tbody></table>';
                return str;
            }

            function makeBody(data, headStr){
                <!--console.log(data, trans, toString);-->
                // 循环数据集合，生成表格
                for (var i in data) {
                    headStr += '<tr>';
                    for (var j in body) {
                        // console.log(data[i], body[j]);
                        let strTemp = '';
                        if (toString.indexOf(body[j]) >= 0) {
                            strTemp = "'";
                        }
                        if (body[j] in callback) {
                            let args = new Array();
                            for(var k in callback[body[j]]['argument']) {
                                if(data[i][callback[body[j]]['argument'][k]] == null) {
                                    data[i][callback[body[j]]['argument'][k]] = 0.00;
                                }
                                // console.log('data=',data[i][callback[body[j]]['argument'][k]])
                                args.push(data[i][callback[body[j]]['argument'][k]])
                            }
                            data[i][body[j]] = eval(callback[body[j]]['callback']+'('+args.join(',')+')');
                        }
                        if (body[j] in statistic) {
                            if(typeof statistic[body[j]].sum == 'undefined'){
                                statistic[body[j]].sum = Number(data[i][body[j]]);
                            }else{
                                statistic[body[j]].sum += Number(data[i][body[j]]);
                            }
                            if(typeof statistic[body[j]].text == 'undefined'){
                                statistic[body[j]].text = head[j];
                            }
                        }
                        if ((body[j] in trans) && (data[i][body[j]] in trans[body[j]])) {
                            headStr += '<td style="border-right: 0.11em solid gainsboro;border-bottom: 0.11em solid gainsboro;text-align: center;height: 28px;">' + strTemp + trans[body[j]][data[i][body[j]]] +'</td>';
                        } else if(data[i][body[j]] && data[i][body[j]] != 'null' && data[i][body[j]] != null) {
                            headStr += '<td style="border-right: 0.11em solid gainsboro;border-bottom: 0.11em solid gainsboro;text-align: center;height: 28px;">' + strTemp + data[i][body[j]] +'</td>';
                        } else {
                            headStr += '<td style="border-right: 0.11em solid gainsboro;border-bottom: 0.11em solid gainsboro;text-align: center;height: 28px;"></td>';
                        }
                    }
                    headStr += '</tr>';
                }
                return headStr;
            }

        })
    })


    // 选择当前的行id【选中的行 或者 全部行】
    function selectRows(checked = true) {
        let ids = new Array();
        let classname = '.grid-row-checkbox:checked';
        if (!checked) {
            classname = '.grid-row-checkbox';
        }
        let objs = document.querySelectorAll(classname);
        if (objs){
            objs.forEach(function (item, index) {
                ids.push(item.getAttribute('data-id'));
            })
        }
        return ids;
    }
</script>