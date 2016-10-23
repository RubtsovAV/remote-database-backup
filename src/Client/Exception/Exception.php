<?php

namespace RubtsovAV\RemoteDatabaseBackup\Client\Exception;

class Exception extends \Exception
{
	protected $response;

	public function __construct(
		$response,
		$message = '', 
		$code = 0, 
		\Exception $previously = null
	){
		$this->response = $response;
		parent::__construct($message, $code, $previously);
	}

	public function getResponse()
	{
		return $this->response;
	}
}