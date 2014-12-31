<?php
/**
 * @brief         
 * @author        zhuangjx<zhuangjx@273.cn>
 * @date          2013-12-25
 */

class ModelDd {
    public static $color =array (
                              1 => array('caption'=>'白色','color'=>'ffffff','bg'=>'000000'),
                              35 => array('caption'=>'银白色','color'=>'EFEFEF','bg'=>'000000'),
                              2 => array('caption'=>'黑色','color'=>'000000','bg'=>'ffffff'),
                              33 => array('caption'=>'灰色','color'=>'94979C','bg'=>'000000'),
                              3 => array('caption'=>'银灰','color'=>'D7DADF','bg'=>'000000'),
                              34 => array('caption'=>'香槟银','color'=>'E5E5E5','bg'=>'000000'),
                              4 => array('caption'=>'铁灰','color'=>'676863','bg'=>'000000'),
                              5 => array('caption'=>'灰紫蓝','color'=>'8892C5','bg'=>'000000'),
                              6 => array('caption'=>'青灰','color'=>'6C8D9E','bg'=>'000000'),
                              7 => array('caption'=>'绿色','color'=>'8FAD53','bg'=>'000000'),
                              8 => array('caption'=>'深绿','color'=>'0B481F','bg'=>'000000'),
                              9 => array('caption'=>'深灰绿','color'=>'264a48','bg'=>'000000'),
                              10 => array('caption'=>'淡蓝','color'=>'C8E8FF','bg'=>'000000'),
                              11 => array('caption'=>'湖蓝','color'=>'439BCB','bg'=>'000000'),
                              12 => array('caption'=>'天蓝','color'=>'3476cc','bg'=>'000000'),
                              13 => array('caption'=>'蓝色','color'=>'072d98','bg'=>'000000'),
                              14 => array('caption'=>'淡紫','color'=>'dfd0e7','bg'=>'000000'),
                              15 => array('caption'=>'粉红','color'=>'ff9ed5','bg'=>'000000'),
                              16 => array('caption'=>'紫色','color'=>'cd39a9','bg'=>'000000'),
                              17 => array('caption'=>'酱紫','color'=>'9a355f','bg'=>'000000'),
                              18 => array('caption'=>'深紫','color'=>'740568','bg'=>'000000'),
                              19 => array('caption'=>'浅香滨','color'=>'ede9d3','bg'=>'000000'),
                              20 => array('caption'=>'金色','color'=>'dbcf97','bg'=>'000000'),
                              21 => array('caption'=>'珍珠白','color'=>'fffae9','bg'=>'000000'),
                              22 => array('caption'=>'淡黄色','color'=>'fff5ae','bg'=>'000000'),
                              23 => array('caption'=>'中黄','color'=>'ffdf6b','bg'=>'000000'),
                              24 => array('caption'=>'黄色','color'=>'f8be2e','bg'=>'000000'),
                              25 => array('caption'=>'橙色','color'=>'f15e00','bg'=>'000000'),
                              26 => array('caption'=>'红色','color'=>'d81207','bg'=>'000000'),
                              27 => array('caption'=>'暗红','color'=>'a62335','bg'=>'000000'),
                              28 => array('caption'=>'深红','color'=>'95000f','bg'=>'000000'),
                              29 => array('caption'=>'灰赭','color'=>'c69374','bg'=>'000000'),
                              30 => array('caption'=>'赭色','color'=>'c67037','bg'=>'000000'),
                              31 => array('caption'=>'褐色','color'=>'994129','bg'=>'000000'),
                              32 => array('caption'=>'深褐','color'=>'582c2b','bg'=>'000000'),
                              99 => array('caption'=>'其它','color'=>'FFFFFF','bg'=>'000000'),
                            ); 
    
    /**
     * @取出其color
     */
    public static  function getColorTow($colorValue='',$flag=false) {
        if (!$colorValue) return ;
        $color = self::$color;
        $color = $color[$colorValue];
        if ($flag) {
            return $color['caption'];
        }
        return $color['caption'];
    }
    
    
    /**
     * 返回附件信息
     * @param intger $id    出售项id
     * @param string $type  类型
     * @param string $changeDir 是否转换图片路径
     * @access public
     * @return array
     */
    public static function returnImgAry($id,$type=1,$changeDir='') {
//         $attachs_table = 'car_attachs';
//         $imgArray = array();
//         include(CFG_PATH_ROOT.'conf/dbWcar.cfg.php');
//         $imgArray = $queryWcar->select('select id,object_id,sort_order,object_type
//               ,file_path,note,is_cover from '.$attachs_table
//                 .' where object_id=\''. intval($id) . '\' and object_type=\''. intval($type) .
//                  '\' and status=1 order by sort_order,id') ;
        $filters = array(
                array('object_id','=',intval($id)),
                array('object_type','=',intval($type)),
                array('status','=',1),
                );
        $orderBy = array(
                'sort_order'=>'asc',
                'id'=>'asc',
                );
         require_once API_PATH . '/interface/CarAttachInterface.class.php';
        $imgArray = CarAttachInterface::getImageInfoByPostIds('*', $filters, $orderBy);
//        print_r($imgArray);
        if ($changeDir) {
            foreach($imgArray as $key => $value) {
                if ($value['mini_file_path']=='') {
                    $value['mini_file_path']=$value['file_path'];
                }
                if (strrpos($value['file_path'],'http://')===false) {
                    $imgArray[$key]['file_path']='http://img.273.com.cn/'.$value['file_path'];
                    $imgArray[$key]['mini_file_path']='http://img.273.com.cn/'.$value['mini_file_path'];
                } else {
                    $imgArray[$key]['file_path']=$value['file_path'];
                    $imgArray[$key]['mini_file_path']=$value['mini_file_path'];
                }
    
                $imgArray[$key]['mini_file_path'] = self::formatImageUrl($imgArray[$key]['mini_file_path'], array(
                        'width' => 318,
                        'height' => 238,
                        'cut' => true,
                        'quality' => 7,
                        'mark' => 0,
                        'version' => 0,
                ));
                $imgArray[$key]['file_path'] = self::formatImageUrl($imgArray[$key]['file_path'], array(
                        'width' => 10000,
                        'height' => 0,
                        'cut' => false,
                        'quality' => 9,
                        'mark' => 0,
                        'version' => 0,
                ));
            }
        }
        return $imgArray;
    
    }
    

    function formatImageUrl($image, $option) {
        $replacement = '';
        if ($image){
            $replacement .= '_' . $option['width'];
            $replacement .= '-' . $option['height'];
            if($option['cut']) {
                $replacement .= 'c';
            }
            $replacement .= '_' . $option['quality'];
            $replacement .= '_' . $option['mark'];
            $replacement .= '_' . $option['version'];
            $pattern = "/(|_[\w-]*)(.[a-z]{3,})$/i";
            $replacement .= "\$2";
            $image = preg_replace($pattern, $replacement, $image);
        }
        return $image;
    }
    
    
    
}