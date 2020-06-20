<?php
// Max Base
// https://github.com/BaseMax/telegram-github-trend
include "_base.php";
$repos=[];
$res=get("https://github.com/trending");
preg_match_all('/<h1 class="h3 lh-condensed">(\s*|)<a href=\"\/([^\/]+)\/([^\"]+)" data-hydro-click/i', $res[0], $_repos);
preg_match_all('/<p class="col-9 text-gray my-1 pr-4">(.*?|)<\/p>/is', $res[0], $_descs);
//print_r($_repos);
// print_r($_descs[1]);
$_descs=$_descs[1];
foreach($_descs as $i=>$_desc) {
	$_descs[$i]=strip_tags($_descs[$i]);
	$_descs[$i]=html_entity_decode($_descs[$i]);
	$_descs[$i]=trim($_descs[$i]);
}
// print_r($_descs);
if(!file_exists("db.txt")) {
	file_put_contents("db.txt", "");
}
$db=file_get_contents("db.txt");
$index=0;
foreach($_repos[2] as $i=>$_repo) {
	$rep=$_repo."/".$_repos[3][$i];
	if(strpos($db, "->".$rep."\n") !== false) {
		continue;
	}
	if(!isset($_descs[$index])) {
		$desc="";
	}
	else {
		$desc=$_descs[$index];
	}
	// $_descs[$i][1]=trim(html_entity_decode(strip_tags($_descs[$i][1])));
	$pat='/<span itemprop=\"programmingLanguage\">([^<]+|)<\/span>(\s*|)<\/span>(\s*|)<a class=\"(\s*|)muted-link d-inline-block mr-3\" href="\/'.str_replace("/", "\/", $rep).'/is';
	// print $pat."\n";
	preg_match($pat, $res[0], $_lang);
	// print_r($_lang);
	if($rep != "") {
		$repos[]=[
			"language"=>isset($_lang[1]) ? $_lang[1] : "",
			"description"=>$desc,
			"repository"=>$rep,
			"link"=>"https://github.com/".$rep,
		];
	}
	$index++;
}
// $argv[1] ==> message
// $argv[1] ==> test
if(isset($argv[1]) and $argv[1] == "test") {
	print_r($repos);
	exit();
}
foreach($repos as $repo) {
	$message=$repo["link"];
	if($repo["language"] != "") {
		if($repo["language"] == "C#") {$repo["language"]="CSharp";}
		else if($repo["language"] == "C++") {$repo["language"]="Cpp";}
		$repo["language"]=str_replace("-", "_", $repo["language"]);
		$repo["language"]=trim(str_replace(" ", "_", $repo["language"]));
		$message.="\n#".$repo["language"];
	}
	//if(strlen($message) + strlen($repo["description"])+1 <= 280) {
		$message=$repo["description"]."\n".$message;
	//}
	if(isset($argv[1]) and $argv[1] == "message") {
		print $message."\n\n";
		continue;
	}
	if($message != "") {
    sendMessage(-1001175872460/*-1001301509041*/, $message);// Channel ID
    file_put_contents("db.txt", "->".$repo["repository"]."\n", FILE_APPEND);
    sleep(120);
	}
}
