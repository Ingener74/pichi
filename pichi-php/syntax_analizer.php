<?php

class SyntaxAnalizer
{
  
	protected $user_text;
	protected $send_text;
	
	public $db;
	public $log;
  
	//Settings
	public $limit_word = 2;
	public $query_limit = 10;
	public $from_world_coi = 3;

	private $try_count = 0; //попытки построить выражения
	public $try_limit = 3; //лимит попыток
	
	public function __construct()
	{
		//$this->log->log("Init syntaxis analizator", PichiLog::LEVEL_DEBUG);
	}

	public function parseText($string)
	{
		$this->testText($string); //режим все лишнее со строки
		$this->log->log("$string to lexems", PichiLog::LEVEL_DEBUG);
		$base = explode(" ", $string);
      
		for($i = 0; $i < count($base); $i++)
		{
			$str = ((@$base[$i-1]) ? $base[$i-1] : "#beg#" ) . " " . $base[$i]  . " " . ((@$base[$i+1]) ? $base[$i+1] : "#end#");
			$this->addLexema($str);
		}
		
		$this->user_text = $string;
	}
	
	public function returnText()
	{
		return $this->send_text;
	}
	
	private function addLexema($str)
	{	
		if($str == NULL)
			return;
      
		$this->db->query("SELECT COUNT(*) FROM lexems WHERE lexeme = '" . $this->db->db->escapeString($str) . "';");
		if($this->db->fetchColumn() > 0)
			$this->db->query("UPDATE lexems SET count = count+1  WHERE lexeme = '".$this->db->db->escapeString($str)."';");
		else
			$this->db->query("INSERT INTO lexems (`lexeme`,`count`) VALUES ('" . $this->db->db->escapeString($str) . "','1');");
		$this->log->log("$str writed to lexems", PichiLog::LEVEL_VERBOSE);
	}
	
	private function choseLexem(& $answers)
	{
		$this->log->log("Rand world from lexems:", PichiLog::LEVEL_VERBOSE);
		$sum = 0;
		foreach($answers as $key=>$data)
		{
			$sum += (int)$data['count'];
			$this->log->log("World \"{$data['lexeme']}\" have {$data['count']} points", PichiLog::LEVEL_VERBOSE);
		}
		$rand = rand(0, $sum);
		$this->log->log("Random seed: {$rand}", PichiLog::LEVEL_VERBOSE);
		
		$temp_sum = 0;
		$num = 0;
		while($temp_sum < $rand)
			$temp_sum += $answers[$num++]['count'];

		$this->log->log("CHOISE: " . $answers[(($num>0) ? $num-1 : $num)]['lexeme'], PichiLog::LEVEL_VERBOSE);
		return $answers[(($num>0) ? $num-1 : $num)]['lexeme'];
	}
	
	//метод поиска слова, за которым следует данное.
	// по умолчанию возращает троеточие
	private function doubleLemem(& $answers, $word, $reverse = false)
	{
		$this->log->log("Seek DOUBLE lexeme for: {$word}", PichiLog::LEVEL_VERBOSE);
		foreach($answers as $key=>$data)
		{
			$this->log->log("Try DOUBLE lexeme: {$data['lexeme']}", PichiLog::LEVEL_VERBOSE);
			$need = explode(" ", $data['lexeme']);
			$need = (!$reverse) ? $need[1] . " " . $need[2] : $need[0] . " " . $need[1];
			$this->db->query("SELECT COUNT(*) FROM lexems WHERE lexeme LIKE '" . $this->db->db->escapeString(((!$reverse) ? $need : $word)) . " " . $this->db->db->escapeString(((!$reverse) ? $word : $need)) . "' LIMIT 0,1;");
			if($this->db->fetchColumn() > 0)
			{
				$this->log->log("CHOISE DOUBLE lexeme: {$data['lexeme']}", PichiLog::LEVEL_VERBOSE);
				return $data['lexeme'];
			}
		}
		return "... ... ...";
	}
	
	public function generate()
	{
		$this->try_count = 0; // сбрасываем попытки
	  
		//if(rand(1, $this->from_world_coi) === 1)
		//{
			$this->log->log("Full Random method", PichiLog::LEVEL_DEBUG);
			$this->send_text = $this->genFullRandom();
		//}
		//else
		//{
		//	$this->log->log("Word method", PichiLog::LEVEL_DEBUG);
		//	$words = explode(" ", $this->user_text);
		//	$word = $this->choseWord($words);
		//	$this->send_text = $this->genFromCenterWord($word);
		//}
	}
	
	private function choseWord(& $array)
	{
		if(count($array) < 1)
			return false;
		$good = array();
		//first
		foreach($array as $word)
		{
			if(strlen($word) > 2)
				$good[] = $word;
		}
		if(count($good) > 0)
		{
			$rand = rand(0, count($good)-1);
			$return = $good[$rand];
		}
		else
		{
			$rand = rand(0, count($array)-1);
			$return = $array[$rand];
		}
		$this->log->log("Choise word: $return", PichiLog::LEVEL_DEBUG);
		return $return;
	}


	private function genFullRandom($limit = NULL)
	{
		if($limit == NULL)
			$limit = rand(5,20);
		$this->try_count++; //очередная попытка
		$this->db->query("SELECT * FROM lexems WHERE lexeme LIKE '#beg# %' ORDER BY RANDOM() LIMIT 0,1;");
		if($this->db->numRows(true) == 0)
			return; //пусто
		$last = $this->choseLexem($this->buildArray());
		$lastx = explode(" ", $last);

		$last = $lastx[1] . " " . $lastx[2];
		$genans = $lastx[1] . (($lastx[2]!="#end#") ? " " . $lastx[2] : "");
		if($lastx[2] == "#end#")
			return $genans;

		for($i=0; $i < $limit; $i++)
		{
			$this->db->query("SELECT * FROM lexems WHERE lexeme LIKE '" . $this->db->db->escapeString($last) . " %' ORDER BY `count` DESC LIMIT 0," . $this->query_limit . ";");
			if($this->db->numRows(true) == 0)
				break; //больше нет совпадений
			if($i != $limit-1)
				$last = $this->choseLexem($this->buildArray());
			else
				$last = $this->doubleLemem($this->buildArray(), "#end#");
			$lastx = explode(" ",$last);
			$last = $lastx[1] . " " . $lastx[2];
	
			if($lastx[2] == "#end#" || $lastx[2]==NULL)
				break;
	
			$genans .= " " . $lastx[2];
		}
		//if($genans != $this->user_text)
			return $genans;
		//else
		//	return $this->randFromLog(); //возращать тоже самое нехорошо, вернем что-нибудь из лога
	}
	
	private function genFromCenterWord($word, $limit = NULL)
	{
		if($limit == NULL)
			$limit = rand(7,12);
		$answer = $word;
		$this->try_count++; //очередная попытка
		//left
		for($i = 0; $i < $limit; $i++)
		{
			$this->db->query("SELECT * FROM lexems WHERE lexeme LIKE '% " . $this->db->db->escapeString(($i == 0) ? $word : $first . " " . $second) . "' ORDER BY `count` DESC LIMIT 0," . $this->query_limit . ";");
			if($this->db->numRows(true) == 0)
				break; //больше нет совпадений
			if($i != $limit-1)
				$last = $this->choseLexem($this->buildArray());
			else
				$last = $this->doubleLemem($this->buildArray(), "#beg#", true);
			list($first, $second, $third) = explode(" ", $last);
				
			if($first == "#beg#" || $first == NULL)
				break;
			$answer = (($i==0) ? $first . " " . $second : $first) . " " . $answer;
		}
		//right
		for($i = 0; $i < $limit; $i++)
		{
			$this->db->query("SELECT * FROM lexems WHERE lexeme LIKE '" . $this->db->db->escapeString(($i == 0) ? $word : $second . " " . $third) . " %' ORDER BY `count` DESC LIMIT 0," . $this->query_limit . ";");
			if($this->db->numRows(true) == 0)
				break; //больше нет совпадений
			if($i != $limit-1)
				$last = $this->choseLexem($this->buildArray());
			else
				$last = $this->doubleLemem($this->buildArray(), "#end#");
			list($first, $second, $third) = explode(" ", $last);
				
			if($third == "#end#" || $third == NULL)
				break;
			$answer = $answer . " " . (($i==0) ? $second . " " . $third : $third);
		}
		//if($answer != $this->user_text)
			return $answer;
		//else
		//	return $this->randFromLog(); //возращать тоже самое нехорошо, вернем что-нибудь из лога
	}
	
	//Использовать случайную фразу из лога
	// 3 слова как лимит
	// смайл если невозможно найти подходящей фразы
	private function randFromLog($words_limit = 3, $dafault = ":(")
	{
		$this->try_count++; //очередная попытка
		$this->db->query("SELECT message FROM log ORDER BY RANDOM() LIMIT 0,10;");
		while($text = $this->db->fetchArray())
			if(count(explode(" ", $text['message'])) <= $words_limit)
				return $text['message'];
		return $dafault;
	}
	
	private function buildArray()
	{
		$answers = array();
		$i = 0;
		while($data = $this->db->fetchArray())
		{
			$answers[$i]['lexeme'] = $data['lexeme'];
			$answers[$i]['count'] = $data['count'];
			$i++;
		}
		return $answers;
	}

	private function testText(& $text)
	{
		$this->log->log("$text before test to lexems", PichiLog::LEVEL_VERBOSE);
		$text = str_replace("\n", "", $text);
		$text = str_replace("#beg#", "", $text);
		$text = str_replace("#end#", "", $text);
		$test1 = explode(",",$text);
		$test2 = explode(":",$text);
		$ignore_nick = array();
		$this->db->query("SELECT nick FROM users WHERE status='available';");
		while($u = $this->db->fetchArray())
			$ignore_nick[] = $u['nick'];
		if(in_array($test1[0], $ignore_nick))
		{
			unset($test1[0]);
			$text = implode(",",$test1);
			ltrim($text);
		}
		if(in_array($test2[0], $ignore_nick))
		{
			unset($test2[0]);
			$text = implode(":",$test2);
			ltrim($text);
		}
		while(strpos($text,'  ') !== FALSE)
			$text = str_replace('  ', ' ', $text);
		
		$this->log->log("$text after test to lexems", PichiLog::LEVEL_VERBOSE);
	}
}

?>
