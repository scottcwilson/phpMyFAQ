<?php
/**
* $Id: ajax.verifyurl.php,v 1.1 2005-10-01 14:40:17 thorstenr Exp $
*
* AJAX: verifyurl
*
* Usage:
*   index.php?uin=<uin>&aktion=ajax&ajax=verifyURL&id=<id>&lang=<lang>
*
* Performs link verification when entries are shown in record.show.php
*
* @author           Minoru TODA <todam@netjapan.co.jp>
* @since            2005-09-30
* @copyright        (c) 2005 NetJapan, Inc.
*
*
* The contents of this file are subject to the Mozilla Public License
* Version 1.1 (the "License"); you may not use this file except in
* compliance with the License. You may obtain a copy of the License at
* http://www.mozilla.org/MPL/
* 
* Software distributed under the License is distributed on an "AS IS"
* basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
* License for the specific language governing rights and limitations
* under the License.
*
* The Initial Developer of the Original Code is released for external use 
* with permission from NetJapan, Inc. IT Administration Group.
*/

if (!defined('IS_VALID_PHPMYFAQ_ADMIN')) {
    header("HTTP/1.0 401 Unauthorized");
    header("Status: 401 Unauthorized");
    exit();
}

@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
@header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate");
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache");
@header("Content-type: text/html");
@header("Vary: Negotiate,Accept");

$linkverifier = new link_verifier();		
if ($linkverifier->isReady() == FALSE) {
    ob_clean();
    print "disabled";
    exit();
}

$linkverifier->loadConfigurationFromDB();

if (isset($_REQUEST["id"]) && is_numeric($_REQUEST["id"])) {
    $id = $_REQUEST["id"];
}

if (isset($_REQUEST["lang"])) {
    $lang = $_REQUEST["lang"];
}

if (!(isset($id) && isset($lang))) {
    //header("X-DenyReason: id/lang bad");
    header("HTTP/1.0 401 Unauthorized");
    header("Status: 401 Unauthorized");
    exit();
}

if (($content = getEntryContent($id, $lang)) === FALSE) {
    //header("X-DenyReason: no content");
    header("HTTP/1.0 401 Unauthorized");
    header("Status: 401 Unauthorized");
    exit();
}

ob_clean();

$linkverifier->parse_string($content);
$linkverifier->VerifyURLs($PMF_CONF['referenceURL']);
$linkverifier->markEntry($id, $lang);
print $linkverifier->getLinkStateString();
exit();

function getEntryContent($id = 0, $lang = "") {
    global $db;

    $query = "SELECT content FROM ".SQLPREFIX."faqdata WHERE id = ".$id." AND lang='".$db->escape_string($lang)."'";
    $result = $db->query($query);
    if ($db->num_rows($result) != 1) {
        return FALSE;
    }

    list($content) = $db->fetch_row($result);
    return $content;
}