<?php

class CSVFile {
	public function __construct($path)
	{
		// Todo: locking (really? Yeah, sorry...)
		$this->path = $path;

		if (file_exists($this->path)) {
			$this->fh = fopen($this->path, 'r+');
		} else {
			$this->fh = fopen($this->path, 'w+');
		}

		if (!$this->fh)
			throw new Exception('Could not open file for writing');

		$this->columns = fgetcsv($this->fh);

		if (!$this->columns)
			$this->columns = array();
	}

	public function __destruct()
	{
		fclose($this->fh);
	}

	public function add(array $data)
	{
		flock($this->fh, LOCK_EX);

		// Check whether all columns are available
		$missing = array_diff(array_keys($data), $this->columns);

		if (count($missing) > 0)
			$this->addColumns($missing);

		// move cursor to end of file
		fseek($this->fh, 0, SEEK_END);

		// Put the data in the correct order
		$row = array();
		foreach ($this->columns as $column)
			$row[] = isset($data[$column]) ? $data[$column] : null;

		$success = fputcsv($this->fh, $row) !== false;

		flock($this->fh, LOCK_UN);

		return $success;
	}

	protected function addColumns($columns)
	{
		$this->columns = array_merge($this->columns, $columns);

		// Rewind to the start of the file
		fseek($this->fh, 0, SEEK_SET);

		// Just read the whole file
		$rows = array();

		while (($line = fgets($this->fh)) !== false)
			$rows[] = $line;

		// Remove the old header
		array_shift($rows);

		// Add the new one
		array_unshift($rows, implode(',', $this->columns) . PHP_EOL);

		// Clear the file
		ftruncate($this->fh, 0);

		// Write all the data back
		foreach ($rows as $row)
			fwrite($this->fh, $row);
	}
}