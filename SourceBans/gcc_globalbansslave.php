<?php
/*
+--------------------------------------------------------------------------
|   GCC Global Ban System v1.0.0
|   =============================================
|   by Charles_, Thomasjosif
|   Copyright 2017 Gaming Community Central
|   https://gamingcommunitycentral.com
+--------------------------------------------------------------------------
*/
define('KEY', 'CREATE_NEW_KEY');
define('ROOT', dirname(__FILE__) . "/");
define('IN_SB', true);

// ---------------------------------------------------
//  Sourcebans config file
// ---------------------------------------------------
if(!file_exists(ROOT.'/config.php') || !include_once(ROOT . '/config.php')) {
	// Config file does not exist / sourcebans hasn't been installed properly.
	die("Config file not found.");
}

if(!isset($_GET["KEY"])) {
    die("No key provided. Check your KEY= parameter");
}
if($_GET["KEY"] != KEY) {
    die("Invalid key. Please provide a correct key.");
}
if(!isset($_GET["STEAMID"])) {
    die("No SteamID provided. Check your STEAMID= parameter");
}

try
{
    /*
        Variables from Sourcebans config:
        DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_PREFIX
    
    	Database fields: 

    	RemoveType:
    		E - Expired 
    		U - Unbanned (This is a manual action)
    		NULL - Nothing (Ban still active)
    	Type:
    		0 - Regular Ban
    		1 - IP Ban (Do not want to use this at the moment)
    	
    	We're selecting the banid as we can use it to link back to the ban EG. Where 123 is the banid.
    	http://skynetgaming.net/bans/index.php?p=banlist&advSearch=123&advType=bid
    */
    
    $servername = DB_HOST;
    $dbname = DB_NAME;
    $username = DB_USER;
    $password = DB_PASS;
    
	$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
	$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	
    $steamid = $_GET["STEAMID"];
    $stmt = $conn->prepare("SELECT bid, authid, reason FROM `" . DB_PREFIX . "_bans` WHERE RemoveType IS NULL AND type = '0' AND aid = '0' AND (authid LIKE :steamid) LIMIT 1;");
    $stmt->bindValue(':steamid', '%' . $steamid);
	
	$stmt->execute();
	$row = $stmt->fetch(PDO::FETCH_ASSOC);
	if($row){
	    echo json_encode($row);
	} else {
	   echo json_encode("OK"); 
	}
	
}
catch(PDOException $e)
{
    echo "Error: " . $e->getMessage();
}

$conn = null;
$stmt = null;