<?php

namespace RubtsovAV\RestDatabaseExporter\Client;

use RubtsovAV\RestDatabaseExporter\Client\Exception\InvalidResponseException;
use GuzzleHttp\Client as HttpClient;

class Client
{
	const SUCCESS_RESPONSE_MARK_TEMPLATE = "\n/* SUCCESS_RESPONSE_MARK %d */";

	private $uri;
	private $dbParams;
	private $httpClient;
	private $successResponseMark;

	public function __construct($uri, $dbParams = [])
	{
		$this->uri = $uri;	
		$this->dbParams = $dbParams;

		$this->httpClient = new HttpClient([
			'connect_timeout'  => 10,
			'timeout'  => 60,
		]);

		$this->successResponseMark = $this->generateSuccessResponseMark();
	}

	public function export($output)
	{
		$this->exportHeader($output);
		$this->exportCreateDatabase($output);
		$this->exportTables($output);
		$this->exportViews($output);
		$this->exportTriggers($output);
		$this->exportRoutines($output);
		$this->exportFooter($output);
	}

	public function exportHeader($output)
	{
		$response = $this->request('exportHeader');
		$this->writeResponseBodyTo($response, $output);
	}

	public function exportCreateDatabase($output)
	{
		$response = $this->request('exportCreateDatabase');
		$this->writeResponseBodyTo($response, $output);
	}

	public function exportTables($output)
	{
		$tables = $this->getTablesMetadata();
		foreach ($tables as $table) {
			$this->exportTable($output, $table['name']);
		}
	}

	public function exportTable($output, $tableName)
	{
		$response = $this->request('exportTable', [$tableName]);
		$this->writeResponseBodyTo($response, $output);
	}

	public function exportTriggers($output)
	{
		$response = $this->request('exportTriggers');
		$this->writeResponseBodyTo($response, $output);
	}

	public function exportViews($output)
	{
		$response = $this->request('exportViews');
		$this->writeResponseBodyTo($response, $output);
	}

	public function exportRoutines($output)
	{
		$response = $this->request('exportRoutines');
		$this->writeResponseBodyTo($response, $output);
	}

	public function exportFooter($output)
	{
		$response = $this->request('exportFooter');
		$this->writeResponseBodyTo($response, $output);
	}

	public function getTablesMetadata()
	{
		$response = $this->request('getTablesMetadata');
		$body = $this->getResponseBody($response);
		return json_decode((string) $body, true);
	}

	public function getUri()
	{
		return $this->uri;
	}

	public function getDbParams()
	{
		return $this->dbParams;
	}

	public function getHttpClient()
	{
		return $this->httpClient;
	}

	private function request($action, $data = [])
	{
		$response = $this->httpClient->request('POST', $this->uri, [
		    'form_params' => [
		        'db' => $this->dbParams,
		        'action' => $action,
		        'data' => $data,
		        'response_mark' => $this->successResponseMark,
		    ]
		]);

		$this->assertResponse($response);
		return $response;
	}

	private function assertResponse($response)
	{
		if ($response->getStatusCode() != 200) {
			throw new InvalidResponseException(
				$response,
				sprintf("The response status code is %d", $response->getStatusCode())
			);
		}

		$contentType = $response->getHeaderLine('Content-Type');
		switch ($contentType) {
			case 'text/plain;charset=UTF-8':
			case 'application/json':
				$flagLength = strlen($this->successResponseMark);
				$body = $response->getBody();
				$body->seek(-$flagLength, SEEK_END);
				if ($body->read($flagLength) !== $this->successResponseMark) {
					throw new InvalidResponseException(
						$response,
						"The response don't have SUCCESS_RESPONSE_MARK"
					);
				}
				break;

			default:
				throw new InvalidResponseException(
					$response,
					"The response have undefined Content-Type: $contentType"
				);
				break;
		}
	}

	private function writeResponseBodyTo($response, $output)
	{
		$body = $response->getBody();
		$body->rewind();
		$bodySize = $body->getSize() - strlen($this->successResponseMark);

		$readed = 0;
		$chunkSizeLimit = 5 * 1024 * 1024;
		while (!$body->eof()) {
			$chunkSize = min($chunkSizeLimit, $bodySize - $readed);
			if (!$chunkSize) {
				break;
			}
		    fwrite($output, $body->read($chunkSize));
		    $readed += $chunkSize;
		}
	}

	private function getResponseBody($response)
	{
		$body = $response->getBody();
		$body->rewind();
		$bodySize = $body->getSize() - strlen($this->successResponseMark);
		return $body->read($bodySize);
	}

	private function generateSuccessResponseMark()
	{
		return sprintf(self::SUCCESS_RESPONSE_MARK_TEMPLATE, time());
	}
}