怎样调用273的AppServ接口？
========================

#### 接口调用域名说明：

	https://appserv.273.cn       (appServ 线上接口域名）
	
	https://appservtest.273.cn (appServ sim环境接口域名）

	http://192.168.5.31            (appServ 测试环境接口域名)

#### 系统参数说明：

> 注意：_api_passport 和 _api_app(目前值只有 bc ) 使用POST方式上发，其他的系统参数均使用GET方式上发。

系统参数名称 | 上发方式 | 系统参数说明
------------|-----------|-------------
_api_version | GET      | 调用接口的版本，例如：1.0、2.0....
_api_method  | GET      | 接口的方法，例如：sale.newSale
_api_time    | GET      | 调用接口时的时间戳
_api_token   | GET      | 调用接口时的口令
_api_key     | GET      | 接口证书：由273提供
_api_secret  | GET      | 接口证书：由273提供
_api_passport | POST    | 由user.login方法返回结果提供，客户端登陆后可将次_api_passport保留下来,不必每次都获取
_api_app     | POST     | 需要用户登录才能调用的接口必须，例如_api_app = bc 表明调用的是业管的接口
_api_debug   | GET      | 调试参数，可选，客户端发版时请将此参数去掉,为1的时候会在bc后台记录请求参数和返回结果的日志

#### 接口调用步骤解说：

1. 获取_api_token
https://appserv.273.cn/1.0/auth.getAccessToken?_api_time={发起请求时间戳}&_api_key={提供给的公钥}&_api_secret={md5(_api_secret{提供的私钥} + _api_time)}

2. 返回:_api_token,之后的请求请使用此_api_token,客户端可将此_api_token保留下来，_api_token有效期是30天 

#### 接口url示例:

	https://appserv.273.cn/1.0/search.getSaleList?_api_time={发起请求时间戳}&_api_key={提供给的公钥}&_api_token={通过auth.getAccessToken得到的令牌}&{method_params方法需要的参数} 

	在此_api_version=1.0，_api_method=search.getSaleList

#### 统一返回:

	返回正确结果的格式： 

	    json_encode(array(‘errorCode’：0，’errorMessge’：’’,’data’：method返回的内容))； 

	返回错误结果的格式： 

	    errorCode为相应错误码，errorMessge为错误信息，data=’’;

	    注:errorCode为9表示_api_token已过期，请重新调用auth.getAccessToken方式获取_api_token
