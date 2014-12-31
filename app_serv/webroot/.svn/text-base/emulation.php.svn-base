<html>
<head>
<meta charset="utf-8">
<style>
    div.center{
        margin: auto;
        width: 400px;
    }
    .boder{
        border: dashed 1px
    }
    .form-area{
        padding: 10px;
    }
    .form-area label{
        float:left;
        width:80px;
    }
</style>
<script src="http://sta.273.com.cn/g.js" type="text/javascript"></script>
<script src="http://sta.273.com.cn/config.js" type="text/javascript"></script>
</head>
<body>
    <div class="center">
        <h2> appserv 接口模拟</h2>
        <div style="margin-bottom: 10px;">
            <label>字段标题（如用户名）：</label> <input id='inputTitle' type="text" >
            <br />
            <label>字段名（如username）：</label> <input id='inputName' type="text" >
            <br />
            <input id='addInput' type="button" value="添加字段">
        </div>
        <div class="form-area boder">
            <h3>表单模拟区</h3>
            <span><label>action:</label><input style="width:250px;" type="text" name="url"/></span>
            <form id="form" action="http">
                <div id="custom">
                    
                </div>
            </form>
        </div>
        <input id='submit' type="button" value="提交表单">
        <input id='remember' type="button" value="记住表单">
        <input id='clear' type="button" value="清空表单">
        
        <h3>返回结果：</h3>
        <div id="result"></div>
    </div>
</body>
<script type="text/javascript">
    G.use(['jquery'], function($){
        var storage = window.localStorage;
        if(!storage){
            alert('当前浏览器不支持记住表单功能，推荐谷歌浏览器');
        } else {
            var formHtml = storage.getItem("273_api_emulation_form");
            if (formHtml) {
                $('#custom').html(formHtml);
            }
        }
        $('#addInput').click(function(){
            var title = $('#inputTitle').val();
            var name = $('#inputName').val();
            if (title && name) {
                var $input = '<span><label>' + title + ':</label>' + '<input type="text" name="' + name + '"></span><br>';
                $('#custom').append($input);
            }
        });
        
        $('#remember').click(function(){
            if (storage && $('#custom').html()) {
                storage.setItem("273_api_emulation_form", $('#custom').html());
            }
        });
        
        $('#clear').click(function(){
           $('#custom').html(""); 
        });
        
        $('#submit').click(function(){
           $.ajax({
               url: $('input[name=url]').val(),
               type: 'get',
               data:$('form').serialize(),
               success:function(data){
                   console.log(data);
                   $('#result').html(JSON.stringify(data));
               }
           });
        });
    });
</script>
</html>