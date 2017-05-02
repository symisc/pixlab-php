<?php
/* 
 * PixLab Official PHP client - https://pixlab.io. 
 * Copyright (C) 2017 Symisc Systems, SUARL http://symisc.net.
 * License: BSD-2 Clause
 */
 class Pixlab {
	private $key = null;   /* The PixLab Key */
	public $status = 200;  /* HTTP status code */
	public $json = null;   /* JSON response from the Pixlab API */
	public $raw_json = null; /* Raw JSON */
	public $blob =  null;  /* Raw (Binary image content) response from the Pixlab API */
	public $mime = '';     /* PixLab API Server MIME response */
	public $error = '';    /* Error message if $status != 200 */
	
	public function __construct($key) {
		$this->key = $key;
	}
	public function get_status(){
		return $this->status;
	}
	public function get_blob(){
		return $this->blob;
	}
	public function get_decoded_json(){
		return $this->json;
	}
	public function get_raw_json(){
		return $this->raw_json;
	}
	public function get_mime(){
		return $this->mime;
	}
	public function get_error_message(){
		return $this->error;
	}
	public function get($cmd,$param = []) {
		if(!$this->key || strlen($this->key) < 15 ){
			$this->status = 401; /* Unauthorized */
			$this->error = 'Missing/Invalid PixLab API Key';
			return false;
		}
		$cmd = basename(trim($cmd," \t/"));
		/* Build the query first */
		$param['key'] = $this->key;
		$request = "https://api.pixlab.io/$cmd?".http_build_query($param);
		/* Make the request now */
		$curl = curl_init($request);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($curl);
		if ($res === false) {
			$this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$this->error = curl_error($curl);
			curl_close($curl);
			return false;
		}
		/* Get the response mime type */
		$this->mime = trim(curl_getinfo($curl, CURLINFO_CONTENT_TYPE));
		/* Close that connection */
		curl_close($curl);
		if( $this->mime == 'application/json'){
			$this->raw_json = $res;
			$this->json = json_decode($res);
			$this->status = $this->json->status;
			if( $this->status != 200){
				$this->error = $this->json->error;
				return false;
			}
		}else{
			/* Successful blob response since error are returned in JSON format */
			$this->blob = $res;
		}
		/* All done */
		return true;
	}
	public function post($cmd,$param = [],$json_form = true) {
		if(!$this->key || strlen($this->key) < 15 ){
			$this->status = 401; /* Unauthorized */
			$this->error = 'Missing/Invalid PixLab API Key';
			return false;
		}
		$cmd = basename(trim($cmd," \t/"));
		$curl = curl_init("https://api.pixlab.io/$cmd?");
		curl_setopt($curl, CURLOPT_POST, true);
		/* Build the query first */
		$param['key'] = $this->key;
		if( $json_form ){
			$request = json_encode($param);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		}else{
			/* Standard form data */
			$request = http_build_query($param);
		}
		/* Make the request now */
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$res = curl_exec($curl);
		if ($res === false) {
			$this->status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			$this->error = curl_error($curl);
			curl_close($curl);
			return false;
		}
		/* Get the response mime type */
		$this->mime = trim(curl_getinfo($curl, CURLINFO_CONTENT_TYPE));
		/* Close that connection */
		curl_close($curl);
		if( $this->mime == 'application/json'){
			$this->raw_json = $res;
			$this->json = json_decode($res);
			$this->status = $this->json->status;
			if( $this->status != 200){
				$this->error = $this->json->error;
				return false;
			}
		}else{
			/* Successful blob response since error are returned in JSON format */
			$this->blob = $res;
		}
		/* All done */
		return true;
	}
 }
