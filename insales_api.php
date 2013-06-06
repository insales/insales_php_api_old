<?php

	function insales_api_client($my_insales_domain, $api_key, $password)
	{
		$baseurl = "http://$api_key:$password@$my_insales_domain/";

		return function ($method, $path, $params=array(), &$response_headers=array()) use ($baseurl)
		{
			$url = $baseurl.ltrim($path, '/');
			$payload = in_array($method, array('POST','PUT')) ? json_encode($params) : array();
			$request_headers = in_array($method, array('POST','PUT')) ? array("Content-Type: application/json; charset=utf-8", 'Expect:') : array();

			$response = curl_http_api_request_($method, $url, $payload, $request_headers, $response_headers);
			$response = json_decode($response, true);

			if (isset($response['errors']) or ($response_headers['http_status_code'] >= 400))
				throw new InsalesApiException(compact('method', 'path', 'params', 'response_headers', 'response', 'my_insales_domain'));

			return $response;
		};
	}

		function curl_http_api_request_($method, $url, $payload='', $request_headers=array(), &$response_headers=array())
		{
			$ch = curl_init($url);
			curl_setopts_($ch, $method, $payload, $request_headers);
			$response = curl_exec($ch);
			$errno = curl_errno($ch);
			$error = curl_error($ch);
			curl_close($ch);

			if ($errno) throw new InsalesCurlException($error, $errno);

			list($message_headers, $message_body) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);
			$response_headers = curl_parse_headers_($message_headers);

			return $message_body;
		}

			function curl_setopts_($ch, $method, $payload, $request_headers)
			{
				curl_setopt($ch, CURLOPT_HEADER, true);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
				curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
				curl_setopt($ch, CURLOPT_USERAGENT, 'HAC');
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
				curl_setopt($ch, CURLOPT_TIMEOUT, 30);

				if ('GET' == $method)
				{
					curl_setopt($ch, CURLOPT_HTTPGET, true);
				}
				else
				{
					curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, $method);
					if (!empty($request_headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
					if (!empty($payload))
					{
						if (is_array($payload)) $payload = http_build_query($payload);
						curl_setopt ($ch, CURLOPT_POSTFIELDS, $payload);
					}
				}
			}

			function curl_parse_headers_($message_headers)
			{
				$header_lines = preg_split("/\r\n|\n|\r/", $message_headers);
				$headers = array();
				list(, $headers['http_status_code'], $headers['http_status_message']) = explode(' ', trim(array_shift($header_lines)), 3);
				foreach ($header_lines as $header_line)
				{
					list($name, $value) = explode(':', $header_line, 2);
					$name = strtolower($name);
					$headers[$name] = trim($value);
				}

				return $headers;
			}

	class InsalesCurlException extends Exception { }
	class InsalesApiException extends Exception
	{
		protected $info;

		function __construct($info)
		{
			$this->info = $info;
			parent::__construct($info['response_headers']['http_status_message'], $info['response_headers']['http_status_code']);
		}

		function getInfo() { return $this->info; }
	}
?>
