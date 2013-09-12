<!-- twitter bootrap 1.3.0 styles -->
<link rel="stylesheet" href="libraries/frontend/twitter-bootstrap/bootstrap.min.css" media="all" />
<script type="text/javascript" src="libraries/frontend/jquery.min.js"></script>
<script type="text/javascript" src="libraries/frontend/jcarousellite/jquery.jcarousel.min.js"></script>
<link rel="stylesheet" href="libraries/frontend/jcarousellite/skins/ie7/skin.css" type="text/css" media="screen" />
<script type="text/javascript" src="libraries/frontend/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
<script type="text/javascript" src="libraries/frontend/jquery-jail/jail.0.9.5.min.js"></script>
	
<script type="text/javascript">
function fnGetDomain(url) {
	return (url.match(/:\/\/(.[^/]+)/)[1]).replace('www.','');
}
    //image uploading
function handleResponse(mes) {
    if (mes.errors != null) {
        $('#res').html("Errors: " + mes.errors);
    }   
    else {
        $('#res').html("File " + mes.name + " is uploaded"); 
        window.parent.$('#'+window.parent.$("#active_editor_id").text()).append('<div><img src="'+mes.src+'" /></div>');
        //apply scaling
        var whole_id=window.parent.$("#active_editor_id").text();
        var parts = whole_id.split("_");
        var selected_id=parts[2];
        if(parts[1]=='title' || parts[1]=='body'){
                window.parent.applyScaling(selected_id)	
        }
        parent.$.fancybox.close();
    }   
}
//for image manager
function insertImageToEditor(){
        
	if($('input#your_link').val()){
		if(fnGetDomain($('input#your_link').val())!='slidewiki.org'){
			$('#your_link').submit();
		}else{
			//no upload
			var mes = new Object;
			mes.src=$('input#your_link').val();
			handleResponse(mes);
		}
		
            //window.parent.$('#'+window.parent.$("#active_editor_id").text()).append('<div><img src="'+$('input#your_link').val()+'" /></div>');
	} else if ($('input#uploade_link').val()){
            $('#upload_image').submit();
        }
        
}
	$(function(){
		images = <?php echo json_encode($images); ?>;
		
		no_images = false;
		if(images.length == 0) {
			no_images = true;
		} else {
			no_images = false;
		}
		
		// make an array with URI
		uri_array = new Array();
		if(no_images) {
			// display error to the user
			alert('Your media library is empty.');
		} else {
			addImages(images);
		}
		
		/*$(".image_gallery").jCarouselLite({
			btnNext: ".next",
			btnPrev: ".prev",
			mouseWheel: true,
			visible: 8
		});*/
		
		$('#images').jcarousel();
		
		//$(".image_gallery").css("width", "100%");
		//$(".image_gallery").css("height", "200px");
		
		$('img').bind({
			click: function() {
				id = this.id.substring(3);
				uri = images[id].uri;
				original_width = images[id].original_width;
				original_height = images[id].original_height;
				size = original_width + "x" + original_height;
				$('#original_size').val(size);
				$('#enlarged_img').attr('data-href',uri + '?filter=Resize-width-250');
				$('#enlarged_img').jail();
				
				width = $("#defined_size").val();
				if(/^\d+$/.test(width)) {
					uri = uri + "?filter-Resize-width-" + width;
				}
				
				
				
				$('#your_link').val(uri)
			}
		});
		
	});
	
	function addImages(images) {
		for(var i = 0; i < images.length; i++) {
			string = "<li><img id= \"img" +i+ "\" src=\"" + images[i].uri + "?filter=Resize-width-150\" alt=\"Image\" width=\"150px\"></img></li>";
			$('#images').append(string);
		}	
	}

</script>

<header>
	<nav>
	</nav>
</header>

<article>
	<div class="image_gallery">
		<ul id="images" class="jcarousel-skin-ie7"></ul>
	</div>
	<div align="center"><img data-href="" id="enlarged_img" src="static/img/enlarged_image.png?filter=Resize-width-250"></div>
	
        <form enctype="multipart/form-data" id="upload_image" action="./?url=img/upload" method="POST" target="hiddenframe">
            <div class="clearfix">
                    <label id="optionsCheckboxes">Original size</label>									
                    <div class="input">
                            <input type="text" id="original_size" disabled="disabled" style="height:25px;width: 50px;" class="span10">
                    </div>
            </div>
            <div class="clearfix">	
                    <label id="optionsCheckboxes">Your desired width (in px)</label>									
                    <div class="input">
                            <input type="text" id="defined_size" style="height:25px;width: 50px;" class="span10">
                    </div>	
            </div>
            <div class="clearfix">
                    <label id="optionsCheckboxes">Image URL</label>									
                    <div class="input">
                            <input type="text" id="your_link" name="your_link" style="height:25px;width:250px;" class="span10">
                    </div>					
            </div>
            <div class="clearfix">
                    <label id="optionsCheckboxes">Upload Image</label>									
                    <div class="input">
                        <input type="file" id="upload_link" name="uploaded_img" style="height:25px;width:50px;" class="span10" accept="image/*" />
                    </div>					
            </div>
            <div class="actions">
                    <button id="insertImage_button" class="btn primary" onclick="insertImageToEditor();">Insert Image</button>
            </div>
	</form>
	<iframe id="hiddenframe" name="hiddenframe" ></iframe>
</article>

<footer>
	
</footer>