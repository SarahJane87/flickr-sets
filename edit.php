<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>

<style type="text/css">

  #ccm-block-form label {
    width: 100%;
    text-align: left;
  }
  #ccm-block-form textarea, #ccm-block-form input[type="text"] {
    width: 368px;
    border: 1px solid #000;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    -ms-border-radius: 5px;
    -o-border-radius: 5px;
    border-radius: 5px;
    margin: 5px 0 7px 0;
    padding: 5px;
  }
  #ccm-block-form textarea {
    height: 95px;
  }
  #ccm-block-form .ui-dialog-buttonset {
    float: none;
  }
  #userID, label[for="userID"], #setTitles {
    display: none;
  }
  .ccm-ui input[type=checkbox] {
    float: left;
    cursor: pointer;
  }
  #ccm-block-form label {
    width: 95%;
    text-align: left;
    padding-left: 4px;
    padding-top: 0px;
  }
  .checkbox_container {
    margin-bottom: 10px;
  }
</style>

<div class="ccm-ui">
  
	<div class="alert-message block-message info">
		<?php echo t("Click save to refresh block with new images.<br/>It may take a while to process images.") ?>
	</div>
  
	<?php echo $form->label('userID', t('Flickr User ID')) ?>
	<?php echo $form->text('userID', $userID) ?>
  
  <?php 
  $setTitles = "";
  
  foreach ($controller->setsInfo as $setInfo) { 
    $setID = $setInfo->id;
    $setTitle = $setInfo->title->_content;
    
    $isSelected = in_array($setID, $controller->currentSetIDs);
    
    if ($isSelected) $setTitles = $setTitles.$setTitle.",";
  ?>
    <p class='checkbox_container'>
      <?php echo $form->checkbox('setIDs['.$setTitle.']', $setID, $isSelected, array('data-title'=>$setTitle)) ?>
      <?php echo $form->label('setIDs['.$setTitle.']', $setTitle) ?>
      <div class='clear'></div>
    </p>
  <?php } 
  $setTitles = substr_replace($setTitles, "", -1);
  ?>
  
  <input id="setTitles" type="text" name="setTitles" value="<?php echo $setTitles ?>" class="ccm-input-text">
  
  <div id="flickrSets"></div>
  
</div>

<script type="text/javascript">
$(document).ready(function() {
  
    var setTitles = "";
    
    var addBtn = $('.ccm-buttons .btn.primary');
    var onclick = addBtn.attr("href");
    
    
    $(".ccm-input-checkbox").each(function(index, el) {
      $(el).data('title', $(el).val());
    });
    
    $('.ccm-input-checkbox').change(function() {
      var checkedBoxes = $(".ccm-input-checkbox").filter(':checked');
      if (checkedBoxes.length > 0){
        
        var setTitles = "";
        
        checkedBoxes.each(function(index, el) {
          setTitles = setTitles + $(el).attr("data-title") + ",";
        });
        setTitles = setTitles.slice(0, -1);
        
        $('#setTitles').val(setTitles);
        
        addBtn.attr("href", onclick);
      } else {
        addBtn.attr("href", "javascript:void(0)");
      }
    });
     
});
</script>