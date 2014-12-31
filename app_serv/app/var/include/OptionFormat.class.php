<?php
/**
 * @package              v3
 * @subpackage           
 * @author               $Author:   weihongfeng$
 * @file                 $HeadURL$
 * @version              $Rev$
 * @lastChangeBy         $LastChangedBy$
 * @lastmodified         $LastChangedDate$
 * @copyright            Copyright (c) 2012, www.273.cn
 */

class OptionFormat {

    public static function formatBrands($data, $len=0) {                    
        $ret = array();                                                        
        if($data) {                                                            
            if (is_array(array_slice($data, 0, 1))) {                          
                $count = count($data);
                if ($len && ($len < $count)) {
                    $count = $len;
                }
                for($i = 0; $i < $count; $i++) {                               
                    $data[$i]['initial'] = substr($data[$i]['spell'], 0 ,1);
                    $data[$i]['text'] = $data[$i]['description_chinese'];
                    unset($data[$i]['description_chinese']);
                    $ret[] = $data[$i];
                }                                                              
            } else {                                                           
                $data['initial'] = substr($data['spell'], 0 ,1);
                $data['text'] = $data[$i]['description_chinese'];
                unset($data['description_chinese']);
                return array($data);
            }                                                                  
        }                                                                      
        return $ret;                                                           
    }                                                                          

    public static function formatBrandsV2($data, $len=0, $type=0) {                    
        $ret = array();                                                        
        if($data) {                                                            
            if (is_array(array_slice($data, 0, 1))) {                          
                $count = count($data);
                if ($len && ($len < $count)) {
                    $count = $len;
                }
                /*
                $temp = array();
                for ($i = 0; $i < $count; $i++) {
                    $temp[$data[$i]['name']]++;
                }
                */
                    
                    
                for ($i = 0; $i < $count; $i++) {
                    $initial = strtoupper(mb_substr($data[$i]['full_spell'], 0 ,1, 'utf-8'));
                    $data[$i]['full_spell'] = strtolower($data[$i]['full_spell']);
                    $data[$i]['initial'] = $initial ? $initial : '';
                    $data[$i]['text'] = $data[$i]['name'] ? $data[$i]['name'] : '';
                    //if (isset($temp[$data[$i]['name']]) && $temp[$data[$i]['name']]>1 && $data[$i]['import_id'] == 1) {
                    if ($data[$i]['import_id'] == 1) {
                        $data[$i]['text'] .= '（进口）';
                    } 
                    $data[$i]['path'] = $data[$i]['url_path'] ? $data[$i]['url_path'] :'';
                    unset($data[$i]['name']);
                    unset($data[$i]['alias_name']);
                    unset($data[$i]['english_name']);
                    unset($data[$i]['short_spell']);
                    unset($data[$i]['nation_id']);
                    unset($data[$i]['display_order']);
                    unset($data[$i]['show_status']);
                    unset($data[$i]['status']);
                    unset($data[$i]['make_code']);
                    unset($data[$i]['liyang_name']);
                    unset($data[$i]['import_source']);
                    unset($data[$i]['url_path']);
                    unset($data[$i]['create_time']);
                    unset($data[$i]['start_year']);
                    unset($data[$i]['last_year']);
                    unset($data[$i]['min_price']);
                    unset($data[$i]['max_price']);
                    unset($data[$i]['family_code']);
                    unset($data[$i]['vehicle_type']);
                    $ret[] = $data[$i];
                }                                                              
            } else {
                $initial = substr($data['full_spell'], 0 ,1);
                $data['initial'] = $initial ? $initial : '';
                $data['full_spell'] = strtolower($data['full_spell']);
                $data['text'] = $data[$i]['name'] ? $data[$i]['name'] : '';
                $data['path'] = $data['url_path'] ? $data['url_path'] : '';
                unset($data['name']);
                unset($data['alias_name']);
                unset($data['english_name']);
                unset($data['short_spell']);
                unset($data['nation_id']);
                unset($data['display_order']);
                unset($data['show_status']);
                unset($data['status']);
                unset($data['make_code']);
                unset($data['liyang_name']);
                unset($data['import_source']);
                unset($data['url_path']);
                unset($data['create_time']);
                unset($data['start_year']);
                unset($data['last_year']);
                unset($data['min_price']);
                unset($data['max_price']);
                unset($data['family_code']);
                unset($data['import_id']);
                unset($data['vehicle_type']);
                return array($data);
            }                                                                  
        }                                                                      
        return $ret;                                                           
    }                                                                          
}
