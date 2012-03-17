<?php

class Timer
{
	private $fStartTime = 0;
	private $fStopTime = 0;
	private $fElapsedTime = 0;
	
	/*Constructor. Allows you to start the timer upon instantiation by passing
	1 to $bStart. */
	public function __construct($bStart = 0)
	{
		if ($bStart = 1)
			$this->start();
	}

	//Starts the timer!
	public function start()
	{
		$this->fStartTime = microtime(true);
	}
	
	//Stops the timer!
	public function stop()
	{
		$this->fStopTime = microtime(true);
	}
	
	/*Returns the elapsed time from timer start to timer stop. Or, returns the
	current elapsed time from start time to now. */
	public function getElapsedTime()
	{
		if ($this->fStopTime != 0)
		{
			$this->fElapsedTime = $this->fStopTime - $this->fStartTime;
			return $this->fElapsedTime;
		}
		else
		{
			$this->fElapsedTime = microtime(true) - $this->fStartTime;
			return $this->fElapsedTime;
		}
		
	}
}
