<?php
session_start();
echo stripslashes($_SESSION['htmldata']);
if(isset($_SESSION['user']) && isset($_SESSION['deck'])){
	echo' <div align="center"><a href="" onclick="saveTransition()">Click to Save the Transition</a></div>';
}
?>

<script>
function saveTransition(){
var tmp,styles=new Array();
$('.mainslide').each(function(k,v){
	tmp='';
	if($('#'+v.id).attr('data-x')!='')
		tmp =' data-x='+$('#'+v.id).attr('data-x')+tmp;
	if($('#'+v.id).attr('data-y')!='')
		tmp =' data-y='+$('#'+v.id).attr('data-y')+tmp;
	if($('#'+v.id).attr('data-z')!='')
		tmp =' data-z='+$('#'+v.id).attr('data-z')+tmp;			
	if($('#'+v.id).attr('data-rotate')!='')
		tmp =' data-rotate='+$('#'+v.id).attr('data-rotate')+tmp;
		/*
	if($('#'+v.id).attr('data-rotate-x')!='')
		tmp =' data-rotate-x='+$('#'+v.id).attr('data-rotate-x')+tmp;
	if($('#'+v.id).attr('data-rotate-y')!='')
		tmp =' data-rotate-y='+$('#'+v.id).attr('data-rotate-y')+tmp;
		*/		
	if($('#'+v.id).attr('data-scale')!='')
		tmp =' data-scale='+$('#'+v.id).attr('data-scale')+tmp;	
	styles.push('style="'+$('#'+v.id).attr('style')+'" '+tmp);
});
$.ajax({
	  type: 'POST',
	  async:true,
	  url: './../../../../?url=transition/saveToDB',
	  data: 'styles='+encodeURIComponent(JSON.stringify(styles)),
	  success: function(){
	  }
	});
window.open('./../../../../?url=transition/saveToDB');
self.close ();
}
</script>