<?php
/**
 * @desc      外网APP车源列表格式化
 * @author    daiyuancheng<daiyc@273.cn>
 * @date      14-5-5
 */
require_once API_PATH . '/interface/car/CarCommonInterface.class.php';
require_once APP_SERV . '/config/AppMsSaleVars.class.php';
class SaleListHelper {
    /**
     * 格式化详情页车源信息
     * @param $info
     * @return array  $info
     */
    public static function formatSaleInfo($info) {
        $info = self::formatCarTags($info);
        // 商业保险
        if ($info['is_safe_business'] == 2) {
            if ($info['safe_business_time'] > 0) {
                $info['busi_insur_title'] = '商险到期';
                $info['busi_insur_text'] = date('Y年m月', $info['safe_business_time']);
            } else {
                $info['busi_insur_title'] = '有无商险';
                $info['busi_insur_text'] = '有';
            }
        } else {
            $info['busi_insur_title'] = '有无商险';
            $info['busi_insur_text'] = '无';
        }
        // 强险
        if ($info['safe_force_time'] > 0) {
            $info['safe_force_title'] = '强险到期';
            $info['safe_force_text'] = date('Y年m月', $info['safe_force_time']);
        } else {
            $info['safe_force_title'] = '交强险';
            $info['safe_force_text'] = '过保';
        }
        // 年检
        if ($info['year_check_time'] > 0) {
            $info['year_check_title'] = '年检到期';
            $info['year_check_text'] = date('Y年m月', $info['year_check_time']);
        } else {
            $info['year_check_title'] = '年检情况';
            $info['year_check_text'] = '未检';
        }
        if ($info['is_look_ck'] && $info['condition_id'] <= 0) {
            $conditionDetail = array();
            if (!empty($info['condition_detail'])) {
                $conditionDetail = unserialize($info['condition_detail']);
                foreach ($conditionDetail as $key => $val) {
                    $conditionDetail[$key] = (int) $val;
                }
            }
            //免责声明相关的数据
            $conditionArr = array(
                'scratched' => !empty($info['is_frame_problem']) ? $info['is_frame_problem'] : 0,
                'soaked' => !empty($info['is_water_problem']) ? $info['is_water_problem'] : 0,
                'engine_fixed' => !empty($info['is_engine_problem']) ? $info['is_engine_problem'] : 0,
                'odometer_fixed' => !empty($info['is_kilometer_problem']) ? $info['is_kilometer_problem'] : 0,
                'scratches' => !empty($info['condition_detail']) ? $conditionDetail : array(),
            );
            $info['condition_info'] = $conditionArr;
        }
        return $info;
    }

    public static function formatCarTags($info) {
        // --------------图标-----------------
        $tags = array();
        switch ($info['mark_type']) {
            case 1 :
                $tags['xsz'] = 1;
                break;
            case 2 :
                $tags['gwkc'] = 1;
                break;
            case 3 :
                $tags['xsz'] = $tags['gwkc'] = 1;
                break;
        }
        if ($info['condition_id'] > 0) {
            $tags['ckb'] = 1;
        }
        if ($info['is_seven']) {
            $tags['sev'] = 1;
        }
        if ($info['is_look_ck']) {
            $tags['mdyyc'] = 1;
        }
        if (CarCommonInterface::isInstallment($info)) {
            $tags['dk'] = 1;
        }
        if ($tags) {
            $tagList = array();
            foreach (AppMsSaleVars::$TAG_ICONS as $key => $tag) {
                if (!empty($tags[$key])) {
                    $tagList[] = $key;
                }
            }
            if (count($tagList) === 1) {
                $info['tag_title'] = AppMsSaleVars::$TAG_ICONS[$tagList[0]]['title'];
            }
            $info['tags'] = $tagList;
        }
        // --------------图标-----------------
        return $info;
    }

    /**
     * 列表页车源信息格式化
     * @param $info
     * @return mixed
     */
    public static function formatSaleListItem($info) {
        $info = self::formatCarTags($info);
        if (!empty($info['tags'])) {
            $info['tags'] = array_slice($info['tags'], 0, 5);
        }
        $info['driving_status'] = in_array($info['mark_type'], array(1, 3)) ? 1 : 0;
        $info['advisor_status'] = in_array($info['mark_type'], array(2, 3)) ? 1 : 0;
        if (strpos($info['value'], 'http://') === 0) {
            $path = str_replace('http://img.273.com.cn/', '', $info['cover_photo']);
            $info['cover_photo'] = str_replace('_120-90_6_0_0', '', $path);
        }
        return $info;
    }

    public static function formatSaleList($list) {
        foreach ($list as $key => $info) {
            $list[$key] = self::listFieldFilter(self::formatSaleListItem($info));
        }
        return $list;
    }

    /**
     * 列表页字段过滤
     * @param $info
     * @return array
     */
    public static function listFieldFilter($info) {
        $data = array(
            'id'          => $info['id'],
            'create_time' => $info['create_time'],
            'update_time' => $info['update_time'],
            'cover_photo' => $info['cover_photo'],
            'title'       => $info['title'],
            'kilometer'   => $info['kilometer'],
            'card_time'   => $info['card_time'],
            'price'       => $info['price'],
            'follow_user_id' => $info['follow_user_id'],
            'ext_phone'   => $info['ext_phone'],
            'seller_name' => $info['seller_name'],
            'telephone'   => $info['telephone'],
            'ip'          => $info['ip'],
            'mark_type'   => $info['mark_type'],
            'tags'        => empty($info['tags']) ? array() : $info['tags'],
            'tag_title'   => isset($info['tag_title']) ? $info['tag_title'] : '',
        );
        return $data;
    }
}
