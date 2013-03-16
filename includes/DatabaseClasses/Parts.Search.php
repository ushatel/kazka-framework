<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");   
  include_once("includes/DatabaseClasses/Db.SearchVocabularyTbl.php"); 
  include_once("includes/DatabaseClasses/Db.SearchIndexTbl.php");
  
/**
 * The Package collects the materials related classes etc
 *
 * @package Search.pkg
 */

/**
 * class Search
 *
 * The main class of the Search. Used for the search operations.
 * 
 * @package Search.pkg
 */

class Search extends CommonClass
{
	private $citiesTblObj = NULL;
	private $companiesTblObj = NULL;
	private $contractorsTblObj = NULL;
	private $countriesTblObj = NULL;
	private $divisibilityTblObj = NULL;
	private $materialsQuantityTblObj = NULL;
	private $materialsTblObj = NULL;
	private $projectsTblObj = NULL;
	private $stepsTblObj = NULL;
	private $usersTblObj = NULL;
	
	private $searchVocabularyTblObj = NULL;
	private $searchIndexTblObj = NULL;
	
	public $word = '';
	public $words_rating = 0;
	public $words_langs_id = 0;
	public $words_id = 0;

	public $index_id = 0;
	public $index_words_id = 0;
	public $index_entities_id = 0;
	public $index_quantity = 0;
	public $index_langs_id = 0;
	public $index_object_id = 0;
	public $index_object_guid = '';
	
	public $searchText = "";
	public $searchConditions = array();
	
	function __construct()
	{
		$this->searchVocabularyTblObj = new SearchVocabularyTbl ();
		$this->searchIndexTblObj = new SearchIndexTbl();
	}
	
	public function CheckWord()
	{
		$this->isLoaded = false;
		
		$this->ParseFieldsToDataRow();
		$this->isLoaded = (bool)$this->searchVocabularyTblObj->GetWord();

		if($this->isLoaded)
		{
			$this->ParseDataRow();
		}
		
		return $this->isLoaded;
	}
	
	public function CreateWord()
	{
		$result = 0;
		
		$this->ParseFieldsToDataRow();
		$result = (float)$this->searchVocabularyTblObj->CreateWord();

		$this->words_id = $result;

		return $result;
	}
	
	/**
	 *  Перевіряє чи існує слово у словнику та додає його, якщо немає
	 */
	public function AddWordIfNotExists()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->CheckWord();
		
		if(!$this->isLoaded)
		{
			$result = $this->CreateWord();
		}
		else
		{
			$result = $this->isLoaded;
		}
		
		return $result; // TRUE, FALSE or WORDS_ID
	}
	
	public function UpdateWordsRating()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->searchVocabularyTblObj->IncWordRating();
		
		if($result != NULL)
		{
			$this->isLoaded = true;
			$this->ParseDataRow();
		}
		else 
		{
			$this->isLoaded = false;
		}		
		
		return $result;
	}

	/**
	 * Перевіряє чи існує вказаний індекс
	 */	
	public function CheckIndex()
	{
		
	}
	
	/**
	 * Додає новий індекс
	 */
	public function CreateIndex()
	{
		$result = 0;
		
		$this->ParseFieldsToDataRow();
		$result = (float)$this->searchIndexTblObj->CreateIndex();
		
		$this->index_id = $result;
		
		return $result;
	}
	
	/**
	 * Update index if exists
	 */
	public function UpdateIndex()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->searchIndexTblObj->UpdateIndex();
		
		if($result != NULL)
		{
			$this->isLoaded = true;
			$this->ParseDataRow();
		}
		else
		{
			$this->isLoaded = false;
		}
		
		return $this->isLoaded;
	}
	
	/**
	 * Перевіряє чи вже було заведено вказаний індекс та модифікує чи додає його
	 */	
	public function AddOrModifyIndex()
	{
		$result = NULL;

		$this->ParseFieldsToDataRow();

		$result = $this->UpdateIndex();
		
		if(!$this->isLoaded)
		{	
			$result = $this->CreateIndex();
		}
		
		return $result;
	}
	
	public function ParseDataRow()
	{		
		$this->word = $this->searchVocabularyTblObj->dataRow['NAME'];
		$this->words_rating = $this->searchVocabularyTblObj->dataRow['RATING'];
		$this->words_langs_id = $this->searchVocabularyTblObj->dataRow['LANGS_ID'];
		$this->words_id = $this->searchVocabularyTblObj->dataRow['WORDS_ID'];

		//ініціалізація значень індексів
		$this->index_id = $this->searchIndexTblObj->dataRow['SEARCH_INDEX_ID'];
		$this->index_words_id = $this->searchIndexTblObj->dataRow['WORDS_ID'];
		$this->index_entities_id = $this->searchIndexTblObj->dataRow['ENTITIES_ID'];
		$this->index_quantity = $this->searchIndexTblObj->dataRow['QUANTITY'];
		$this->index_langs_id = $this->searchIndexTblObj->dataRow['LANGS_ID'];
		$this->index_object_id = $this->searchIndexTblObj->dataRow['OBJECT_ID'];
		$this->index_object_guid = $this->searchIndexTblObj->dataRow['OBJECT_GUID'];
	}
	
	public function ParseFieldsToDataRow()
	{
		$this->searchVocabularyTblObj->dataRow['NAME'] = substr($this->word, 0, 150);
		$this->searchVocabularyTblObj->dataRow['RATING'] = (float)$this->words_rating;
		$this->searchVocabularyTblObj->dataRow['LANGS_ID'] = (float)$this->words_langs_id;
		$this->searchVocabularyTblObj->dataRow['WORDS_ID'] = (float)$this->words_id;
		
		// ініціалізування строки індексів
		$this->searchIndexTblObj->dataRow['SEARCH_INDEX_ID'] = (float)$this->index_id;
		$this->searchIndexTblObj->dataRow['WORDS_ID'] = (float)$this->index_words_id;
		$this->searchIndexTblObj->dataRow['ENTITIES_ID'] = (float)$this->index_entities_id;
		$this->searchIndexTblObj->dataRow['QUANTITY'] = (float)$this->index_quantity;
		$this->searchIndexTblObj->dataRow['LANGS_ID'] = (float)$this->index_langs_id;
		$this->searchIndexTblObj->dataRow['OBJECT_ID'] = (float)$this->index_object_id;
		$this->searchIndexTblObj->dataRow['OBJECT_GUID'] = substr($this->index_object_guid, 0, 36);
		
	}
}
 


?>