<?php

require_once("pichi_core.php");

class CommandHandler extends PichiCore
{

	public function __construct(&$jabber)
	{
		parent::__construct($jabber);
	}

	protected function seperate($command, $level = 2)
	{
		$array = array();
		if(strpos($command, "=") !== FALSE)
		{
			$w = explode(" ",$command);
			$array[0] = $w[0];
			unset($w[0]);
			$w = implode(" ",$w);
			$w = explode("=",$w);
			$array[1] = $w[0];
			$array[2] = $w[1];
			return $array;
		}
		else if(strpos($command, "\"") !== FALSE)
		{
			$w = explode("\"", $command);
			$array[0] = trim($w[0]);
			for($i = $g = 1; $i < count($w); $i = $i+2, $g++)
				$array[$g] = $w[$i];
			$array[$g] = trim($w[--$i]);
			return $array;
		}
		else // default
		{
			$w = explode(" ",$command);
			for($i = 0; $i < $level; $i++)
			{
				$array[$i] = $w[$i];
				unset($w[$i]);
			}
			$array[$i] = implode(" ", $w);
			return $array;
		}
	}
	
        private function isCommand($command)
        {
                return (substr($command,0,1) == "!");
        }
    
        private function getCommand($command)
        {
                if($this->isCommand($command))
                {
                        $get = explode(" ", $command);
                        return $get[0];
                }
        }

	protected function fetchCommand()
	{
		global $config;
		
		$this->log->log("Command from " . $this->last_from . ": $command", PichiLog::LEVEL_DEBUG);
		
		if(isCommand($this->last_message))
		{
			$command = $this->getCommand($this->last_from);
			$command = substr($command, 1);
			if(method_exists("command_{$command}"))
				$this->command_$command();
		}
	
	}

	public function do_if_message($message, $from, $type)
	{
		// No reaction while time off
		if(time() - $this->wait < $this->wait_time)
		{
			$this->log->log("Ignore Message: <$from> $message", PichiLog::LEVEL_DEBUG);
			return;
		}
			
		$this->last_message = $message;
		$this->last_from = $from;
		$this->last_type = $type;
		if($this->last_type == "groupchat")
			$this->last_room = $this->getJID($this->last_from);
		else
			$this->last_room = NULL;
	
		$this->log->log("Call message method", PichiLog::LEVEL_DEBUG);
		
		if(!$this->isIgnore())
			$this->fetch_commands($this->last_message, $this->last_from, $this->last_type); // проверяем на комманду

		if(!$this->isCommand($message) && $this->options['log_enabled'] == 1)
			$this->db->query("INSERT INTO log (`from`,`time`,`type`,`message`) VALUES ('".$this->db->db->escapeString($this->last_from)."','".$this->db->db->escapeString(time())."','".$this->db->db->escapeString($this->last_type)."','".$this->db->db->escapeString($this->last_message)."');");
		//Log
	
		//to lexems massges
		if(!$this->isIgnore() && !$this->isCommand($this->last_message) && $this->options['answer_remember'] == 1)
			$this->syntax->parseText($this->last_message);
	
		//test message
		if(!$this->isIgnore() && !$this->isCommand($this->last_message) && $this->options['answer_mode'] == 1)
		{
			if((int)$this->options['answer_random'] === 0 || rand(1, (int)$this->options['answer_random']) === 1)
			{
				$this->syntax->generate();
				if(rand(1, (int)$this->options['treatment_coincidence']) === 1 && $this->options['treatment_coincidence'] > 0)
				{
					switch(rand(1,2))
					{
						case 1:
							$this->sendAnswer($this->getName($this->last_from) . ": " . $this->syntax->returnText());
							break;
						case 2:
							$this->sendAnswer($this->getName($this->last_from) . ", " . $this->syntax->returnText());
							break;		
					}
				}
				else
				{
					$this->sendAnswer($this->syntax->returnText());
				}
			}
		}
	}

}

?>