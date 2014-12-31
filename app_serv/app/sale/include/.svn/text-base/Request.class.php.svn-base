<?php
//通过curl远程访问web端业管逻辑并取得数据供手机端使用

class Request {
    
   //远程请求
    public static function RequestUrl($url, $data, $request = 0, $time = 5) {
        try {
            $ch = curl_init ();
        
            curl_setopt ($ch, CURLOPT_URL, $url);            //定义表单提交地址
            curl_setopt ($ch, CURLOPT_POST, $request);       //定义提交类型 1：POST ；0：GET 
            curl_setopt ($ch, CURLOPT_HEADER, 0);            //定义是否显示状态头 1：显示 ； 0：不显示 
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);    //定义是否直接输出返回流 
            curl_setopt ($ch, CURLOPT_POSTFIELDS, $data);    //定义提交的数据
            
            // fix start curl连接等待时间和取数据等待时间 修改人：苏卫林 20140816
            //connection 连接等待时间
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 1);
            //get data 等待时间
            curl_setopt ($ch, CURLOPT_TIMEOUT, 1);
            // fix end 
            
            $ret = curl_exec ($ch);
            curl_close ($ch);
        } catch (Exception $e) {
            
        }
        
        return $ret;
    }
    
}


