function findMode(){
    var node = "tool_button_current";
    for(var i=0;i<document.getElementsByTagName('div').length;i++){
        if(document.getElementsByTagName('div')[i].className == node){
            
            var id = document.getElementsByTagName('div')[i].id;
            svgCanvas.setMode('figure+'+id);
        }
    }
    
}
    function getButtons(){
        var buttons = new Array();
        
            $.ajax({
                url : "extensions/getAllFiles.php",
                async :false,                
		response:'json',
                success : function(msg){
                    var data = eval ("(" + msg + ")");
                    for (var i in data){
                        var nameArray = data[i].split('.');
                        if (nameArray[1]=='svg' ){                            
                            var button = nameArray[0];
                            buttons.push(button);
//                            if ($.inArray(nameArray[0]+'.png', data)<0){
//                                $.ajax({
//                                    url : "extensions/converter.php?svg="+data[i],
//                                    async: false
//                                })
//                            }
                        }
                            
                    }
                    svgEditor.addExtension("figures", function(S) {
                        
                        var re = /figure\+([a-zA-Z0-9_-]+)/;
                        var buttons_array = new Array();
                        var event = new Array();
                        var events = new Array();
                         
                        for (var j in buttons) {
                            var button = new Array();
                            var k = 0;
                            
                            button = {
                                id : buttons[j],
                                type : "mode",
                                icon : "extensions/figures/" + buttons[j] + ".svg",
                                title : 'Draw a ' + buttons[j],
                                events : {
                                    'click' : function() {
                                        findMode();                                   
                                    }
                                }
                            }
                            buttons_array.push(button);
                        }
                        
                        return {
                            name: "Other figures",                            
                            buttons: buttons_array,
                            
                            mouseDown: function() {
                                var mode = svgCanvas.getMode();
                                
                                if(re.test(mode)){
                                    
                                    var fileName = mode.split('+');
                                        $.ajax({
                                            'url': 'extensions/figures/'+ fileName[1] +'.svg',
                                            'dataType': 'text',
                                            success: svgCanvas.importSvgString,
                                            error: function(xhr, stat, err) {
                                                    if(xhr.responseText) {
                                                            svgCanvas.setSvgString(xhr.responseText);
                                                    } else {
                                                            $.alert("Unable to load from URL. Error: \n"+err+'');
                                                    }
                                            }
                                        });                         

                                }

                            }
                      }
                    })
                }
            })
            
               
    }       
    

			
		
