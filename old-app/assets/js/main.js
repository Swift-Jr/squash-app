$(document).ready(function(){
	$('.plusminus').click(function(e){
		e.preventDefault();
		var target = $('input[name*=' + $(this).attr('data-target') + ']');
		if($(this).attr('data-operation') == "p"){
			target.val(parseInt(target.val())+1);
		}else if(parseInt(target.val()) > 0){
			target.val(parseInt(target.val())-1);
		}
	});
});