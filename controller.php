<?php defined('C5_EXECUTE') or die(_("Access Denied."));

include('flickr.php');

class FlickrSetsBlockController extends BlockController {
	
	protected $btTable = "btFlickrBlock";
	protected $btInterfaceWidth = "350";
	protected $btInterfaceHeight = "300";
  
  protected $btExportTables = array('btFlickrBlock', 'btFlickrSetInfo', 'btFlickrPhotos');
  
  //Need to change API key. Currently key is for an uncommercial app.
  //Create an app here: http://www.flickr.com/services/apps/create/apply
  public $api_key = "604fbee2e687131cfa3cb67aae0d6228";
  
	public function getBlockTypeName() {
		return t('Flickr Sets');
	}

	public function getBlockTypeDescription() {
		return t('Pulls in Flickr sets');
	}
  
	public function on_page_view() {
		$html = Loader::helper('html');
		$this->addHeaderItem('<script type="text/javascript" src="/blocks/flickr_sets/slimbox2.js"></script>');
	  $this->addHeaderItem('<link rel="stylesheet" type="text/css" href="/blocks/flickr_sets/css/slimbox2.css" />');
	  $this->addHeaderItem('<link rel="stylesheet" type="text/css" href="/blocks/flickr_sets/css/flickrsets.css" />');
	}
  
	public function view(){
    $db = Loader::db();
    
    $bid = $this->bID;
    
    $photoSetsInfo = $db->GetArray('SELECT * FROM btFlickrSetInfo WHERE bID ='.$bid);
    $photos = $db->GetArray('SELECT * FROM btFlickrPhotos WHERE bID ='.$bid);
    
    $this->set('photoSetsInfo', $photoSetsInfo);
    $this->set('photos', $photos);
    $this->set('copytext', $this->copytext);
	}
  
	public function edit(){
    $db = Loader::db();
    $bid = $this->bID;
    
    $setsInfo = $this->getSetInfo($this->userID);
    $this->setsInfo = $setsInfo;
    
    $currentSetsInfo = $db->GetArray('SELECT * FROM btFlickrSetInfo WHERE bID ='.$bid);
    
    $currentSetIDs = array();
    foreach ($currentSetsInfo as $setInfo) {
      $currentSetIDs[] = $setInfo[setID];
    }
    
    $this->currentSetIDs = $currentSetIDs;
	}
  
	public function getSetInfo($flickUserID) {
    
    $setlist_params = array(
       'api_key' => $this->api_key,
       'format' => Flickr::JSON,
       'method' => 'flickr.photosets.getList',
       'user_id' => $flickUserID,
       'per_page' => '100'
    );
    
    $setlist_results = json_decode(Flickr::makeCall($setlist_params))->photosets->photoset;
    
    return $setlist_results;
	}
  
	public function getSetPhotos($set_id) {

    $setphotos_params = array(
       'api_key' => $this->api_key,
       'format' => Flickr::JSON,
       'method' => 'flickr.photosets.getPhotos',
       'photoset_id' => $set_id,
       'extras' => 'url_m, url_c, url_o, description'
    );
    
    $setphoto_results = json_decode(Flickr::makeCall($setphotos_params))->photoset->photo;
    
    return $setphoto_results;
	}
  
  function filter_by_key($array, $member, $value) {
    $filtered = array();
    foreach ($array as $k => $v) {
      if($v[$member] == $value) $filtered[$k] = $v;
    }
    return $filtered;
  }
  
  function getImagesData($arr) {
    $src = function($a) {
       return array($a[photoSRC], htmlentities($a[photoDesc], ENT_QUOTES));
    };
    return array_map($src, $arr);
  }
  
  public function delete() {
    $db = Loader::db();
    
    $bid = $this->bID;
    
    //Delete set info
    $db->Execute('DELETE FROM btFlickrSetInfo WHERE bID='.$bid);
  
    //Delete photos
    $db->Execute('DELETE FROM btFlickrPhotos WHERE bID='.$bid);
    
    parent::delete();
  }
  
	public function save($data) {
    
    $db = Loader::db();
    
    $bid = $this->bID;
    $thumbSRC = "";
    
    //Delete previous set info
    $db->Execute('DELETE FROM btFlickrSetInfo WHERE bID='.$bid);
  
    //Delete previous photos
    $db->Execute('DELETE FROM btFlickrPhotos WHERE bID='.$bid);
    
		//Save user ID
    $args['userID'] = !empty($data['userID']) ? trim($data['userID']) : '98026096@N06';
    
    //Get set IDs
    $setIDs = array_values($data['setIDs']);
    
    //Get set titles
    $setTitlesString = trim($data['setTitles']);
    $setTitles = explode(',', $setTitlesString);
    
    for ($i = 0; $i < count($setIDs); $i++) {
      
      $setID = $setIDs[$i];
      $setTitle = $setTitles[$i];
      
      //Get new set photos
      $setPhotos = $this->getSetPhotos($setID);
    
      foreach ($setPhotos as $j=>$photo_info) {
        
        $photoID = $photo_info->id;
        $photoURLc = $photo_info->url_c;
        $photoURLo = $photo_info->url_o;
        $photoSRC = !empty($photoURLc) ? $photoURLc : $photoURLo;
        $photoDescription = $photo_info->description->_content;
        
        //Add new photo row to database
        $vb = array($bid, $photoID, $photoSRC, $photoDescription, $setID);
        $qb = "INSERT INTO btFlickrPhotos (bID, photoID, photoSRC, photoDesc, setID) values (?, ?, ?, ?, ?)";
        $rb = $db->Execute($qb,$vb);
        
        if ($j == 0) $thumbSRC = $photo_info->url_m;
      }
      
      //Add new set row to database
      $v = array($bid, $setID, $setTitle, $thumbSRC);
      $q = "INSERT INTO btFlickrSetInfo (bID, setID, setTitle, setThumbSRC) values (?, ?, ?, ?)";
      $r = $db->Execute($q,$v);
    }
    
    parent::save($args);
	}
}
