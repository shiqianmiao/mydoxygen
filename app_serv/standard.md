AppServ注释怎么写？                        
==================

#### 说明：

	该规范定义后，所有AppServ的接口文件必须严格按照要求来，以方便使用者查询和调用。

	虽然规范注释会再书写程序时在注释上面花费较多的时间，但是以此可以换来实时的有权威性的文档说明，可以大大方便后期的维护。

#### 接口类文件注释规范示例如下：
> 必须要有的属性：brief、version、date、author

	/**
	 * @ brief  这里用brief来说明类文件的主要功能
	 * @version 接口版本，例如：1.0、2.0
	 * @date    这里用date来说明类文件的创建时间
	 * @author  这里用author来说明类文件的作者
	 */
	
	class SaleServiceApp extends BcPost {
		public function xxx() {
		}
	}

#### 接口方法注释规范示例：
> 必须要有的属性：brief、date、author、param(表格的形式)、return(建议用表格形式，特别是返回值个数较多时)<br/>
> 注意：不对外开发的接口方法一定要写成private属性，所有public的接口都将在文档中展现。

	/**
	 * @ brief 这里用brief来说明接口方法的主要功能
	 * @date   接口方法的创建时间
	 * @author 接口方法的创建人
	 * @param  : 参数说明如下表：
	 * name     | type     |description of param 
	 * ----------|-----------|--------------------
	 * car_id   | int      |车源编号
	 * province | int      |业务员所在省份
	 * x        |  x       |   x
	 * x        |  x       |   x
	 * x        |  x       |   x
	 * @return    返回值说明如下：
	 * name     | type     | description of value
	 * -------- |----------|----------------------
	 * car_id   | int      | 车源编号
	 * car_info | object   | json对象格式的车源信息
	 * @warning   该接口需要告知给调用者看的一些警告
	 * @attention 该接口需要告知给调用者看的一些注意事项
	 * @note      该接口的一些备注说明。通常用于当后者对该接口有较大改动的时候。备注一下某个时间点某人改动了什么东西
	 * @ todo     该接口的一些未完成的待办内容
	 */
	
	 public function newSale() {
	 	do someting;
	 }
        
#### 注意事项：   

	1. 接口类文件的类名称不要重复，特别是当接口升级版本的时候，例如SaleServiceApp、SaleServiceApp_2(表示版本2.0的卖车接口)。

	2. 不对外提供的接口方法使用private属性。

	3. 后者在修改别人的接口的时候，如果发生变动，务必要修改对应的接口注释，以免误导使用者。
	
	4. 规定必须注释的属性必须要注释，其余的根据个人情况酌情添加。

	5. 最好建议提交接口后，去查看下文档系统中的注释错误日志里面是否有自己的错误，或者在文档中查看自己写的接口注释是否OK。

	6. 当你对接口有较为大的改动的时候，建议在接口注释中添加@note 的通知注释，里面表面改动和改动着姓名。建议，但不强制。

#### 其他常用注释属性：
1. code 与 endcode的使用,在注释当中加上对于的代码块，并且高亮显示与文档中。
@code{.py}
<?php
    foreach($a as $b) {
        do someting;
    }
@endcode
