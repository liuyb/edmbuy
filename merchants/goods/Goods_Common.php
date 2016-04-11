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
    static function cat_list($cat_id = 0, $selected = 0, $re_type = true, $level = 0, $is_show_all = true, $exclude_ids = array(ORIGIN_PLACE_TOP_CAT_ID)){
        $merchant_id = $GLOBALS['user']->uid;
        static $res = NULL;
        $data = false;
        if ($res === NULL)
        {
            if ($data === false)
            {
                $table = "shp_category";
                $sql = "SELECT c.cat_id, c.cat_name, c.parent_id from $table c where c.merchant_id = '$merchant_id' and is_delete = 0 ORDER BY c.parent_id, c.sort_order ";
                $res = D()->query($sql)->fetch_array_all();
            }
            else
            {
                $res = $data;
            }
        }
        $options = self::cat_options($cat_id, $res); // 获得指定分类下的子分类的数组
        return $options;
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
    static function cat_options($spec_cat_id, $arr){
        if(!$arr || count($arr) == 0){
            return [];
        }
        $cat_map = [];
        foreach ($arr as $cs){
            $parent_id = $cs['parent_id'];
            $cat_id = $cs['cat_id'];
            $cat_name = $cs['cat_name'];
            if(!$parent_id){
                $cs['level'] = 0;
                $cat_map[$cat_id] = $cs;
            }else{
                if(!isset($cat_map[$parent_id]) || !$cat_map[$parent_id]){//目前只支持两层
                    continue;
                }
                if(!isset($cat_map[$parent_id]['child']) || !$cat_map[$parent_id]['child']){
                    $cat_map[$parent_id]['child'] = [];
                }
                $cs['level'] = 1;
                array_push($cat_map[$parent_id]['child'], $cs);
            }
        }
        $options = [];
        foreach ($cat_map as $catop){
            array_push($options, array("cat_id" => $catop['cat_id'], "cat_name" => $catop['cat_name'], "level" => $catop['level']));
            if(isset($catop['child']) && $catop['child']){
                foreach ($catop['child'] as $cl){
                    array_push($options, array("cat_id" => $cl['cat_id'], "cat_name" => $cl['cat_name'], "level" => $cl['level']));
                }
            }
        }
        return $options;
    }
    /**
     * 构建分类下拉列表
     * @param unknown $options
     * @param number $selected
     */
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
     * 构建运费下拉列表
     * @param unknown $options
     * @param number $selected
     * @return string
     */
    static function build_ship_options($options, $selected = 0){
        if(!$options || count($options) == 0){
            return '';
        }
        $select = '';
        foreach ($options AS $var)
        {
            $select .= '<option value="' . $var['sp_id'] . '" ';
            $select .= ($selected == $var['sp_id']) ? "selected='ture'" : '';
            $select .= '>';
            $select .= htmlspecialchars(addslashes($var['tpl_name']), ENT_QUOTES) . '</option>';
        }
        return $select;
    }
    
    /**
            生成商品规格HTML
     */
    static function generateSpecifiDropdown($specifis){
        $html = "";
        foreach ($specifis as $cat){
            $html .= "<li data-cat='$cat[cat_id]'>".htmlspecialchars($cat['cat_name'])."</li>";
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
            $TR_HD .= "<th data-cat=".$item['cat_id'].">".htmlspecialchars($item['cat_name'])."</th>";
        }
        $TR_HD .= "<th>市场价</th>";
        $TR_HD .= "<th>售价</th>";
        $TR_HD .= "<th>供货价</th>";
        $TR_HD .= "<th>成本价</th>";
        $TR_HD .= "<th>库存</th></tr>";
        foreach ($specifis[0]['attrs'] as $attr){
            $TR_HD .= "<tr class='attr_data'>";
            for($i = 1; $i <= $count; $i++){
                $TR_HD .= "<td class='attrcls' data-attr='".$attr['attr'.$i.'_id']."'>".htmlspecialchars($attr['attr'.$i.'_value'])."</td>";
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