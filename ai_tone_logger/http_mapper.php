<?php

	/*
		v0.2
		HTTP Mapper
		@Wrtten By ELvis
	*/


	class httpMapper
	{
		public $instance;
		public $url;

		public function __construct($url,$options){
			$option;

			if(!empty($options)){
				$option = "?";
				$c = count($options);
				$k=1;

				foreach ($options as $key => $value) {
					$key=urlencode($key);
					$value = urlencode($value);
					$option.="${key}=${value}";
					if($k!=$c) $option.="&";
					$k++;
				}
			}

			$this->instance = curl_init("${url}${option}");
			$this->url = $url.$option;
		}

		public function __destruct(){
    		curl_close($this->instance);
		}
		public function setup()
		{
			curl_setopt($this->instance, CURLOPT_URL, $this->url);
    		curl_setopt($this->instance, CURLOPT_RETURNTRANSFER, true);
		}

		public function auth(array $header)
		{

			curl_setopt($this->instance, CURLOPT_HTTPHEADER, $header);
    		//for debug only!
    		curl_setopt($this->instance, CURLOPT_SSL_VERIFYHOST, false);
    		curl_setopt($this->instance, CURLOPT_SSL_VERIFYPEER, false);

		}

		public function get_response(String $reponse="json")
		{
			$resp = curl_exec($this->instance);

			if($reponse=='array') return json_decode($resp,true);
			else if($response=='object') return json_decode($resp);
			else return $resp;
		}

	}