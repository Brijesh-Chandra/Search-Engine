<?php  
	include("config.php");
	include("classes/SiteResultsProvider.php");
	include("classes/ImageResultsProvider.php");
	$term = $_GET["query"];
	$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
	$page = isset($_GET["page"]) ? $_GET["page"] : 1;
?>
<!DOCTYPE html>
<html>
<head>
	<title>Search Engine - <?php echo $term ?> </title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.css"/>
	<link rel="shortcut icon" type="image/png" href="assets/images/favicon.png">
	<link rel="stylesheet" type="text/css" href="assets/css/style.css">
	<script
	  src="https://code.jquery.com/jquery-3.3.1.min.js"
	  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
	  crossorigin="anonymous">
  	</script>
</head>
<body>
	<div class="wrapper">
		<div class="header">
			<div class="headerContent">
				<div class="logoContainer">
					<a href="index.php">
						<img src="assets/images/searchengineLogo.png" alt="Site Logo">
					</a>
				</div>
				<div class="searchContainer">
					<form action="" method="GET" id="formId">
						<div class="searchBarContainer">
							<input type="hidden" name="type" value="<?php echo $type; ?>">
							<input class="searchBox" type="text" name="query" value="<?php echo $term ?>" id="searchTextId">
							<button class="searchPageButton" type="submit" onclick="validation()">
								<img src="assets/images/searchIcon.png">
							</button>
						</div>						
					</form>
				</div>
			</div>
			<div class="tabContainer">
				<ul class="tabList">
					<li class="<?php echo $type == 'sites' ? 'active' : '' ?>">
						<a href="search.php?query=<?php echo $term ?>&type=sites">
							Sites
						</a>
					</li>
					<li class="<?php echo $type == 'images' ? 'active' : '' ?>">
						<a href="search.php?query=<?php echo $term ?>&type=images">
							Images
						</a>
					</li>
				</ul>
			</div>
		</div>	
		<div class="mainResultsSection">
			<?php 
				if ($type == "images") {
					$resultsProvider = new ImageResultsProvider($con);
					$pageSize = 50;
				} else {					
					$resultsProvider = new SiteResultsProvider($con);
					$pageSize = 20;
				}				
				$numResults = $resultsProvider->getNumResults($term);
				echo "<p class='resultsCount'>$numResults results found.</p>";
				if ($numResults == 0) {
					echo "<p class='zeroResult'>
							Your search -<b> $term </b>- did not match any documents.<br>
							<br><br>Suggestions:<br>
							<ul class='zeroResultMessage'>
								<li> Make sure that all words are spelled correctly.</li><br>
								<li> Try different keywords.</li><br>
								<li> Try more general keywords.</li><br>
						</p>";
				}
				else{
					echo $resultsProvider->getResultsHtml($page, $pageSize, $term);
				}
			?>
		</div>	
		<div class="paginationContainer">
			<?php  
				if ($numResults == 0) {
					echo '<div class="pageNumberContainer">
							<img src="assets/images/noFindimage.png">
						</div>';
				}
				else{
					echo '<div class="pageButtons">
							<div class="pageNumberContainer">
								<img src="assets/images/pageStart.png">
							</div>';
					$pageToShow = 10; 
					$numPages = ceil($numResults/$pageSize);
					$pagesLeft = min($pageToShow, $numPages);
					$currentPage = $page - floor($pageToShow/2);
					if ($currentPage < 1) {
						$currentPage = 1;
					}
					if ($currentPage + $pagesLeft > $numPages + 1) {
						$currentPage = $numPages + 1 - $pagesLeft;
					}
					while ($pagesLeft != 0 && $currentPage <= $numPages) {
						if ($currentPage == $page) {
							echo "<div class = 'pageNumberContainer'>
									<img src='assets/images/pageSelected.png'>
									<span class='pageNumber'>$currentPage</span>
								</div>";
						}
						else {
							echo "<div class = 'pageNumberContainer'>
												<a href='search.php?query=$term&type=$type&page=$currentPage'>
													<img src='assets/images/page.png'>
													<span class='pageNumber'>$currentPage</span>
												</a>
											</div>";
						}					
						$currentPage++;
						$pagesLeft--;
					}  
					echo '<div class="pageNumberContainer">
								<img src="assets/images/pageEnd.png">
							</div>
						</div>';
				}
			?>			
		</div>
	</div>
	<script src="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.2/dist/jquery.fancybox.min.js"></script>
	<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>
	<script type="text/javascript" src="assets/js/script.js"></script>
</body>
</html>
