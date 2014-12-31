<?php 
/**
 * @brief   测试数组leftJoin功能,包含测试demo
 * @author  guoch
 * @desc    这个类主要是用来解决sql语句中的in查询后，需要和原来的数组进行合并处理，但由于多维数组，没有统一的PHP函数，现在写一个工具类来处理此类事情
 */
class testLeftJoinArray {
    public static function LeftJoinArray($array1, $array2, $field1, $field2 = '') {
        $ret = array();
        //使用循环嵌套的办法-----淘汰掉了，效率不够高，两个数组比较大的时候，循环次数是两个数组长度的乘积
//         foreach($array1 as $key1 => $value1 ) {
//             foreach ($array2 as $key2 => $value2) {
//                 if($value1[$field1] == $value2[$field2]) {
//                     $ret[$key1] = array_merge($value1, $value2);
//                 }
//             }
//         }
    
        //使用数组下标的办法-------目前正在使用，循环次数是两个数组长度之和。（求效率更高的）
        foreach ($array2 as $key => $value) {
            $array3[$value[$field1]] = $value;
        }
        foreach ($array1 as $key => $value) {
            $ret[] = array_merge($array3[$value[$field2]], $value);
        }
        return $ret;
    }
}


/********demo 开始*********************/
//测试数组：
$test1 =  Array(
        0 => Array(
                'id' => 9478137,
                'create_time' => 1394760724
        ),
        1 => Array(
                'id' => 9478138,
                'create_time' => 1394760725
        ),
        2 => Array(
                'id' => 9478138,
                'create_time' => 1394760725
        )
);
$test2 = array(
        0 => array(
                'id' => 9478137,
                'message' => 'love you'
        ),
        1 => array(
                'id' => 9478138,
                'message' => 'miss you'
        )
);
$ret = testLeftJoinArray::LeftJoinArray($test1, $test2, 'id', 'id');
print_r($ret);exit;


/********demo 结束*********************/



/*********结果开始

如果把test1，和test2看成两张数据表中的数据下面的函数类似与SQL中的left join
select a.id,a.create_time from test1 a left join test2 b on a.id = b.id;
结果显示（chrome中）
Array
(
    [0] => Array
        (
            [id] => 9478137
            [message] => love you
            [create_time] => 1394760724
        )

    [1] => Array
        (
            [id] => 9478138
            [message] => miss you
            [create_time] => 1394760725
        )

    [2] => Array
        (
            [id] => 9478138
            [message] => miss you
            [create_time] => 1394760725
        )

)

结果结束*********************/



