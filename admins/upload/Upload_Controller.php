<?php
defined('IN_SIMPHP') or die('Access Denied');

class Upload_Controller extends Controller {
	
  private $_uproot_dir = '/a/';
  
  /**
   * hook init
   * @param string $action
   * @param Request $request
   * @param Response $response
   */
  public function init($action, Request $request, Response $response) {
    $this->_uproot_dir = Config::get('env.picsavedir');
  }
  
	public function index(Request $request, Response $response){
		$ref_id = isset($_GET['ref_id']) ? intval($_GET['ref_id']):0;
		$from   = isset($_GET['from']) ? treat_input_str($_GET['from']):'';
		
		if($from=='content'){
			$this->content($ref_id);
		}else{
			
		}
	}
	
	private function content($ref_id) {
		$ext='jpg,jpeg,gif,png';
		$files = isset($_FILES['filedata']) ? $_FILES['filedata']:null;
		$attachment = upload($files,false,$ext,'content',$error);
		if($attachment!=''){
			$data = [
				'uploadfrom'=>'content',
				'ref_id'=>$ref_id,
				'path'=>$attachment
			];
			//Upload_Model::saveUpload($data);
			echo "{'err':'','msg':'".$attachment."'}";
			exit();
		}
		echo"{'err':'{$error}','msg':''}";
		exit();
	}
	
	/**
	 * 简单上传一个文件，然后用标准JSON格式返回文件的地址，不记录数据库数据
	 * @param Request $request
	 * @param Response $response
	 */
	public function upfile(Request $request, Response $response) {
	  if ($request->has_files()) {
	    $upfile = $request->files('upfile');
	    $dbsave = $request->get('dbsave', 0);
	    
	    $extpart  = strtolower(strrchr($upfile['name'],'.'));
	    $fileext  = substr($extpart, 1);
	    $filetype = 'attach';
	    if ('swf'==$fileext) {
	      $filetype = 'flash';
	    }
	    elseif (in_array($fileext, array('jpg','jpeg','png','gif'))) {
	      $filetype = 'pic';
	    }
	    elseif ('apk'==$fileext) {
	      $filetype = 'android';
	    }
	    elseif ('ipa'==$fileext) {
	      $filetype = 'ios';
	    }
	    elseif ('xap'==$fileext || 'cab'==$fileext) {
	      $filetype = 'wp';
	    }elseif (in_array($fileext, array('mp3'))){
	      $filetype = 'audio';
	    }
	  
	    //~ create directory
	    $targetfilecode = date('d_His').'_'.randstr();
	    $targetfile = $targetfilecode.$extpart;
	    $targetdir  = ltrim($this->_uproot_dir,'/')."{$filetype}/".date('Ym').'/';
	    if(!is_dir($targetdir)) {
	      mkdirs($targetdir, 0777, FALSE);
	    }
	  
	    //~ move upload file to target dir
	    $filepath = $targetdir . $targetfile;
	    move_uploaded_file($upfile['tmp_name'], $filepath);
	    chmod($filepath, 0644);
	  
	    if (file_exists($filepath)) {
	      $mid    = 0;
	      $width  = 0;
	      $height = 0;
	      $size   = filesize($filepath);
	      if ($filetype=='pic') {
	        list($width,$height,$type,$attr) = getimagesize($filepath);
	      }
	      
	      $filepath_site = C('env.contextpath','/').$filepath; //要补上网站的根路径
	      if ($dbsave) {
	        $data = [
  	        'mtype'    => $filetype,
  	        'filesize' => $size,
  	        'path'     => $filepath_site
	        ];
	        $mid = Media::save($data);
	      }
	      $response->sendJSON(['flag'  => 'OK',
	                           'msg'   => 'upload file success',
	                           'mid'   => $mid, 
	                           'path'  => $filepath_site,
	                           'type'  => $filetype,
	                           'width' => $width,
	                           'height'=> $height,
	                           'size'  => $size]);
	    }
	    $response->sendJSON(['flag'=>'ERR', 'msg'=>'upload file error']);
	  }
	  $response->sendJSON(['flag'=>'ERR_NOFILES', 'msg'=>'no files upload']);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}
