<?php

	class PagifyIO {
		public $options = array("hostName" => "127.0.0.1",
									"port" => 3000,
									"path" => "",
									"method" => null,
									"acceptType" => ""
								);
		
		private $appId = "";
		private $appSecret = "";
		private $requestLength = 0;
		private $contentType = "application/json";
		private $currentDate = "";
		
		public function __construct($id, $secret) {
			$this->appId = $id;
			$this->appSecret = $secret;
		}
		
		public function listTemplates() {
			$this->options["path"] = "/api/templates";
			$this->options["method"] = "GET";
			$this->options["acceptType"] = "application/json";
			return json_decode($this->request(""));
		}
		
		public function createTemplate($name) {
		    if ($name == null || $name == "")
                throw "Please supply a name";
			$this->options["path"] = "/api/templates";
			$this->options["method"] = "POST";
			$this->options["acceptType"] = "application/json";
			$requestData = array("template_name" => $name);
            return json_decode($this->request(json_encode($requestData)));
		}
		
		public function editTemplate($templateID) {
			$this->options["path"] = "/api/templates/". $templateID . "/edit";
			$this->options["method"] = "GET";
			$this->options["acceptType"] = "application/json";
			return json_decode($this->request(""));
		}
		
		public function deleteTemplate($templateID) {
			$this->options["path"] = "/api/templates/". $templateID;
			$this->options["method"] = "DELETE";
			$this->options["acceptType"] = "application/json";
			return json_decode($this->request(""));
		}
		
		public function generatePDF($templateID, $data) {
		    if ($templateID == null || $templateID == "")
                throw "Please supply a templateID";
			$this->options["path"] = "/api/templates/". $templateID . "/generate_pf";
			$this->options["method"] = "POST";
			$this->options["acceptType"] = "application/json";
			$requestData = array("data" => $data);
			$tmp = $this->request(json_encode($requestData));
			try {
				$tmp1 = json_decode($tmp);
			} catch(Exception $e) {
				return $tmp;
			}
			return $tmp1;
		}
		
		private function request($data) {
			$this->requestLength = strlen($data);
			$this->currentDate = date("r");
			$ch = curl_init();
			$url = "http://" . $this->options["hostName"] . ":" . $this->options["port"] . $this->options["path"];
			$header = array ("Accept: " . $this->options["acceptType"], 
							"Content-Length: " . $this->requestLength, 
							"Authentication:" . $this->appId . ":" . $this->signRequest(), 
							"Content-Type:application/json",
							"Date:" . $this->currentDate);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			
			switch($this->options["method"]) {
				case "POST":
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
					break;
				case "DELETE":
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
					break;
				case "PUT":
					curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
					curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
			}
			
			$responseBody = curl_exec($ch);
			$responseInfo	= curl_getinfo($ch);
		    curl_close($ch);
			return $responseBody;
		}
		
		private function signRequest() {
			return trim(base64_encode(hash_hmac("SHA1", $this->canonicalString(), $this->appSecret, true)));
		}
		
		private function canonicalString() {
			$method = $this->options["method"];
			$contentType = $this->contentType;
			$contentMD5 = "";
			$contentLength = $this->requestLength;
			$date = $this->currentDate;
			$path = $this->options["path"];
			$canonicalStr = $method . $contentType . $contentMD5 . $contentLength . $date . $path;
			return $canonicalStr;
		}
	}
	

?>