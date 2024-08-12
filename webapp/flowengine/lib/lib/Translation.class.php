<?php
class translation
{
	public function IsLeftAligned()
	{
		try
		{
			$locale = sfContext::getInstance()->getUser()->getCulture();

			$q = Doctrine_Query::create()
			   ->from("ExtLocales a")
			   ->where("a.locale_identifier LIKE ?", "%".$locale."%");
			$locale = $q->fetchOne();

			if($locale && $locale->getTextAlign())
			{
				return false;
			}
			else
			{
				return true;
			}
		}catch(Exception $ex)
		{
			//error_log($ex);
		}
	}

	public function getTranslation($tablename,$fieldname,$fieldid)
	{
		try
		{
			$fieldid = ($fieldid) ? $fieldid : 0;

			$locale = sfContext::getInstance()->getUser()->getCulture();

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
			{
				return $translation->getTrlContent();
			}
			else
			{
				return false;
			}
		}catch(Exception $ex)
		{
			//error_log($ex);
		}
	}

	public function getOptionTranslation($tablename,$fieldname,$fieldid,$optionid)
	{
		try
		{
			$fieldid = ($fieldid) ? $fieldid : 0;

			$locale = sfContext::getInstance()->getUser()->getCulture();

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.option_id = ? AND a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($optionid,$fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
			{
				return $translation->getTrlContent();
			}
			else
			{
				return false;
			}
		}catch(Exception $ex)
		{
			//error_log($ex);
		}
	}

	public function setTranslation($tablename,$fieldname,$fieldid,$value)
	{
		try
		{
			$locale = sfContext::getInstance()->getUser()->getCulture();

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
		    {
			  $translation->setTrlContent($value);
			  $translation->save();
		    }
		    else
		    {
			  $translation = new ExtTranslations();
			  $translation->setLocale($locale);
			  $translation->setTableClass($tablename);
			  $translation->setFieldName($fieldname);
			  $translation->setFieldId($fieldid);
			  $translation->setTrlContent($value);
			  $translation->save();
		    }
			}catch(Exception $ex)
			{
				//error_log($ex);
			}
	}

	public function setOptionTranslation($tablename,$fieldname,$fieldid,$optionid,$value)
	{
		try
		{
			$locale = sfContext::getInstance()->getUser()->getCulture();

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.option_id = ? AND a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($optionid,$fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

		    if($translation)
		    {
		      $translation->setTrlContent($value);
			  $translation->save();
		    }
		    else
		    {
		      $translation = new ExtTranslations();
			  $translation->setLocale($locale);
			  $translation->setTableClass($tablename);
			  $translation->setFieldName($fieldname);
			  $translation->setFieldId($fieldid);
			  $translation->setOptionId($optionid);
			  $translation->setTrlContent($value);
			  $translation->save();
		    }
			}catch(Exception $ex)
			{
				//error_log($ex);
			}
	}

	public function getTranslationManual($tablename,$fieldname,$fieldid, $locale)
	{
		try
		{
			$fieldid = ($fieldid) ? $fieldid : 0;

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
			{
				return $translation->getTrlContent();
			}
			else
			{
				return false;
			}
		}catch(Exception $ex)
		{
			//error_log($ex);
		}
	}

	public function getOptionTranslationManual($tablename,$fieldname,$fieldid,$optionid, $locale)
	{
		try
		{
			$fieldid = ($fieldid) ? $fieldid : 0;

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.option_id = ? AND a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($optionid,$fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
			{
				return $translation->getTrlContent();
			}
			else
			{
				return false;
			}
		}catch(Exception $ex)
		{
			//error_log($ex);
		}
	}

	public function setTranslationManual($tablename,$fieldname,$fieldid,$value, $locale)
	{
		try
		{
			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
		    {
			  $translation->setTrlContent($value);
			  $translation->save();
		    }
		    else
		    {
			  $translation = new ExtTranslations();
			  $translation->setLocale($locale);
			  $translation->setTableClass($tablename);
			  $translation->setFieldName($fieldname);
			  $translation->setFieldId($fieldid);
			  $translation->setTrlContent($value);
			  $translation->save();
		    }
		}catch(Exception $ex)
		{
			error_log($ex);
		}
	}

	public function setOptionTranslationManual($tablename,$fieldname,$fieldid,$optionid,$value, $locale)
	{
		try
		{
			$fieldid = ($fieldid) ? $fieldid : 0;

			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.option_id = ? AND a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($optionid,$fieldid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();

			if($translation)
			{
				$translation->setTrlContent($value);
			 	$translation->save();
			}
			else
			{
				$translation = new ExtTranslations();
				$translation->setLocale($locale);
				$translation->setTableClass($tablename);
				$translation->setFieldName($fieldname);
				$translation->setFieldId($fieldid);
				$translation->setOptionId($optionid);
				$translation->setTrlContent($value);
				$translation->save();
			}
		}catch(Exception $ex)
		{
			error_log($ex);
		}
	}
	
	//OTB patch - Get field translation
        public function getFieldTranslation($tablename,$fieldname,$formid,$optionid)
	{
		try
		{
			$fieldid = ($fieldid) ? $fieldid : 0;

			$locale = sfContext::getInstance()->getUser()->getCulture();

			//$dbconn = mysql_connect(sfConfig::get('app_mysql_host'),sfConfig::get('app_mysql_user'),sfConfig::get('app_mysql_pass'));
			//mysql_select_db(sfConfig::get('app_mysql_db'),$dbconn);

			//$sql = "SELECT * FROM ext_translations WHERE field_id = ".$formid." AND field_name = '".$fieldname."' AND table_class = '".$tablename."' AND locale = '".$locale."'"." AND option_id = ".$optionid;
                        //$rows = mysql_query($sql, $dbconn) or die($sql);
			$q = Doctrine_Query::create()
			   ->from("ExtTranslations a")
			   ->where("a.option_id = ? AND a.field_id = ? AND a.field_name = ? AND a.table_class = ?", array($optionid,$formid, $fieldname, $tablename))
			   ->andWhere("a.locale = ?", $locale);
			$translation = $q->fetchOne();
			if($translation)
			{
				return $translation->getTrlContent();
			}
			else
			{
				return false;
			}
		}catch(Exception $ex)
		{
			error_log($ex);
		}
	}

}
?>
