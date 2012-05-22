<?php
/*CSVedit - A class for simplifying the use of CSV databases with PHP.
Written by: Brandon Foltz
www.brandonfoltz.com
brandon@brandonfoltz.com
February, 2012 */

/*
TODO: Test parallel/simultaneous file updates... maybe need a locking mechanism
to avoid problems with the single temp file? - Yes this is a problem. Data
corruption occurs when file is updated simultaneously. 
*/

define('LOCK_METHOD', 0); //0 uses file, 1 uses flock() //XXX: not finished
define('LOCK_TIMEOUT', 5000000); //in microseconds  

class CSVedit
{
	private $sError;
	private $sCSVfile;
	private $sTempFilePath;
	private $sLockFile;
	private $bHasLock;
	private $sCSVdelimiter;
	
	private $iNumColumns;
	private $aColumnNames;
	
	//const LOCK_METHOD = 0; //0 uses file, 1 uses flock()
	//const LOCK_TIMEOUT = 5000; //in milliseconds
	
	/*Creates object, selects CSV file to edit.*/
	public function __construct($sCSVfile, $bCreate)
	{
		if ($this->selectCSVfile($sCSVfile, $bCreate) != 0)
			return 1;
		else
			return 0;
	}
	
	/*Sets internal error string.*/
	private function setError($sError)
	{	
		if(isset($sError))
		{
			$this->sError = $sError;
			return 1;
		}
		else
			return 0;
	}
	
	/*Returns internal error string.*/
	public function getError()
	{
		return $this->sError;
	}	
	
	/*Selects the CSV file to edit.*/
	public function selectCSVfile($sCSVfile, $bCreate = 1, $sDelimiter = ",")
	{
		/*TODO: Probably a way to shorten this function given that there is some
		duplicate code. */
	
		if(file_exists($sCSVfile))
		{
			$this->sCSVfile = $sCSVfile;
			$this->sTempFilePath = dirname(realpath($this->sCSVfile))."/temp.csv";
			$this->sLockFile = $this->sCSVfile."_lock";
			$this->sCSVdelimiter = $sDelimiter;
			
			$aColumnNames = $this->getColumnNames();
			
			return 1;
		}
		else
		{
			if ($bCreate == 1)
			{
				$fp = fopen($sCSVfile, "w");
				if ($fp == NULL)
				{
					$this->setError("Unable to create file: ".$sCSVfile);
					return 0;
				}
				fwrite($fp, "");
				fclose($fp);
				if (true == file_exists($sCSVfile))
				{
					$this->sCSVfile = $sCSVfile;
					$this->sTempFilePath = dirname(realpath($this->sCSVfile))."/temp.csv";
					$this->sLockFile = $this->sCSVfile."_lock";
					$this->sCSVdelimiter = $sDelimiter;
					
					return 1;
				}
				else
				{
					$this->setError("Unable to create file: ".$sCSVfile);
					return 0;
				}
				
			}
			else
			{
				$this->setError("File ".$sCSVfile." does not exist!");
				return 0;
			}
		}
	}
	
	/*Returns the number of rows in the table.*/
	public function numRows()
	{
		$i = 0;
		
		if ($this->acquireLock())
		{
			$fp = fopen($this->sCSVfile, "r");
			while ($aTempData = fgetcsv($fp, 0, $this->sCSVdelimiter))
			{
				$i++;
			}
			fclose($fp);
			$this->releaseLock();
			return $i - 1; //exclude column names
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}
	
	/*Returns the first row of the CSV file, presumably containing column names.
	These are returned as an array, each element representing a column.*/
	public function getColumnNames()
	{
		$i = 0;
		
		if ($this->acquireLock())
		{
			$fp = fopen($this->sCSVfile, "r");
			$aTempData = fgetcsv($fp, 0, $this->sCSVdelimiter);
			fclose($fp);
			$this->releaseLock();
			return $aTempData;
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}
	
	/*Returns an array of row data whos element indices correspond to their 
	respective columns.*///0 uses file, 1 uses flock()
	public function getRow(&$aReturnData, $iRow)
	{
		$i = 0;
		if ($this->acquireLock())
		{
			$fp = fopen($this->sCSVfile, "r");
			while ($aTempData = fgetcsv($fp, 0, $this->sCSVdelimiter))
			{
				if ($i == $iRow)
				{		
					$aReturnData = $aTempData;
					fclose($fp);
					$this->releaseLock();
					return 1;
					break;
				}
				$i++;
			}
			fclose($fp);
			$this->releaseLock();
			$this->setError("Nonexistant row!");
			return 0;
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}
	
	/*Returns an array of arrays (rows) in specified range. */
	public function getRowRange(&$aReturnData, $iStartRow, $iEndRow)
	{
		if ($this->acquireLock())
		{
			$fp = fopen($this->sCSVfile, "r");
		
			$row = 0;
			$index = 0;
			while ($aTempData = fgetcsv($fp, 0, $this->sCSVdelimiter))
			{			
				if ($row >= $iStartRow && $row <= $iEndRow)
				{
					$aReturnData[$index] = $aTempData;
					$index++;
				}	
				else if ($row > $iEndRow)
					break;
				$row++;
			}
		
			fclose($fp);
			
			$this->releaseLock();
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}
	
	/*Returns an array of arrays (rows) in specified range sorted "regularly"
	in ascending order by the specified column.*/
	public function getSortedRows(&$aReturnData, $iRowStart, $iRowEnd, $sortIndex)
	{
		for ($i=$iRowStart;$i<=$iRowEnd;$i++)
		{			
			$this->getRow($aRow, $i);
			$aRowData[$i] = $aRow[$sortIndex];
		}
		
		asort($aRowData, SORT_REGULAR);		
		
		$i = 0;
		foreach($aRowData as $key => $data)
		{
			$this->getRow($aData[$i], $key);
			$i++;
		}
		
		$aReturnData = $aData; //return data to parameter passed by reference.
	}
	
	/*Returns the entire table as a two dimensional array.*/
	public function getTable(&$aReturnTable, $bIncludeColumnNames)
	{
		/*
		FIXME: Not really a "bug", but if you use numRows() and don't include 
		column names, you can get an "Undefined offset" notice when looping
		through data returned since numRows() will return a value 1 higher than
		the actual number of rows, when the first (column names) are excluded.
		*/
	
		if ($bIncludeColumnNames == 1)
			$startRow = 0;
		else
			$startRow = 1;
		
		if ($this->acquireLock())
		{
			$fp = fopen($this->sCSVfile, "r");
		
			if ($bIncludeColumnNames == 0)
				$aNoData = fgetcsv($fp, 0, $this->sCSVdelimiter);
		
			$i = 0;
			while ($aTempData = fgetcsv($fp, 0, $this->sCSVdelimiter))
			{			
				$aReturnTable[$i] = $aTempData;	
				$i++;		
			}
		
			fclose($fp);
			
			$this->releaseLock();
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}
	
	/*Replaces a single column/row element in the table.*/
	public function updateCell($iRow, $iColumn, $sData)
	{
		if ($iRow > $this->numRows())
			die("Row ".$iRow." does not exist!\n");
			
		if ($iColumn > count($this->getColumnNames()))
			die("Column ".$iColumn." does not exist!\n");
	
		$this->getTable($db, 1);
		
		//set the new value
		$db[$iRow][$iColumn] = $sData;
		//delete existing temp file
		/*if (file_exists($this->sTempFilePath))
			unlink($this->sTempFilePath);*/
			//try rm'ing temp file only after lock acquired
		
		//write the new csv to a temp file
		if ($this->acquireLock())
		{
			if (file_exists($this->sTempFilePath)) //^^
				unlink($this->sTempFilePath);
		
			$fp = fopen($this->sTempFilePath, "a+");
		
			$rowCount = count($db);
			for ($i=0; $i<$rowCount; $i++)
			{
				fputcsv($fp, $db[$i]);
			}
		
			fclose($fp);
		
			unlink($this->sCSVfile);
			rename($this->sTempFilePath, $this->sCSVfile);
			
			$this->releaseLock();
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}
	
	/*Replaces an entire row in the table from an array whos element indices 
	should correspond with their respective columns to update.*/
	public function updateRow($iRow, $aData)
	{
		if ($iRow > $this->numRows())
			die("Row ".$iRow." does not exist!\n");
	
		$this->getTable($db, 1);
		
		//set the new value(s)
		$numElements = count($aData);
		for	($i=0; $i<$numElements; $i++)
			$db[$iRow][$i] = $aData[$i];
		
		//delete existing temp file
		/*if (file_exists($this->sTempFilePath))
			unlink($this->sTempFilePath);*/
		
		//write the new csv to a temp file
		if ($this->acquireLock())
		{
			if (file_exists($this->sTempFilePath))
				unlink($this->sTempFilePath);
		
			$fp = fopen($this->sTempFilePath, "a+");
		
			$rowCount = count($db);
			for ($i=0; $i<$rowCount; $i++)
			{
				fputcsv($fp, $db[$i]);
			}
		
			fclose($fp);
		
			unlink($this->sCSVfile);
			rename($this->sTempFilePath, $this->sCSVfile);
		
			$this->releaseLock();
		}
		else
		{
			$this->setError("Couldn't acquire lock!");
			return 0;
		}
	}

	/*Inserts a new row on to the end of the table.*/
	public function addRow($aData)
	{
		if ($aData != NULL || $aData != "")
		{
			if ($this->acquireLock())
			{
				$fp = fopen($this->sCSVfile, "a+");
				if ($fp == NULL | $fp == 0)
				{
					$this->setError("Unable to open ".$this->sCSVfile." for writing!");
					return 0;
				}
				fputcsv($fp, $aData);
				fclose($fp);
			
				$this->releaseLock();
				return $this->numRows(); //- 1; //return index of newly inserted row
			}
			else
			{
				$this->setError("Couldn't acquire lock!");
				return 0; //return 0 on failure
			}
		}
		else
		{
			$this->setError("No data to write!");
			return 0; //return 0 on failure
		}
	}
	
	/*Removes an entire row from the table*/
	public function deleteRow($iRow)
	{
		if ($iRow > $this->numRows())
			die("Row ".$iRow." does not exist!\n");
	
		$this->getTable($db, 1);
		
		//delete existing temp file
		if (file_exists($this->sTempFilePath))
			unlink($this->sTempFilePath);
		
		//write the new csv to a temp file
		if($this->acquireLock())
		{
			$fp = fopen($this->sTempFilePath, "a+");
		
			$rowCount = count($db);
			for ($i=0; $i<$rowCount; $i++)
			{
				if ($i != $iRow)
					fputcsv($fp, $db[$i]);
			}
		
			fclose($fp);
		
			unlink($this->sCSVfile);
			rename($this->sTempFilePath, $this->sCSVfile);
			
			$this->releaseLock();
		}
		else
			$this->setError("Couldn't acquire lock!");
	}
	
	/*Searches all rows for $sSearchData by the specified column. If $iFlag is
	0, it will return only the first instance it finds. If $iFlag is 1, it will
	return all instances it finds in an array.*/
	public function search($sSearchData, $iSearchColumn, $iFlag)
	{
		/*TODO: impliment partial match, ie: search for "Gary" find 
		"Gary Gobbles" */
		
		/*TODO: Search speed could be greatly improved if getRow() and like
		code is inlined so that file pointers do not have to be opened/closed
		constantly. */
	
		if ($iFlag == 0)
		{
			$numRows = $this->numRows();
			for ($i=0;$i<=$numRows;$i++)
			{
				$this->getRow($aData, $i);
			
				if($aData[$iSearchColumn] == $sSearchData)
					return $i;
			}
			return -1;
		}
		else if ($iFlag == 1)
		{
			$numRows = $this->numRows();
			for ($i=0;$i<=$numRows;$i++)
			{
				$this->getRow($aData, $i);
			
				if($aData[$iSearchColumn] == $sSearchData)
					$aResults[] = $i;
			}
			if (count($aResults) > 0)
				return $aResults;
			else
				return -1;
		}
	}
	
	//FIXME: These functions should be made private once testing is done.
	
	/*Tries to acquire a r/w lock on the DB. Will loop until lock is acquired
	or it has timed-out. Returns 1 on success, 0 on failure. */
	private function acquireLock()
	{
		$minWaitTime = 1000; //500 microseconds
		$totalWaitTime = 0;
		
		if ($this->hasLock() == 1)
			return 1;
		
		while($this->isLocked())
		{
			/* delay time between each lock attempt is randomized to help avoid
			deadlock in scenarios where two processes are trying for the lock
			simultaneously. */
			$waitTime = ($minWaitTime + rand(100, 900));
			usleep($waitTime);
			$totalWaitTime += $waitTime;
			
			if ($totalWaitTime > LOCK_TIMEOUT)
			{
				$this->bHasLock = false;
				return 0; //timed out, couldn't get lock
			}
		}
	
		touch($this->sLockFile);
		//$fp = fopen($this->sLockFile, "w");
		//fputs($fp, "");
		//fclose($fp);
		$this->bHasLock = true;
		return 1;
	}
	
	/*Releases the r/w lock on the DB. Returns 1 on success, 0 on failure. */
	private function releaseLock()
	{
		if ($this->hasLock()) //can't unlock if we don't have the lock!
		{
			if (true == unlink($this->sLockFile))
			{	
				$this->bHasLock = false;
				return 1;
			}
			else
				return 0;	
		}
		else
			return 0;
	}
	
	/*Checks if this instance of the class has the r/w lock on the DB. Returns
	1 if it does, 0 if it does not. */
	private function hasLock()
	{
		if ($this->bHasLock == true)
			return 1;
		else
			return 0;
	}
	
	/*Checks to see if the DB file is currently locked by another process. 
	Returns 1 if it is, 0 if not. */
	private function isLocked()
	{
		if (true == file_exists($this->sLockFile))
			return 1;
		else
			return 0;
	}
}
?>
