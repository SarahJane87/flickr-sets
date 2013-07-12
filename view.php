<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>

<?php
  foreach ($photoSetsInfo as $set) { ?>
    <div class="flickrSet">
      <img src="<?php echo $set[setThumbSRC] ?>"/>
      <p class="button-link"><a class="openFlickrSet" href="javascript:void(0)" data-set_id="set_<?php echo $set[setID] ?>">View photos from <?php echo str_replace("Genius Perfect Toast Tour - ","",$set[setTitle]) ?></a></p>
    </div>
<?php } ?>

<script type="text/javascript">
  
  var photoSRCBySet = new Object();
  <?php
    foreach ($photoSetsInfo as $set) {
      $setID = $set[setID];
      $setPhotos = $controller->filter_by_key($photos, setID, $setID);
      $photoSRCs = $controller->getImagesData($setPhotos);
      $photoSRCs = array_values($photoSRCs); ?>
      var setID = 'set_<?php echo $setID ?>';
      photoSRCBySet[setID] = <?php echo json_encode($photoSRCs) ?>;
      var arr = <?php echo json_encode($photoSRCs) ?>;
      photoSRCBySet[setID] = $.map( arr, function(val, i) {
        return [[val[0],htmlDecode(val[1])]];
      });
  <?php } ?>
  
  $('.openFlickrSet').click(function() {
    
    var setID = $(this).data("set_id");
    var photoSRCs = photoSRCBySet[setID];
    jQuery.slimbox(photoSRCs, 0, {
      imageFadeDuration: 600,
      resizeDuration: 200,
      counterText: false,
      captionAnimationDuration: 100
    });
    
  });
  
  function htmlDecode(input){
    var e = document.createElement('div');
    e.innerHTML = input;
    return e.childNodes.length === 0 ? "" : e.childNodes[0].nodeValue;
  }
  
</script>