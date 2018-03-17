<?php

class CSVFile {
	public function __construct($path)
	{
		$this->path = $path;

		if (($this->fh = fopen($this->path, 'a+')) === false)
			throw new Exception('Could not open file for writing');

		$this->columns = fgetcsv($this->fh);

		if ($this->columns === false)
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

		// Rewind (again) to start writing at the beginning of the file
		fseek($this->fh, 0, SEEK_SET);

		// Write all the data back
		foreach ($rows as $row)
			fwrite($this->fh, $row);
	}
}