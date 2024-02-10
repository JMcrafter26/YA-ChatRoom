/**
 * CheetahChat 1.0b
 * simple php chat room
 * Hamid Reza Samak
 * https://github.com/hamidsamak/cheetahchat
 */

var name = "";
var last = 0;

function login(){
	$.get("index.php", { action: "login", name: name }, function(data){
		name = data;
	});
}

function users(){
	$.get("index.php", { action: "users" }, function(data){
		data = eval("(" + data + ")");

		$("aside").html("<ul></ul>");
		
		for (i in data)
			$("aside ul").append("<li>" + data[i].name + " <span>" + data[i].ip + "</span></li>");

		$(window).resize();
		setTimeout(function(){ users(); }, 10000);
	}).fail(function(){
		$("section ul").append("<li class=\"text-red\"><strong>Error:</strong> Could not retrieve users list.</li>");
	});;
}

function chats(){
	$.get("index.php", { action: "chats", name: name, last: $("section").attr("data-last") }, function(data){
		var section = $("section");
		data = eval("(" + data + ")");

		if (section.html().length < 1)
			section.html("<ul></ul>");
		
		for (i in data) {
			section.find("ul").append("<li><strong>" + data[i].name + ":</strong> " + data[i].message + "</li>");

			last = data[i].id;
		}

		$("section").attr("data-last", last).animate({ scrollTop: $(document).height() }, "slow");

		$(window).resize();

		setTimeout(function(){ chats()}, 2500);
	}).fail(function(){
		$("section ul").append("<li class=\"text-red\"><strong>Error:</strong> Could not retrieve chats.</li>");
	});
}

$(function(){
	$(window).resize(function(){
		$("aside").css("height", $(window).height());

		var height = parseInt($(window).height() - $("input").height());
		height = height - (height / 19);
		
		$("section").css("height", height);
	});

	$(window).resize();
	
	while (name.trim().length < 1)	
		name = prompt("Please enter your name");

	if (name.length > 16)
		name = name.substring(0, 16);

	if (name.length > 0) {
		login();

		$("input").prop("disabled", false).focus();

		users();
		chats();
	}

	$("input").val("").keypress(function(event){
		var message = $(this).val().trim();

		if (event.which == 13) {
			if (message.length < 1)
				return false;

			$(this).prop("disabled", true);

			$.get("index.php", { action: "send", name: name, message: message }, function(){
				$("input").val("").prop("disabled", false).focus();
			});
		}
	})
});