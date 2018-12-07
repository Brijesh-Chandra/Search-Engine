$(document).ready(function(){
	$(".result").on("click", function(){
		var id = $(this).attr("data-linkId");
		var url = $(this).attr("href");
		increaseLinkClicks(id, url);
		return false;
	});
	var grid = $(".imageResults");
	grid.on("layoutComplete", function(){
		$(".gridItem img").css("visibility", "visible");
	})
	grid.masonry({
		itemSelector: ".gridItem",
		fitWidth: true,
		columnWidth: 70,
		gutter: 5,
		isInitLayout: false
	});
	$("[data-fancybox]").fancybox({
		caption : function(instance, item){
			var caption = $(this).data('caption') || '';
			var src = $(this).data('siteurl') || '';
			if (item.type === 'image') {
				caption = (caption.length ? caption + '<br />' : '')
				 + '<br><a href="' + item.src + '">View image</a>'
				 + '<a href="' + src + '">Visit Page</a>';
			}
			return caption;
		},
		afterShow : function(instance, item){
			increaseImageClicks(item.src);
		}
	});
});

function validation() {
	var text = document.getElementById('searchTextId').value;
	var formid = document.getElementById('formId');
	var trimText = text.trim();
	if(trimText != ""){
		formid.action = "search.php?query="+trimText;
	} else{
		return false;		
	}
}

var timer;
function loadImage(src, className){
	var image = $("<img>");
	image.on("load", function(){
		$("." + className + " a").append(image);
		clearTimeout(timer);
		timer = setTimeout(function(){
			$(".imageResults").masonry();
		}, 500);		
	});
	image.on("error", function(){
		$("." + className).remove();
		$.post("ajax/setBroken.php", {src: src});
	});
	image.attr("src", src);
}

function increaseLinkClicks(linkId, url){
	$.post("ajax/updateLinkCount.php", {linkId: linkId})
	.done(function(result){
		if(result != ""){
			alert(result);
			return;
		}
		window.location.href = url;
	});
}

function increaseImageClicks(src){
	$.post("ajax/updateImageCount.php", {src: src})
	.done(function(){
		if (result != "") {
			alert(result);
			return;
		}
	});
}