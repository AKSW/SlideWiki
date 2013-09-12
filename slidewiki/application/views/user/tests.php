<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<script src="static/js/questions.js"></script>
<?php if (count($lists_array)) : ?>
<h3>Manage your tests</h3>


<script id="listsShow" type="text/x-jquery-tmpl">
    
   <div class="diff_star_small" id="listString-${id}">
            
        <div class="diff_small">
            <div class = "diff_bar_small" style="width: ${avg_diff*9}px;" id = "diff_bar_${id}"></div> 
        </div>
        <div class="diff_info_small">
             <a id="list-${id}" href="./?url=main/tests&id=${id}&type=user">${title}</a>
            (${quest_count})
            <a title="Enter a new name" id ="renameList_${id}" style="cursor:pointer" onclick="listRename('${id}')"><img width="18px" src="/static/img/edit-icon.png"></a>
            <a title="List '${title}' content:" id ="viewList_${id}" style="cursor:pointer" onclick="listView('${id}')"><img width="18px" src="/static/img/search-icon.png"></a>
            <a style="cursor:pointer" onclick="listDelete(${id})"><img width="18px" src="/static/img/remove.png"></a>
        </div>
    </div>
    
</script>

<div id="listsTable">
<?php 

foreach ($lists_array as $list) { ?>
    <div class="diff_star_small" id="listString-<?php echo $list->id?>">
            
        <div class="diff_small">
                <div class = "diff_blank_small" id = "diff_blank_<?php echo $list->id?>"></div> 
                <div class = "diff_hover_small" id = "diff_hover_<?php echo $list->id?>"></div> 
                <div class = "diff_bar_small" style="width: <?php echo $list->avg_diff*9; ?>px;" id = "diff_bar_<?php echo $list->id?>"></div> 
        </div>
        <div class="diff_info_small">
             <a id="list-<?php echo $list->id?>" href="./?url=main/tests&id=<?php echo $list->id?>&type=user"><?php echo $list->title; ?></a>
            (<?php echo $list->quest_count; ?>)
            <a title="Enter a new name" id ="renameList_<?php echo $list->id;?>" style="cursor:pointer" onclick="listRename('<?php echo $list->id;?>')"><img width="18px" src="/static/img/edit-icon.png"></a>
            <a title="List '<?php echo $list->title; ?>' content:" id ="viewList_<?php echo $list->id;?>" style="cursor:pointer" onclick="listView('<?php echo $list->id;?>')"><img width="18px" src="/static/img/search-icon.png"></a>
            <a style="cursor:pointer" onclick="listDelete(<?php echo $list->id;?>)"><img width="18px" src="/static/img/remove.png"></a>
        </div>
    </div>
</div>      
    
    <script id="list-content" type="text/x-jquery-tmpl">
    {{each questions}}
            
        <div class="diff_star_small" id="questionString_${$value.id}">
            
            <div class="diff_small">
                    <div class = "diff_blank_small" id = "diff_blank_${$value.id}"></div> 
                    <div class = "diff_hover_small" id = "diff_hover_${$value.id}"></div> 
                    <div class = "diff_bar_small" id = "diff_bar_${$value.id}"></div> 
            </div>
            <div class="diff_info_small">

                    <b>${$value.question}</b>

                    by <a href="./?url=user/profile&id=${$value.user.id}">
                            ${$value.user.username}
                    </a>
<a style="cursor:pointer" onclick="deleteFromList(<?php echo $list->id;?>,${$value.id})"><img width="18px" src="/static/img/remove.png"></a>

            </div>
    </div>		
           
        {{/each}} 
       
    </script> 
    
    
<?php 

}
?>
<?php else : ?>
    <center><div data-alert="alert" class="alert-message error">You don't have any manually created tests</div></center>
<?php endif; ?>
