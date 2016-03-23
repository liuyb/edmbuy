$(document).ready(function  () {
	
	$(window).scroll(function (){
		if($(window).scrollTop()>=200)
		{
			$(".backtop").fadeIn();
		}
		else{
			$(".backtop").fadeOut();
		}

	});

	$(".backtop").on("click",function(event){
		$('html,body').animate({scrollTop:0},500);
		return false;
	})

})