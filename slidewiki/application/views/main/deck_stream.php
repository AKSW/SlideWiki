<?php require_once (ROOT . DS . 'application' . DS . 'views' . DS . 'activity_templates.php'); ?>
<script type="text/javascript" src="libraries/frontend/jquery-tmpl/jquery.tmpl.min.js"></script>
<script type="text/javascript">
showDeckStream(<?php echo $deck->id ?>,'');
</script>

<article class="page-header">
    <section>
        <div class="form-stacked">
            <fieldset>
                <div id="deck_profile" class="row header">
                    <div>
                        <div id="main_deck_id" deck_id = "<?php echo $deck->deck_id ?>">               
                            <a href="./deck/<?php echo $deck->id . '_' . $deck->slug_title; ?>"><h2><?php echo $deck->title ?> : Latest activities</h2></a>
                        </div>      
                    </div>                 
                </div>                    
            </fieldset>
        </div>
    </section>
</article>


<div id="deck_activities">
        <nav>
            <div class="btn-toolbar primary clearfix">
                <div id="filter-array" style="float:right;vertical-align:bottom;clear:both;display:inline;" class="btn-group">
                    <a onclick="applyFilterDeckStream($(this), <?php echo $deck->id; ?>)" filter="0" class="btn small success filter">Slide activities</a>
                    <a onclick="applyFilterDeckStream($(this), <?php echo $deck->id; ?>)" filter="1" class="btn small success filter">Follow activities</a>
                    <a onclick="applyFilterDeckStream($(this), <?php echo $deck->id; ?>)" filter="1" class="btn small success filter">Comments</a>
                    <a onclick="applyFilterDeckStream($(this), <?php echo $deck->id; ?>)" filter="1" class="btn small success filter">Translations</a>
                </div>
            </div>
        </nav>
    <article id="activity_stream"></article>
    <footer></footer>
</div>
