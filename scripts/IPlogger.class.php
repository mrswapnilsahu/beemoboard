<?php
/*IPlogger - A class for logging visitor IP's, their last visit time, and 
and banning them if needed.
Written by: Brandon Foltz
www.brandonfoltz.com
brandon@brandonfoltz.com
February, 2012 */


/*The IP log file should be in this format:

ip-addr,last-visit,quick-refreshes,banned,ban-expiration,total-visits

Auto-banning will work like this: $iMinRefreshTimeS is set to the minimum 
"allowable" time between visits. If less time than this value has passed since
the users last visit, "quick-refreshes" will be incremented. Once
"quick-refreshes" has reached it's specified threshold value, the user will be
automatically banned for 60 minutes. "quick-refreshes" should be re-set once a 
visitor leaves the site and returns after > 2 minutes have passed. Each of these
values will be manually configurable. 

TODO: Perhaps use a config file for default values so they don't have to be
defined each time an instance of the object is created?

TODO: Lots of optimization could be done by inlining certain oft-used functions 
that access the DB. Benchmarking/real-world testing will be necessary to see if 
this is in fact, necessary.
*/

require_once('CSVedit.class.php');

class IPlogger 
{
	private $sError;
	private $sLogFile;
	private $db; //instance of CSVedit object
	private $iMinRefreshTimeS;
	private $iQuickRefreshThreshold;
	
	/*TODO: need global column-identifiers defined at top of file so that only
	one line needs changed if the DB is re-designed. */
	
	public function __construct($sLogFile)
	{
		//Initializing default values
		$this->iMinRefreshTimeS = 2;
		$this->iQuickRefreshThreshold = 50;
		
		$this->db = new CSVedit($sLogFile, 1);
		if ($this->db->getError() != "")
		{
			$this->setError($this->db->getError);
			return 0;
		}
		else
		{
			$this->sLogFile = $sLogFile;
			return 1;
		}
	}
	
	/*Returns the IP of the current visitor/user. */
	public function getUserIP()
	{
		if ($_SERVER['REMOTE_ADDR'] == "")
			die("HEY NOW HOLD ON A SEC!!!");
		return $_SERVER['REMOTE_ADDR'];
	}
	
	/*Puts data for the current visitor in the DB. If the user already exists
	in the DB, update their data. Checks if user is banned and removes it if
	it is expired. Also handles "quick-refresh" desribed at the top of the file
	to ban abusive/dos'ing users. Does NOT forward banned user to their 
	destination. This is the "heavy-lifting" function of this class. */ 
	public function logUser()
	{
		$userIP = $this->getUserIP();
		$entry = $this->db->search($userIP, 0, 0);
		if ($entry == -1)
		{
			$newEntry = array("ip" => $userIP,
							"last-visit" => time(),
							"quick-refreshes" => 0,
							"banned" => 0,
							"ban-expiration" => 0,
							"total-visits" => 1);
			$this->db->addRow($newEntry);
		}
		else
		{
			$this->db->getRow($userData, $entry);
			
			//remove ban if expired.
			if ($userData[3] == 1)
			{
				if ($userData[4] < time())
				{
					$userData[3] = 0;
					$userData[4] = 0;	
				}
			}
			
			if ($userData[3] != 1)
			{
				if ((time() - $userData[1]) < $this->iMinRefreshTimeS)
				{
					$userData[2] += 1;
				}
				else if ((time() - $userData[1]) > 120)
				{
					//remove "quick-refresh" count after 2 mins
					$userData[2] = 0;
				}
			}
			
			if ($userData[2] >= $this->iQuickRefreshThreshold)
			{
				$userData[3] = 1;
				$userData[4] = time() + (60 * 60); // ban for 60 mins.
				$userData[2] = 0;
			}
			
			$userData[5] += 1;
			
			$userData[1] = time(); //only update visit time when finished
			$this->db->updateRow($entry, $userData); //update DB!
		}
	}
	
	public function getTotalVisits()
	{
		$entry = $this->db->search($this->getUserIP(), 0, 0);
		if ($entry != -1)
		{
			$this->db->getRow($userData, $entry);
			return $userData[5];
		}
		else
			return 0; //first visit! (or not logged)
	}
	
	/*Returns the time the current visitors IP was last logged. */
	public function getLastVisit()
	{
		$entry = $this->db->search($this->getUserIP(), 0, 0);
		if ($entry != -1)
		{
			$this->db->getRow($userData, $entry);
			return $userData[1];
		}
		else
			return time(); //right now is their last visit!
	}
	
	/*Marks the current visitors IP as "banned", and specifies at what time in 
	the future they should be allowed to re-visit the site. */
	public function banUser($iMinutes)
	{
		$entry = $this->db->search($this->getUserIP(), 0, 0);
		if ($entry != -1)
		{
			$this->db->getRow($userData, $entry);
			
			//set ban variables
			$userData[3] = 1;
			$userData[4] = time() + ($iMinutes * 60);
			
			$this->db->updateRow($entry, $userData);
		}
		else
		{
			$this->logUser();
			
			$entry = $this->db->search($this->getUserIP(), 0, 0);
			if ($entry == -1)
				$this->setError("Unable to ban user!");
			
			$this->db->getRow($userData, $entry);
			
			//set ban variables
			$userData[3] = 1;
			$userData[4] = time() + ($iMinutes * 60);
			$this->db->updateRow($entry, $userData);
		}
	}
	
	/*Checks if the current user is banned or not. Returns 1 if they are, 0 
	otherwise. Also places the time when the user will be unbanned in 
	$iUnbanTime variable passed by reference. */
	public function isBanned(&$iUnbanTime)
	{
		$entry = $this->db->search($this->getUserIP(), 0, 0);
		if ($entry == -1)
		{
			$iUnbanTime = 0;
			return 0;
		}
		else
		{
			$this->db->getRow($userData, $entry);
			if ($userData[3] == 1)
			{
				$iUnbanTime = $userData[4];
				return 1;
			}
			else if ($userData[3] == 0)
			{
				$iUnbanTime = 0;
				return 0;
			}
			else
			{
				$this->setError("Database error when checking user ban status.");
				return -1;
			}
		}
	}
	
	/*Enforces a user ban. First checks if the user is banned, and if they are
	will forward them to the specified URL. */
	public function enforceBan($sForwardURL)
	{
		if ($this->isBanned($banExpire) == 1)
		{
			header("Location: ".$sForwardURL);
			return 1;
		}
		else
			return 0;
	}
	
	/*Remove entries from the DB whos last visit date is older than $iDays. */
	public function pruneList($iDays)
	{
	
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
	
}
