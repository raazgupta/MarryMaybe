<?php 
	session_start();
	
	$app_id = "xxx";
    $app_secret = "xxx";
    $my_url = "http://m.facebook.com/apps/marrymaybe/";
    
    
	$code = $_REQUEST["code"];
	
	if(empty($code)) {
        $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
        $dialog_url = "https://m.facebook.com/dialog/oauth?client_id=" 
        . $app_id . "&redirect_uri=" . urlencode($my_url) . "&scope=friends_relationships&state="
        . $_SESSION['state'];
        
        echo("<script> top.location.href='" . $dialog_url . "'</script>");
	}
	
	function getData(){
		global $app_id, $app_secret, $my_url, $code;
        if($_REQUEST['state'] == $_SESSION['state']) {
            $token_url = "https://graph.facebook.com/oauth/access_token?"
            . "client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)
            . "&client_secret=" . $app_secret . "&code=" . $code;
            
            
            $response = @file_get_contents($token_url);
            
            if($response == false)
            {
                echo("<script> top.location.href='http://apps.facebook.com/mymarriedfriends/'</script>");
            }
            
            $params = null;
            parse_str($response, $params);
            
            $graph_url = "https://graph.facebook.com/me/friends?fields=name,relationship_status,username&access_token=" 
            . $params['access_token'];
            $graphResults = @file_get_contents($graph_url);
            
            if($graphResults == false)
            {
                echo("<script> top.location.href='http://apps.facebook.com/mymarriedfriends/'</script>");
            }
            
            $array = json_decode($graphResults,true);
            $data = $array["data"];
		}
		else {
            echo("Loading...");
		}
		return $data;
		
	}
	
	function printMarriedPercent($data){
		
        $numFriends = 0;
        $numMarried = 0;
        
        if(count($data) == 0) {
			throw new Exception('Loading...');
        }
        
        foreach($data as $friend){
			++$numFriends;
			$isMarried = false;
			if($friend["relationship_status"] == "Married") {	
				$isMarried = true;
				++$numMarried;
				//$username = $friend["username"];
				//echo("<a href='https://www.facebook.com/" . $username . "'><img src='https://graph.facebook.com/" . $friend["id"] . "/picture?type=square' /></a>");
			}
			
			//echo($numFriends . " " . $friend["name"] . " " . $friend["relationship_status"] . " " . $isMarried . "<br/>");
        }
        
        $marriedPercent = round($numMarried / $numFriends * 100);
        $firstSen = "";
        if($marriedPercent < 33){
			$firstSen = "Only " . "<span style='color:#3b5998; font-weight: bold;'>" . $marriedPercent . "% </span> of your friends are Married. You still got plenty of time!";
        }
        else if($marriedPercent > 33 && marriedPercent < 66) {
			$firstSen = "<span style='color:#3b5998; font-weight: bold;'>" . $marriedPercent . "% </span> of your friends are married. Looks like you are sitting on the fence";
        }
        else if($marriedPercent > 66) {
			$firstSen = "<span style='color:#3b5998; font-weight: bold;'>" . $marriedPercent . "% </span> of your friends are married! Time to find new friends :P";
        }
        return $firstSen;
		
    }
    
    function printMarriedImages($data)
    {
		
		if(count($data) == 0) {
			throw new Exception('Loading...');
        }
		
		foreach($data as $friend){
			if($friend["relationship_status"] == "Married") {	
				$username = $friend["username"];
				echo("<img src='https://graph.facebook.com/" . $friend["id"] . "/picture?type=square' />");
			}
        }
		
    }
    
    function printUnmarriedImages($data)
    {
		if(count($data) == 0) {
			throw new Exception('Loading...');
        }
        
		foreach($data as $friend){
			if($friend["relationship_status"] != "Married") {	
				$username = $friend["username"];
				echo("<img src='https://graph.facebook.com/" . $friend["id"] . "/picture?type=square'  />");
			}
        }
		
    }
    
    ?>

<html>
<head>
<meta name="apple-mobile-web-app-capable" content="yes" />
<?php try{$data = getData();} catch (Exception $e) {echo ($e->getMessage());} ?>
<link href='http://fonts.googleapis.com/css?family=Philosopher&v1' rel='stylesheet' type='text/css'>
<style type="text/css" title="styleid" media="all">
.mainContainer
{
    border-style: solid;
    border-color: #3b5998;
    margin-top: 125px;
}
.title
{
    text-align: center;
    margin-top: 20px;
color: #3b5998;
    font-weight: bold;
    font-size: x-large;
    font-family: 'Philosopher', arial;
}
.firstSentence
{
    text-align: center;
    margin-top: 20px;
    font-family: arial;
}
.firstImageStack
{
    margin-left: 100px;
    margin-top: 20px;
    margin-bottom: 20px;
width: 500px;
}
.imageTitle
{
    text-align: left;
    font-weight: bold;
    font-size: large;
    font-family: 'Philosopher', arial;
color: #3b5998;
    margin-top: 10px;
    margin-bottom: 10px;
}
.secondImageStack
{
    margin-top: 20px;
    margin-left: 100px;
    margin-bottom: 20px;
width: 500px;
}
.images
{
    margin-top: 10px;
}
</style>
</head>
<body onload="setTimeout(function() { window.scrollTo(0, 1) }, 100);"></body>
<div class='mainContainer'>
<div class='title'>Should I get Married?</div>
<div class='firstSentence'><?php try {echo(printMarriedPercent($data));} catch (Exception $e) { echo($e->getMessage());} ?></div>
<div class='firstImageStack'>
<div class='imageTitle'>Married</div>
<div class='images'><?php try {printMarriedImages($data);} catch (Exception $e) { echo($e->getMessage());}?></div>
</div>
<div class='secondImageStack'>
<div class='imageTitle'>Unmarried</div>
<div class='images'><?php try {printUnmarriedImages($data);} catch (Exception $e) { echo($e->getMessage());}?></div>
</div>


</div>
</body>
</html>