<?php

namespace RubtsovAV\RemoteDatabaseBackup\Server;

class DatabaseAdapterFactory
{
	public function createAdapter($name = null, $params = [])
	{
		if (!$name) {
			$name = $this->getAvailableAdapters();
			$name = array_shift($name);
		}

		$name = strtolower($name);
		if (!$this->adapterIsAvailable($name)) {
			throw new \Exception("adapter '$name' is not available");
		}
		
		switch ($name)
		{
			case 'mysqli':
				return new DatabaseAdapter\Mysqli($params);
		}
	}

	public function adapterIsAvailable($name)
	{
		return in_array($name, $this->getAvailableAdapters());
	}

	public function getAvailableAdapters()
	{
		$availableAdapters = [];
		if (function_exists('mysqli_connect')) {
			$availableAdapters[] = 'mysqli';
		}

		return $availableAdapters;
	}
}