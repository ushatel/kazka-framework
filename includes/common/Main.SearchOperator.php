<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


	include_once("includes/DatabaseClasses/Parts.Search.php");

  /**
   * class Word
   *
   * The class interpretes the Words in the vocabulary
   *
   * @package Common.pkg
   */
   class Word 
   {
   		public $word_id = 0;
    	public $word = "";
    	public $rating = 0;
   }

  /**
   * class SearchOperator
   *
   * The main class for work with Search operations (Search Processor)
   *
   * @package Common.pkg
   */
    
	class SearchOperator
	{
		public $page = NULL;

		private $search = NULL; 
				
		private $wordsArray = NULL;
		
		public $options = GLOBAL_SEARCH;  // GLOBAL_SEARCH - Searching everywhere
										  // ENTITY_SEARCH - Searching just inside the exact entity
		
		public $entity_code = "";
		
		function __construct()
		{}
		
		private function GroupWords($docsArray, $replace = false)
		{
			$result_array = array();

			$this->wordsArray = array();
			
			for($i = 0; $i < count($docsArray); $i++ )
			{
				$new_word = new Word();
				$new_word->word = (string)$docsArray[$i];
				
				$key = array_search($new_word->word, $result_array);
				if($key === FALSE)
				{
					$new_word->rating = 0;
					$result_array[$i] = $new_word->word;

					if($replace)
					{
						$this->wordsArray[$i] = $new_word;
					}
				}
				else
				{
					if($replace)
					{
						$word = $this->wordsArray[$key];
						$word->rating += 1;
						
						$this->wordsArray[$key] = $word;
					}
				}
			}
			
			return $result_array;
		}
		
		/**
		 * Розбирає документ на слова
		 *
		 * $index_data - масив ['object_id'], ['object_guid'], ['entity_id']
		 *               якщо NULL - не потрібно проводити ініціалізацію індексів 
		 */
		public function ParseDocument($string, $index_data = NULL, $langs_id = 0)
		{
			$newString = strtolower($string);
			$matches = NULL;

			//parses document for the words and puts the new words to the vocabulary
			if(preg_match_all('/\b(?<Word>\w+)\b/i', $newString, &$matches ))
			{				
				$this->GroupWords($matches[0], true);
			
				/*
				echo "<pre>";
				print_r($matches); echo "uuuu<br/><br/>kl;lk;lk";
				print_r($this->wordsArray);
				echo "</pre>";
				*/
			
				foreach($this->wordsArray as $word)
				{
					$search = new Search();
					
					$search->word = $word->word;
					$search->words_rating = (float)$word->rating;
					$search->words_langs_id = (float)$langs_id;

					$new_word = $search->AddWordIfNotExists();
					
					if($index_data != NULL)
					{
						if($new_word !== FALSE)
						{
							$search->UpdateWordsRating();
						}
											
						$search->index_words_id = (float)$search->words_id;
						$search->index_entities_id = (float)$index_data['entity_id'];
						$search->index_quantity = (float)$word->rating;
						$search->index_langs_id = (float)$langs_id;
						$search->index_object_id = (float)$index_data['object_id'];
						$search->index_object_guid = substr($index_data['object_guid'], 0, 36);
						
						$search->AddOrModifyIndex();
					}
				}
			}
		}
		
		public function SearchRequest($request = "")
		{
			$newString = strtolower($request);
			$matches = NULL;
		
			if(preg_match_all('/\b(?<Word>\w+)\b/i', $newString, &$matches ))
			{
				$this->GroupWords($matches[0], true);
			}
		}
	}
?>