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
  #setTitles {
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
		<?php echo t("It may take a while to process images.") ?>
	</div>
  
  <div id="userID_wrap">
  	<?php echo $form->label('userID', t('Flickr User ID')) ?>
  	<?php echo $form->text('userID') ?>
  	<div class="ccm-note"><?php echo t('Example User ID: 98026096@N06')?> </div>
  </div>
  
  <div id="flickrSets"></div>
  
  <input id="setTitles" type="text" name="setTitles" value="" class="ccm-input-text" />
  
  <a id="getFlickrSets" href="javascript:void(0)" class="ccm-button-right btn primary">Get Sets</a>
  
</div>


<script type="text/javascript">
$(document).ready(function() {
  
  var gettingSets = false;
  
  var addBtn = $('.ccm-button-right.accept');
  var onclick = addBtn.attr("onclick");

  addBtn.removeAttr("onclick");
  addBtn.hide();
  
  var getSetsBtn = $('#getFlickrSets');
  
  getSetsBtn.appendTo('.ccm-buttons.dialog-buttons');
  
  getSetsBtn.click(function() {
    var userID = $('#userID').val();
    if (userID.length > 0 && !gettingSets) {
      
      gettingSets = true;
      
      var api_key = "<?php echo $controller->api_key ?>";
      var f = new Flickr("604fbee2e687131cfa3cb67aae0d6228");
      f.Api(
        "flickr.photosets.getList",
        function(resp){
          if(resp.stat != "fail") {
            var sets = resp.photosets.photoset;
            $.each(sets, function(index, value) {
              var setTitle = value.title._content;
              var setID = value.id.toString();
              var input = "<input type='checkbox' class='ccm-input-checkbox' data-title='" + setTitle + "' name='setIDs[" + setTitle + "]' id='setIDs[" + setTitle + "]' value='" + setID + "' />";
              var label = "<label for='setIDs[" + setTitle + "]'>" + setTitle + "</label>";
              $('#flickrSets').append("<p class='checkbox_container'>" + input + label + "<div class='clear'></div></p>");
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
                
                addBtn.attr("onclick", onclick);
              } else {
                addBtn.removeAttr("onclick");
              }
            });
            
            $('#userID_wrap').hide();
            getSetsBtn.remove();
            addBtn.show();
            
          } else {
            console.log("API-error: " + resp.message);
            gettingSets = false;
          }
        },
        {user_id: userID}
      );    
    }
  });
  
  
  
});


function Flickr(apiKey){
	if(!apiKey){throw "InvalidApiKeyProvided";}
	this.key = apiKey;
	this.Api = function(method, callback, params, cparams){
 
		var xhttp = new XMLHttpRequest();
		xhttp.open('GET', this.createUrl(method, params), true);
		xhttp.onreadystatechange = function(){
			if(xhttp.readyState == 4){
				callback(JSON.parse(xhttp.responseText), cparams);
			}
		}
		xhttp.send(null);
	}
	this.Api_S = function(method, params){
		var xhttp = new XMLHttpRequest();
		xhttp.open('GET', this.createUrl(method, params), false);
		xhttp.send(null);
		return JSON.parse(xhttp.responseText);
	}
	this.createUrl = function(method, params){
		var url = 
			"http://api.flickr.com/services/rest/?method=" + method + 
			"&api_key=" + this.key + 
			"&format=json" +
			"&nojsoncallback=1";
		if(params){
			for(var p in params){
				url += "&" + p + "=" + params[p];
			}
		}
		return url;
	}
}
</script>