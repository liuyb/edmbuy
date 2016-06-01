<?php
/**
 * 用Curl库实现的ApiIO类
 *
 * @author Gavin<laigw.vip@gmail.com>
 */
if (!function_exists('curl_init')) {
	throw new Exception('PHP ApiRequest API requires the CUrl PHP extension');
}

class ApiIOCurl extends ApiIO {
	
	/**
	 * 执行一个ApiRequest请求
	 * @param  ApiRequest $request
	 * @return ApiRequest $request
	 */
	public function makeRequest(ApiRequest $request) {
		
		if (empty($request->url)) {
			throw new ApiRequestException('ApiRequest->url required setting');
		}
		
		$cookie_string = $request->makeCookieString($request->cookies);
		if ($request->sendfmt=='json') {
			$query_string = $request->makeJsonData($request->params);
		}
		elseif ($request->sendfmt=='xml') {
			$query_string = $request->makeXmlData($request->params);
		}
		else {
			$query_string  = $request->makeQueryString($request->params, 2);
		}
		
		if ($request->packto) {
			$query_string = 'd='.base64_encode($query_string);
		}

		$ch = curl_init();

		if ('get' == strtolower($request->method)) {
			curl_setopt($ch, CURLOPT_URL,  $request->url.(FALSE===strrpos($request->url, '?')?'?':'&').$query_string);
		}
		else {			
			curl_setopt($ch, CURLOPT_URL,  $request->url);
			curl_setopt($ch, CURLOPT_POST, 1);
			
			if (!empty($request->files)) { //with files to post
				//curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT'); //for debug PUT request
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: multipart/form-data"));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $request->mergeQueryParams($request->params,$request->files));
			}
			else { //no files, just ordinary variables to post
				if ($request->sendfmt=='json') {
					curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json","Content-Length: ".strlen($query_string)));
				}
				elseif ($request->sendfmt=='xml') {
					curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml","Content-Length: ".strlen($query_string)));
				}
				else {
					curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded","Content-Length: ".strlen($query_string)));
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query_string);
			}
		}
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, TRUE); //may set to FALSE to debug DNS 
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $request->timeout_connect);
		curl_setopt($ch, CURLOPT_TIMEOUT,        $request->timeout);
		curl_setopt($ch, CURLOPT_HEADER,         FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		
		// check whether outfile
		$fp = NULL;
		if (''!==$request->outfile && 'STDOUT'!==$request->outfile) {
		  $dir = dirname($request->outfile);
		  if (!is_dir($dir)) {
		    $mode = 0755;
		    mkdir($dir, $mode, TRUE);
		    chmod($dir, $mode);
		  }
		  $fp = @fopen($request->outfile, 'w');
		  if ($fp) {
		    curl_setopt($ch, CURLOPT_FILE, $fp);
		  }
		}

		// disable 100-continue
		curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Expect:'));
		
		// set ipv4 as default dns resolving
    if(defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')){
		  curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4); //php 5.3+ and curl 7.10.8+ are required
		}

		if (!empty($cookie_string)) {
			curl_setopt($ch, CURLOPT_COOKIE, $cookie_string);
		}

		if ('https' == strtolower($request->protocol)) {
			curl_setopt($ch, CURLOPT_SSLVERSION, 1); //set to CURL_SSLVERSION_TLSv1
			
			if (empty($request->sslcert) && empty($request->cafile)) {
			  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			}
			else {
			  //1 to check the existence of a common name in the SSL peer certificate. 
			  //2 to check the existence of a common name and also verify that it matches the hostname provided
			  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
			  
			  if (!empty($request->sslcert)) {
			    curl_setopt($ch, CURLOPT_SSLCERT, $request->sslcert['cert_file']);
			    curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $request->sslcert['cert_passwd']);
			    curl_setopt($ch, CURLOPT_SSLCERTTYPE, $request->sslcert['cert_type']);
			  }
			  
			  if (!empty($request->cafile)) {
			    //对认证证书来源的检查，FALSE表示阻止对证书的合法性的检查;1表示需要设置CURLOPT_CAINFO
			    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			    curl_setopt($ch, CURLOPT_CAINFO, $caFile);
			  }
			  else {
			    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			  }
			}
		}
		
		$ret = curl_exec($ch);
		$err = curl_error($ch);

		$response = array('flag'=>1, 'ret'=>$ret);
		if (FALSE === $ret || !empty($err)) {
			$errno = curl_errno($ch);
			$code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			$response = array(
					'flag'  => 0,
					'ret'   => $err,
					'errno' => $errno,
					'httpcode'=> $code,
			);
		}
		curl_close($ch);
		
		if (is_resource($fp)) {
		  fflush($fp);
		  fclose($fp);
		}
		
		$request->setResponse($response);
		
		return $response;
	}
	
}



/*--- END FILE: class.ApiIOCurl.php ---*/