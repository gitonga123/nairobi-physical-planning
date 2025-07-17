<?php
/**
 * _comments_general template.
 *
 * General comments from all reviewers #Old, may no longer apply if _comments_reviewer is operational
 *
 * @package    backend
 * @subpackage applications
 * @author     Webmasters Africa / Thomas Juma (thomas.juma@webmastersafrica.com)
 */


if($application->getCirculationId())
{
    $objMyCirculation = new CCirculation();
	
     //--- open database
    $nConnection = mysql_connect($DATABASE_HOST, $DATABASE_UID, $DATABASE_PWD);

    if ($nConnection)
    {
        if (mysql_select_db($DATABASE_DB, $nConnection))
        {
            //--- get the single circulation form
            $query = "select * from cf_circulationform WHERE nid=".$application->getCirculationId();

            $nResult = mysql_query($query, $nConnection);

            if($nResult)
            {
                if (mysql_num_rows($nResult) > 0)
                {

                    $arrCirculationForm = mysql_fetch_array($nResult);

                }

            }

            $nSenderUserId = $arrCirculationForm['nsenderid'];

            //-----------------------------------------------

            //--- get history (all revisions)

            //-----------------------------------------------

            $arrHistoryData = array();

            $nMaxRevisionId = 0;

            $strQuery = "SELECT * FROM cf_circulationhistory WHERE ncirculationformid=".$application->getCirculationId()." ORDER BY nrevisionnumber DESC";

            $nResult = mysql_query($strQuery, $nConnection);

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    while (	$arrRow = mysql_fetch_array($nResult))

                    {

                        if ($nMaxRevisionId == 0)

                        {

                            $nMaxRevisionId = $arrRow["nid"];	

                        }

                        $arrHistoryData[$arrRow["nid"]] = $arrRow;

                    }

                }

            }

            

            if ($_REQUEST['nRevisionId'] == '')

            {

                $_REQUEST['nRevisionId'] = $nMaxRevisionId;

            }

            

            //-----------------------------------------------

            //--- get all users

            //-----------------------------------------------

            $arrUsers = array();

            $strQuery = "SELECT * FROM cf_user  WHERE bdeleted <> 1";

            $nResult = mysql_query($strQuery, $nConnection);

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    while (	$arrRow = mysql_fetch_array($nResult))

                    {

                        $arrUsers[$arrRow["nid"]] = $arrRow;

                    }

                }

            }

            

            //-----------------------------------------------

            //--- get the mailing list

            //-----------------------------------------------

            $query = "select * from cf_mailinglist WHERE nid=".$arrCirculationForm["nmailinglistid"];

            $nResult = mysql_query($query, $nConnection);

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    $arrMailingList = mysql_fetch_array($nResult);

                }

            }



            $nMailingListID = $arrMailingList['nid'];

            

            //-----------------------------------------------

            //--- get the template

            //-----------------------------------------------	            

            $strQuery = "SELECT * FROM cf_formtemplate WHERE nid=".$arrMailingList["ntemplateid"];

            $nResult = mysql_query($strQuery, $nConnection);

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    $arrTemplate = mysql_fetch_array($nResult);

                    $strTemplateName = $arrTemplate["strname"];

                }

            }

            

            //-----------------------------------------------

            //--- get the form slots

            //-----------------------------------------------	            

            $arrSlots = array();

            $strQuery = "SELECT * FROM cf_formslot WHERE ntemplateid=".$arrMailingList["ntemplateid"]."  ORDER BY nslotnumber ASC";

            $nResult = mysql_query($strQuery, $nConnection);

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    while (	$arrRow = mysql_fetch_array($nResult))

                    {

                        $arrSlots[] = $arrRow;

                    }

                }

            }

            

            //-----------------------------------------------

            //--- get the field values

            //-----------------------------------------------	

                        

            $arrValues = array();

            $strQuery = "SELECT * FROM cf_fieldvalue WHERE nformid=".$application->getCirculationId();

            $nResult = mysql_query($strQuery, $nConnection);

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    while (	$arrRow = mysql_fetch_array($nResult))

                    {

                        $arrValues[$arrRow["ninputfieldid"]."_".$arrRow["nslotid"]] = $arrRow;

                    }

                }

            }

            

            //-----------------------------------------------

            //--- get the form process detail

            //-----------------------------------------------	            

            $arrProcessInformation = array();

            $arrProcessInformationSubstitute = array();

            

            $strQuery = "SELECT * FROM cf_circulationprocess WHERE ncirculationformid=".$application->getCirculationId()." ORDER BY dateinprocesssince";

            $nResult = mysql_query($strQuery, $nConnection) or die ($strQuery."<br>".mysql_error());

            

            

            if ($nResult)

            {

                if (mysql_num_rows($nResult) > 0)

                {

                    $nPosInSlot = -1;

                    $nLastSlotId = -1;

                    while (	$arrRow = mysql_fetch_array($nResult))

                    {

                        if ($arrRow["nissubstitiuteof"] != 0)

                        {

                            if ($arrRow["nslotid"] != $nLastSlotId)

                            {

                                $nLastSlotId = $arrRow["nslotid"];	

                                $nPosInSlot = -1;

                            }

                            //$nPosInSlot++;

                            $arrProcessInformationSubstitute[$arrRow["nissubstitiuteof"]] = $arrRow;

                        }

                        else

                        {

                            if ($arrRow["nslotid"] != $nLastSlotId)

                            {

                                $nLastSlotId = $arrRow["nslotid"];	

                                $nPosInSlot = -1;

                            }

                            $nPosInSlot++;

                            $arrProcessInformation[$arrRow["nuserid"]."_".$arrRow["nslotid"]."_".$nPosInSlot] = $arrRow;

                        }

                    }    				

                }

            }

        }

    }

    

    $nConnection = mysql_connect($DATABASE_HOST, $DATABASE_UID, $DATABASE_PWD);

    if ($nConnection)

    {

        if (mysql_select_db($DATABASE_DB, $nConnection))

        {

            foreach ($arrSlots as $arrSlot)

            {

                $q = Doctrine_Query::create()

                     ->from('CfFieldvalue a')

                     ->where('a.nslotid = ?', $arrSlot['nid'])

                     ->andWhere('a.nformid = ?', $application->getCirculationId());

                $fielddata = $q->execute();

                if(sizeof($fielddata) > 0)

                {

                ?>

                <h4><a href="#"><?php echo $arrSlot['strname']; ?></a></h4>

                <div>

                    <table width="100%">

                    <tr><td style="font-weight: bold;background: #666666; color: #fff;  height: 40px;" colspan="5"><h1 style="margin: 0px; padding: 0px; font-size: 18px; font-family:'Palatino Linotype', 'Book Antiqua', Palatino, serif;"><?php echo $arrSlot['strname']; ?></h1></td></tr>

                    <tr>

                    <?php

                        $strQuery = "SELECT * FROM cf_inputfield INNER JOIN cf_slottofield ON cf_inputfield.nid = cf_slottofield.nfieldid WHERE cf_slottofield.nslotid = ".$arrSlot["nid"]."  ORDER BY cf_slottofield.nposition ASC";

                        $nResult = mysql_query($strQuery, $nConnection) or die ($strQuery."<br>".mysql_error());

                        if ($nResult)

                        {

                            if (mysql_num_rows($nResult) > 0)

                            {

                                $nRunningCounter = 1;

                                while (	$arrRow = mysql_fetch_array($nResult))

                                {

                                    if($arrRow["ntype"] == "10")

                                                                    {

                                                                        echo "<td width=\"100%\" valign=\"top\" align=\"left\" style=\" text-align: left;\"><h2 style='margin: 0px; padding: 5px; font-size: 17px; font-family:\"Palatino Linotype\", \"Book Antiqua\", Palatino, serif;'>".htmlentities($arrRow["strname"])."</h2>";

                                                                    }

                                                                    else if($arrRow["ntype"] == "11")

                                                                    {

                                                                        echo "<td width=\"100%\" valign=\"top\" align=\"left\" style=\" text-align: left;\"><h3 style='margin: 0px; padding: 5px; font-size: 16px; font-family:\"Palatino Linotype\", \"Book Antiqua\", Palatino, serif;'>".htmlentities($arrRow["strname"])."</h3>";

                                                                    }

                                                                    else

                                                                    {

                                                                        echo "<td width=\"100%\" valign=\"top\" align=\"left\" style='text-align: left;background-color: #CCCCCC; border: 1px solid silver; padding: 5px;'><div style='margin-bottom: -2px; font-size: 14px; font-family:\"Palatino Linotype\", \"Book Antiqua\", Palatino, serif;'><b>".htmlentities($arrRow["strname"])."</b></div><br>";

                                                                    }

                                                                    

                                                                    

                                    if ($arrRow["ntype"] == 1)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            $arrValue = split('rrrrr',$arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]);

                                            

                                            

                                            $output = replaceLinks($arrValue[0]); 

                                            if ($arrRow['strbgcolor'] != "") {

                                                $output = '<span style="background-color: #'.$arrRow['strbgcolor'].'">'.$output.'<span>';

                                            }																

                                            echo $output; 

                                        }

                                        else

                                        {

                                            $arrValue = split('rrrrr',$arrRow['strstandardvalue']);

                                            

                                            $output = replaceLinks($arrValue[0]); 

                                            if ($arrRow['strbgcolor'] != "") {

                                                $output = '<span style="background-color: #'.$arrRow['strbgcolor'].'">'.$output.'<span>';

                                            }																

                                            echo $output;

                                        }

                                    }

                                    else if ($arrRow["ntype"] == 2)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"] != "on")

                                        {

                                            $state = "inactive";

                                        }

                                        else

                                        {

                                            $state = "active";

                                        }

                                        

                                        echo "<img src=\"/asset_misc/assets_backend/images/$state.gif\" height=\"16\" width=\"16\">";

                                    }

                                    else if ($arrRow["ntype"] == 3)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            $arrValue = split('xx',$arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]);								

                                            $nNumGroup 	= $arrValue[1];														

                                            $arrValue1 = split('rrrrr',$arrValue[2]);														

                                            $strMyValue	= $arrValue1[0];

                                        }

                                        else

                                        {

                                            $arrValue = split('xx',$arrRow['strstandardvalue']);								

                                            $nNumGroup 	= $arrValue[1];														

                                            $arrValue1 = split('rrrrr',$arrValue[2]);														

                                            $strMyValue	= $arrValue1[0];

                                        }

                                        $output = replaceLinks($strMyValue); 

                                        if ($arrRow['strbgcolor'] != "") {

                                            $output = '<span style="background-color: #'.$arrRow['strbgcolor'].'">'.$output.'<span>';

                                        }																

                                        echo $output;

                                    }

                                    else if ($arrRow["ntype"] == 4)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            $arrValue = split('xx',$arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]);

                                            $nDateGroup 	= $arrValue[1];

                                            $arrValue2 = split('rrrrr',$arrValue[2]);

                                            $strMyValue 	= $arrValue2[0];

                                        }

                                        else

                                        {

                                            $arrValue 		= split('xx',$arrRow['strstandardvalue']);

                                            $nDateGroup 	= $arrValue[1];

                                            $arrValue2 		= split('rrrrr',$arrValue[2]);

                                            $strMyValue 	= $arrValue2[0];

                                        }

                                        $output = replaceLinks($strMyValue); 

                                        if ($arrRow['strbgcolor'] != "") {

                                            $output = '<span style="background-color: #'.$arrRow['strbgcolor'].'">'.$output.'<span>';

                                        }																

                                        echo $output;

                                    }

                                    else if ($arrRow["ntype"] == 5)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            echo replaceLinks($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]);

                                        }

                                        else

                                        {

                                            echo replaceLinks($arrRow['strstandardvalue']);

                                        }

                                    }

                                    else if ($arrRow["ntype"] == 6)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            $strValue = $arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"];

                                            $arrMySplit = split('---', $strValue);

                                            

                                            if ($arrMySplit[1] > 1)

                                            {	// edited field values

                                                

                                                $strValue = '';

                                                $nMax = (sizeof($arrMySplit));

                                                for ($nIndex = 3; $nIndex < $nMax; $nIndex = $nIndex + 2)

                                                {

                                                    $strValue .= $arrMySplit[$nIndex].'---';

                                                }

                                                $keyId = rand(1, 150);

                                            }

                                            else

                                            {	// we have to use the standard value

                                                $strValue = $arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"];

                                                $keyId = rand(1, 150);

                                            }

                                        }

                                        else

                                        {

                                            $strValue = $arrRow['strstandardvalue'];

                                        }

                                        

                                        $nInputfieldID 	= $arrRow["nfieldid"];

                                        $bIsEnabled 	= 0;

                                        

                                        $strEcho = $objMyCirculation->getRadioGroup2($nInputfieldID,$application->getCirculationId(),$arrSlot['nid'], $strValue, $bIsEnabled, $keyId, $nRunningCounter);

                                        

                                        echo $strEcho;

                                    }

                                    else if ($arrRow["ntype"] == 7)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                        $strValue = $arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"];

                                            $arrMySplit = split('---', $strValue);

                                            

                                            if ($arrMySplit[1] > 1)

                                            {	// edited field values

                                                

                                                $strValue = '';

                                                $nMax = (sizeof($arrMySplit));

                                                for ($nIndex = 3; $nIndex < $nMax; $nIndex = $nIndex + 2)

                                                {

                                                    $strValue .= $arrMySplit[$nIndex].'---';

                                                }

                                                $keyId = rand(1, 150);

                                            }

                                            else

                                            {	// we have to use the standard value

                                                $strValue = $arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"];

                                                $keyId = rand(1, 150);

                                            }

                                        }

                                        else

                                        {

                                            $strValue = $arrRow['strstandardvalue'];

                                        }

                                        

                                        $nInputfieldID 	= $arrRow["nfieldid"];

                                        $bIsEnabled 	= 0;

                                        

                                        

                                        $strEcho = $objMyCirculation->getCheckboxGroup2($nInputfieldID,$application->getCirculationId(),$arrSlot['nid'], $strValue, $bIsEnabled, $keyId, $nRunningCounter);

                                        

                                        echo $strEcho;										

                                    }

                                    elseif($arrRow["ntype"] == 8)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            $strValue = $arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"];

                                            $arrMySplit = split('---', $strValue);

                                            

                                            if ($arrMySplit[1] > 1)

                                            {	// edited field values

                                                

                                                $strValue = '';

                                                $nMax = (sizeof($arrMySplit));

                                                for ($nIndex = 3; $nIndex < $nMax; $nIndex = $nIndex + 2)

                                                {

                                                    $strValue .= $arrMySplit[$nIndex].'---';

                                                }

                                                $keyId = rand(1, 150);

                                            }

                                            else

                                            {	// we have to use the standard value

                                                $strValue = $arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"];

                                                $keyId = rand(1, 150);

                                            }

                                        }

                                        else

                                        {

                                            $strValue = $arrRow['strstandardvalue'];

                                        }

                                        

                                        $nInputfieldID 	= $arrRow["nfieldid"];

                                        $bIsEnabled 	= 0;

                                        

                                        

                                        $strEcho = $objMyCirculation->getComboBoxGroup2($nInputfieldID,$application->getCirculationId(),$arrSlot['nid'], $strValue, $bIsEnabled, $keyId, $nRunningCounter);

                                        

                                        echo $strEcho;

                                    }

                                    elseif($arrRow["ntype"] == 9)

                                    {

                                        if ($arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]!='')

                                        {

                                            $arrSplit = split('---',$arrValues[$arrRow["nfieldid"]."_".$arrSlot["nid"]]["strfieldvalue"]);

                                        }

                                        else

                                        {

                                            $arrSplit = split('---',$arrRow['strstandardvalue']);

                                        }

                                        

                                        $nNumberOfUploads 	= $arrSplit[1];

                                        $strDirectory		= $arrSplit[2].'_'.$nNumberOfUploads;

                                        

                                        $arrValue22 = split('rrrrr',$arrSplit[3]);

                                        

                                        $strFilename		= $arrValue22[0];

                                        

                                        $strUploadPath 		= '/asset_uplds/';

                                        $strLink			= $strUploadPath.$strDirectory.'/'.$strFilename;

                                        

                                        echo "<a href=\"$strLink\" target=\"_blank\">$strFilename</a>";

                                    }

                                    

                                    echo "</td></tr>\n<tr>";

                                                                        

                                    

                                    

                                    $nRunningCounter++;

                                }

                                echo "<td></td>";

                            }

                        }

                        

                        ?>

                    </tr>

                    </table>

                </div>

                <?php

                }

            }

        }

    }

}
?>