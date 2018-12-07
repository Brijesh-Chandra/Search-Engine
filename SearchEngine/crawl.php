<?php 
	include("config.php");
	include("classes/DomDocParser.php");

	$alreadyCrawled = array();
	$crawling = array();
	$alreadyFoundImages = array();

	function  linkExists($url){
		global $con;
		$query = $con->prepare("SELECT * FROM sites WHERE url = :url");
		$query->bindParam(":url", $url);
		$query->execute();
		return $query->rowCount() != 0;
	}

	function insertLink($url, $title, $description, $keywords){
		global $con;
		$query = $con->prepare("INSERT INTO sites(url, title, description, keywords)
								VALUES(:url, :title, :description, :keywords)");
		$query->bindParam(":url", $url);
		$query->bindParam(":title", $title);
		$query->bindParam(":description", $description);
		$query->bindParam(":keywords", $keywords);
		return $query->execute();
	}

	function insertImage($url, $src, $title, $alt){
		global $con;
		$query = $con->prepare("INSERT INTO images(url, src, title, alt)
								VALUES(:url, :src, :title, :alt)");
		$query->bindParam(":url", $url);
		$query->bindParam(":src", $src);
		$query->bindParam(":title", $title);
		$query->bindParam(":alt", $alt);
		return $query->execute();
	}

	function createLink($src, $url){
		$scheme = parse_url($url)["scheme"];
		$host = parse_url($url)["host"];
		if(substr($src, 0, 2) == "//"){ 
			$src = $scheme.":".$src;
		}
		elseif (substr($src, 0, 1) == "/") {
			$src = $scheme."://".$host.$src;
		}
		elseif (substr($src, 0, 2) == "./") {
			$src = $scheme."://".$host.dirname(parse_url($url)["path"]).substr($src, 1);
		}
		elseif (substr($src, 0, 3) == "../") {
			$src = $scheme."://".$host."/".$src;
		}
		elseif (substr($src, 0, 5) != "https" && substr($src, 0,4) != "http") {
			$src = $scheme."://".$host."/".$src;
		}
		return $src;
	}

	function getDetails($url){
		global $alreadyFoundImages;
		$parser = new DomDocParser($url);
		$titleArray = $parser->getTitleTags();
		if (sizeof($titleArray) == 0 || $titleArray->item(0) == NULL) {
			return;
		}
		$title = $titleArray->item(0)->nodeValue;
		$title = str_replace("\n", "", $title);
		if($title == ""){
			return;
		}
		$description = "";
		$keywords = "";
		$metaArray = $parser->getMetatags();
		foreach ($metaArray as $meta) {
			if($meta->getAttribute("name") == "description"){
				$description = $meta->getAttribute("content");
			}
			if($meta->getAttribute("name") == "keywords"){
				$keywords = $meta->getAttribute("content");
			}
		}
		$description = str_replace("\n", "", $description);
		$keywords = str_replace("\n", "", $keywords);
		if (linkExists($url)) {
			echo "$url already exists<br>";
		} 
		else if(insertLink($url, $title, $description, $keywords)) {
			echo "SUCCESS: $url<br>";
		}
		else {
			echo "ERROR: Failed to inser $url<br>";
		}	

		$imageArray = $parser->getImages();
		foreach ($imageArray as $image) {
			$src = $image->getAttribute("src");
			$title = $image->getAttribute("title");
			$alt = $image->getAttribute("alt");
			if (!$title && !$alt) {
				continue;
			}
			$src = createLink($src, $url);
			if (!in_array($src, $alreadyFoundImages)) {
				$alreadyFoundImages[] = $src;
				insertImage($url, $src, $alt, $title);
			}
		}	
	}

	function followLinks($url){
		global $alreadyCrawled;
		global $crawling;
		$parser = new DomDocParser($url);
		$linkList = $parser->getLinks();
		foreach ($linkList as $link) {
			$href = $link->getAttribute("href");
			if(strpos($href, "#") !== false){
				continue;
			}
			elseif (substr($href, 0, 11) =="javascript:") {
				continue;
			}
			$href = createLink($href, $url);
			if (!in_array($href, $alreadyCrawled)) {
				$alreadyCrawled[] = $href;
				$crawling[] = $href;
				getDetails($href);
			}
		}
		array_shift($crawling);
		foreach ($crawling as $site) {
			followLinks($site);

		}
		
	}

	$startUrl = ""; //https://www.__.com
	followLinks($startUrl);

?>