<?php
/**
 * 同步微信素材作业
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
class SyncWxMaterialJob extends CronJob {
	
	/**
	 * 微信素材列表返回素材的最大数量(微信接口限制为1~20)
	 * @var constant
	 */
	const MAX_COUNT = 20;
	
	/**
	 * 素材表
	 * @var constant
	 */
	const TABLE = 'material_wx';
	
	/**
	 * 素材类型集合
	 * @var array
	 */
	private static $type_set = ['news','image','video','voice'];
	
	/**
	 * 作业主入口
	*/
	public function main($argc, $argv) {
	
		$wx = new Weixin();
		$total = $wx->getMaterialCount();
	
		//按素材类型逐个获取列表
		foreach (self::$type_set AS $type) {
	
			$type_total = $total[$type.'_count'];
			$offset = 0;
			$count  = $type_total > self::MAX_COUNT ? self::MAX_COUNT : $type_total;
			$this->log("Type={$type}...");
	
			do {
	
				$ret = $wx->getMaterialList($type, $offset, $count);
				if (empty($ret) || $ret['item_count']==0) {
					break;
				}
				$curr_latest_uptime = $ret['item'][0]['update_time'];
				if ($curr_latest_uptime == $this->check_latest_uptime($type)) {
					break; //如果当前返回的最新时间戳跟本地的最大时间戳一样，则不需要处理了(仅在第一次检查)
				}
	
				// 循环获取各个素材
				foreach ($ret['item'] AS $it) {
	
					$data_base = [
							'type'           => $type,
							'media_id'       => $it['media_id'],
							'show_cover_pic' => 0,
							'update_time'    => $it['update_time'],
							'is_multiple'    => 0,
							'add_time'       => simphp_time()
					];
	
					if ($type == 'news') { //图文消息相比其他消息复杂特殊
	
						if (!empty($it['content']) && !empty($it['content']['news_item'])) {
	
							$multi_count = count($it['content']['news_item']);
	
							if ($multi_count > 1) { //多图文信息
								$data_base['is_multiple'] = 1;
							}
	
							foreach ($it['content']['news_item'] AS $cont) {
								$sign = $this->media_sign($type, $it['media_id'], $cont['url']);
								if (!$this->can_insert($sign) && !$this->need_update($sign, $it['update_time'])) {
									break 3; // 记录存在且时间未更新，则不需要处理了，直接跳出主循环(在这里是3层)
								}
	
								$data = [];
								$data['media_sign']         = $sign;
								$data['title']              = $cont['title'];
								$data['author']             = $cont['author'];
								$data['digest']             = $cont['digest'];
								$data['content']            = $cont['content'];
								$data['content_source_url'] = $cont['content_source_url'];
								$data['url']                = $cont['url'];
								$data['thumb_media_id']     = $cont['thumb_media_id'];
								$data['show_cover_pic']     = $cont['show_cover_pic'];
								$data = array_merge($data_base, $data);
								$nid = $this->save_material($data);
	
								// 下载图片并保存
								if ($nid) {
									$outfile = '';
									if ($wx->getMaterial($cont['thumb_media_id'], $outfile)) {
										if ($outfile) {
											$this->save_thumb_media_url($nid, $outfile);
										}
									}
								}
	
							}
	
						}
	
					}
					else {
						$sign = $this->media_sign($type, $it['media_id'], $it['url']);
						if (!$this->can_insert($sign) && !$this->need_update($sign, $it['update_time'])) {
							break 2; // 记录存在且时间未更新，则不需要处理了，直接跳出主循环(在这里是2层)
						}
	
						$data = [];
						$data['media_sign']  = $sign;
						$data['name']        = $it['name'];
						$data['url']         = $it['url'];
						$data = array_merge($data_base, $data);
						$this->save_material($data);
					}
	
				} //END foreach ($ret['item'] AS $it)
	
				$offset += $ret['item_count']; //改写偏移
			} while($offset < $type_total);
	
	
		} //END foreach (self::$type_set AS $type)
	
	}
	
	/**
	 * 插入新素材内容(有检查是否需要插入)
	 */
	private function save_material($data) {
		$nid = 0;
	
		if ($this->can_insert($data['media_sign'])) { //可以新插入
	
			$nid = D()->insert(self::TABLE, $data);
			if ($nid) {
				$this->log("New insert: nid={$nid},media_sign={$data['media_sign']}");
			}
	
		}
		else { //只能更新
	
			$sign = $data['media_sign'];
			unset($data['type'],$data['media_id'],$data['media_sign'],$data['add_time']);
			if ( $nid = $this->need_update($sign, $data['update_time']) ) {
				D()->update(self::TABLE, $data, ['media_sign' => $sign]);
				$this->log("Update: nid={$nid},media_sign={$sign}");
			}
	
		}
	
		return $nid;
	}
	
	/**
	 * 保存图文素材封面图片地址
	 */
	private function save_thumb_media_url($nid, $media_url) {
		$effcnt = D()->update(self::TABLE, ['thumb_media_url' => $media_url], ['nid' => $nid]);
		if ($effcnt) {
			$this->log("Update thumb_media_url: thumb_media_url={$media_url}");
			return true;
		}
		return false;
	}
	
	/**
	 * 检查本地微信最新更新时间
	 */
	private function check_latest_uptime($type) {
		$res = D()->from(self::TABLE)->where(['type'=>$type])->select("MAX(update_time) AS max_uptime")->result();
		$res = $res ? intval($res) : 0;
		$this->log('Local max uptime: '.$res);
		return $res;
	}
	
	/**
	 * 通过检查素材签名确定是否能新插入
	 */
	private function can_insert($media_sign) {
		$nid  = D()->from(self::TABLE)->where(['media_sign' => $media_sign])->select('nid')->result();
		return empty($nid) ? TRUE : FALSE;
	}
	
	/**
	 * 通过检查素材签名和更新时间确定是否需要更新
	 */
	private function need_update($media_sign, $update_time) {
		$nid  = D()->from(self::TABLE)->where("media_sign='{$media_sign}' AND update_time<{$update_time}")->select('nid')->result();
		return $nid ?  : FALSE;
	}
	
	/**
	 * 对素材作唯一性签名
	 */
	private function media_sign($type, $media_id, $url) {
		return sha1($type.$media_id.$url);
	}
	
}
 
/*----- END FILE: SyncWxMaterialJob.php -----*/