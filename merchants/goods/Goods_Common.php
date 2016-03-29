<?php
/**
 * 提供给商品模块的工具类
 */
defined('IN_SIMPHP') or die('Access Denied');

class Goods_Common {
    
    /**
     * 获得指定分类下的子分类的数组
     *
     * @access  public
     * @param   int     $cat_id     分类的ID
     * @param   int     $selected   当前选中分类的ID
     * @param   boolean $re_type    返回的类型: 值为真时返回下拉列表,否则返回数组
     * @param   int     $level      限定返回的级数。为0时返回所有级数
     * @param   int     $is_show_all 如果为true显示所有分类，如果为false隐藏不可见分类。
     * @param   array   $exclude_ids 排除cat_id集合，将会排除$exclude_ids包括的cat_id，及对应cat_id下级的所有子分类id；默认排除“原产地”分类id
     * @return  mix
     */
    static function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true, $exclude_ids = array(ORIGIN_PLACE_TOP_CAT_ID))
    {
        $merchant_id = $GLOBALS['user']->uid;
        static $res = NULL;
        $data = false;
        if ($res === NULL)
        {
            if ($data === false)
            {
                $table = "shp_category";
                $sql = "SELECT c.cat_id, c.cat_name, c.measure_unit, c.parent_id, c.is_show, c.show_in_nav, c.grade, c.sort_order, COUNT(s.cat_id) AS has_children ".
                    'FROM ' . $table . " AS c ".
                    "LEFT JOIN " . $table . " AS s ON s.parent_id=c.cat_id where c.merchant_id = '$merchant_id' ". 
                    "GROUP BY c.cat_id ".
                    'ORDER BY c.parent_id, c.sort_order ASC';
                $res = D()->query($sql)->fetch_array_all();
            }
            else
            {
                $res = $data;
            }
        }
    
        if (empty($res) == true)
        {
            return $re_type ? '' : array();
        }
        $options = self::cat_options($cat_id, $res); // 获得指定分类下的子分类的数组
        $children_level = 99999; //大于这个分类的将被删除
        if ($is_show_all == false)
        {
            foreach ($options as $key => $val)
            {
                if ($val['level'] > $children_level)
                {
                    unset($options[$key]);
                }
                else
                {
                    if ($val['is_show'] == 0)
                    {
                        unset($options[$key]);
                        if ($children_level > $val['level'])
                        {
                            $children_level = $val['level']; //标记一下，这样子分类也能删除
                        }
                    }
                    else
                    {
                        $children_level = 99999; //恢复初始值
                    }
                }
            }
        }
    
        //Add by Gavin
        $children_level = 99999; //大于这个分类的将被删除
        if (!empty($exclude_ids) && is_array($exclude_ids))
        {
            foreach ($options as $key => $val)
            {
                if ($val['level'] > $children_level)
                {
                    unset($options[$key]);
                }
                else
                {
                    if (in_array($val['cat_id'], $exclude_ids))
                    {
                        unset($options[$key]);
                        if ($children_level > $val['level'])
                        {
                            $children_level = $val['level']; //标记一下，这样子分类也能删除
                        }
                    }
                    else
                    {
                        $children_level = 99999; //恢复初始值
                    }
                }
            }
        }
    
        /* 截取到指定的缩减级别 */
        if ($level > 0)
        {
            if ($cat_id == 0)
            {
                $end_level = $level;
            }
            else
            {
                $first_item = reset($options); // 获取第一个元素
                $end_level  = $first_item['level'] + $level;
            }
    
            /* 保留level小于end_level的部分 */
            foreach ($options AS $key => $val)
            {
                if ($val['level'] >= $end_level)
                {
                    unset($options[$key]);
                }
            }
        }
    
        if ($re_type == true)
        {
            return $options;
        }
    }
    
    static function build_options($options, $selected = 0){
        if(!$options || count($options) == 0){
            return '';
        }
        $select = '';
        foreach ($options AS $var)
        {
            $select .= '<option value="' . $var['cat_id'] . '" ';
            $select .= ($selected == $var['cat_id']) ? "selected='ture'" : '';
            $select .= '>';
            if ($var['level'] > 0)
            {
                $select .= str_repeat('&nbsp;', $var['level'] * 4);
            }
            $select .= htmlspecialchars(addslashes($var['cat_name']), ENT_QUOTES) . '</option>';
        }
        return $select;
    }
    
    /**
     * 过滤和排序所有分类，返回一个带有缩进级别的数组
     *
     * @access  private
     * @param   int     $cat_id     上级分类ID
     * @param   array   $arr        含有所有分类的数组
     * @param   int     $level      级别
     * @return  void
     */
    static function cat_options($spec_cat_id, $arr)
    {
        static $cat_options = array();
    
        if (isset($cat_options[$spec_cat_id]))
        {
            return $cat_options[$spec_cat_id];
        }
    
        if (!isset($cat_options[0]))
        {
            $level = $last_cat_id = 0;
            $options = $cat_id_array = $level_array = array();
            $data = false;
            if ($data === false)
            {
                while (!empty($arr))
                {
                    foreach ($arr AS $key => $value)
                    {
                        $cat_id = $value['cat_id'];
                        if ($level == 0 && $last_cat_id == 0)
                        {
                            if ($value['parent_id'] > 0)
                            {
                                break;
                            }
    
                            $options[$cat_id]          = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id']    = $cat_id;
                            $options[$cat_id]['name']  = $value['cat_name'];
                            unset($arr[$key]);
    
                            if ($value['has_children'] == 0)
                            {
                                continue;
                            }
                            $last_cat_id  = $cat_id;
                            $cat_id_array = array($cat_id);
                            $level_array[$last_cat_id] = ++$level;
                            continue;
                        }
    
                        if ($value['parent_id'] == $last_cat_id)
                        {
                            $options[$cat_id]          = $value;
                            $options[$cat_id]['level'] = $level;
                            $options[$cat_id]['id']    = $cat_id;
                            $options[$cat_id]['name']  = $value['cat_name'];
                            unset($arr[$key]);
    
                            if ($value['has_children'] > 0)
                            {
                                if (end($cat_id_array) != $last_cat_id)
                                {
                                    $cat_id_array[] = $last_cat_id;
                                }
                                $last_cat_id    = $cat_id;
                                $cat_id_array[] = $cat_id;
                                $level_array[$last_cat_id] = ++$level;
                            }
                        }
                        elseif ($value['parent_id'] > $last_cat_id)
                        {
                            break;
                        }
                    }
    
                    $count = count($cat_id_array);
                    if ($count > 1)
                    {
                        $last_cat_id = array_pop($cat_id_array);
                    }
                    elseif ($count == 1)
                    {
                        if ($last_cat_id != end($cat_id_array))
                        {
                            $last_cat_id = end($cat_id_array);
                        }
                        else
                        {
                            $level = 0;
                            $last_cat_id = 0;
                            $cat_id_array = array();
                            continue;
                        }
                    }
    
                    if ($last_cat_id && isset($level_array[$last_cat_id]))
                    {
                        $level = $level_array[$last_cat_id];
                    }
                    else
                    {
                        $level = 0;
                    }
                }
            }
            else
            {
                $options = $data;
            }
            $cat_options[0] = $options;
        }
        else
        {
            $options = $cat_options[0];
        }
    
        if (!$spec_cat_id)
        {
            return $options;
        }
        else
        {
            if (empty($options[$spec_cat_id]))
            {
                return array();
            }
    
            $spec_cat_id_level = $options[$spec_cat_id]['level'];
    
            foreach ($options AS $key => $value)
            {
                if ($key != $spec_cat_id)
                {
                    unset($options[$key]);
                }
                else
                {
                    break;
                }
            }
    
            $spec_cat_id_array = array();
            foreach ($options AS $key => $value)
            {
                if (($spec_cat_id_level == $value['level'] && $value['cat_id'] != $spec_cat_id) ||
                    ($spec_cat_id_level > $value['level']))
                {
                    break;
                }
                else
                {
                    $spec_cat_id_array[$key] = $value;
                }
            }
            $cat_options[$spec_cat_id] = $spec_cat_id_array;
            return $spec_cat_id_array;
        }
    }
    
    /**
            生成商品规格HTML
     */
    static function generateSpecifiDropdown($specifis){
        $html = "";
        foreach ($specifis as $cat){
            $html .= "<li data-cat='$cat[cat_id]'>$cat[cat_name]</li>";
        }
        return $html;
    }
    
    /**
     * 生成商品编辑时看到的根据商品属性展示的商品库存信息
     * @param unknown $specifis
     * @return string
     */
    static function generateSpecifiTable($specifis){
        if(!$specifis || count($specifis) == 0){
            return '';
        }
        $TR_HD = "<tr class='firsttr'>";
        $count = 0;
        foreach ($specifis as $item){
            $count ++;
            $TR_HD .= "<td data-cat=".$item['cat_id'].">".$item['cat_name']."</td>";
        }
        $TR_HD .= "<td>市场价</td>";
        $TR_HD .= "<td>售价</td>";
        $TR_HD .= "<td>供货价</td>";
        $TR_HD .= "<td>成本价</td>";
        $TR_HD .= "<td>库存</td></tr>";
        foreach ($specifis[0]['attrs'] as $attr){
            $TR_HD .= "<tr class='attr_data'>";
            for($i = 1; $i <= $count; $i++){
                $TR_HD .= "<td class='attrcls' data-attr='".$attr['attr'.$i.'_id']."'>".$attr['attr'.$i.'_value']."</td>";
            }
            $TR_HD .= "<td><input type='text' class='attr_market_price' data-type='money' value='$attr[market_price]' required ></td>";
            $TR_HD .= "<td><input type='text' class='attr_shop_price' data-type='money' value='$attr[shop_price]' required ></td>";
            $TR_HD .= "<td><input type='text' class='attr_income_price' data-type='money' value='$attr[income_price]' required ></td>";
            $TR_HD .= "<td><input type='text' class='attr_cost_price' data-type='money' value='$attr[cost_price]' required ></td>";
            $TR_HD .= "<td><input type='text' data-type='positive' class='attr_goods_number' value='$attr[goods_number]' required ></td></tr>";
        }
        return $TR_HD;   
    }
    
    /**
     * full the img path
     * @param string $img_path
     * @return string
     */
    static function imgurl($img_path) {
        static $urlpre;
        if (!isset($urlpre)) $urlpre = C('env.site.merchant');
        $img_path = Media::path($img_path);
        return preg_match('/^http(s?):\/\//i', $img_path) ? $img_path : ($urlpre.$img_path);
    }
}