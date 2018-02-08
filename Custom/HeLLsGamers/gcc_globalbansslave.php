<?php
/*
+--------------------------------------------------------------------------
|   GCC Global Ban System v1.0.0 - HeLLsGamers Version
|   =============================================
|   by Charles_, Thomasjosif
|   Copyright 2017 Gaming Community Central
|   https://gamingcommunitycentral.com
+--------------------------------------------------------------------------
*/
define('KEY', 'CHANGE_KEY');
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

function mg_StartsWith($Haystack, $Needle, $IsCaseSensitive = false)
{
    if($IsCaseSensitive)
        return(strcmp(substr($Haystack, 0, strlen($Needle)), $Needle) === 0);
    else
        return(strcasecmp(substr($Haystack, 0, strlen($Needle)), $Needle) === 0);
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
        https://hellsgamers.com/hgbans.php?i=123
    */
    
    $servername = DB_HOST;
    $dbname = DB_NAME;
    $username = DB_USER;
    $password = DB_PASS;
    
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $steamid = $_GET["STEAMID"];
    $stmt = $conn->prepare("SELECT bans.id as bid, bans.subject_steamid as authid, posts.content as reason FROM `bans` INNER JOIN posts ON bans.uuid=posts.ban_uuid WHERE bans.category='-1' AND (bans.approved_state = 1 OR bans.approved_state = 3) AND (((bans.datetime_added + bans.duration_seconds) > UNIX_TIMESTAMP()) OR bans.duration_seconds = 0) AND bans.admin_name='CONSOLE' AND (bans.subject_steamid = :steamid) LIMIT 1;");
    $stmt->bindValue(':steamid', $steamid);

    $stmt->execute();
    $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if($row){
        $row[0]['reason'] = (mg_StartsWith($row[0]['reason'], '__PLAIN__') ? substr($row[0]['reason'], 9) : gzinflate($row[0]['reason']));
        echo json_encode($row[0]);
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