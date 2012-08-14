<?

// Report all PHP errors
error_reporting(-1);

// Same as error_reporting(E_ALL);
ini_set('error_reporting', E_ALL);

require ('src/resize-class.php');
require ('src/phpgdwatermarker.php');

$tempname = md5(time());

$settings = json_decode(stripcslashes($_POST['settings']),true);

$images = json_decode(stripcslashes($_POST['images']),true);

function aasort (&$array, $key) {
    $sorter=array();
    $ret=array();
    reset($array);
    foreach ($array as $ii => $va) {
        $sorter[$ii]=$va[$key];
    }
    asort($sorter);
    foreach ($sorter as $ii => $va) {
        $ret[$ii]=$array[$ii];
    }
    $array=$ret;
}

aasort($images,"zIndex");

//Creating canvas
$img_background = imagecreatetruecolor($settings['canvas_width'], $settings['canvas_height']);

//Copy background image if specified
if($settings['background_pic'] != ''){
	$img_background_pic = imagecreatefromjpeg($settings['background_pic']);
	imagecopy($img_background, $img_background_pic, 0, 0, 0, 0, $settings['canvas_width'], $settings['canvas_height']);
	imagedestroy($img_background_pic);
}

//Saving background
imagejpeg($img_background,'pictures/'.$tempname.'_background.jpg', 90);
imagedestroy($img_background);


//Transforming Images
$i = 0;
foreach($images as $image){
	
	//Get and download image
	$ext = pathinfo($image['src'], PATHINFO_EXTENSION);
	$ext = explode('?',$ext);
	$ext = $ext[0];
	
	$temp_img = file_get_contents($image['src']);
	$image_name = $tempname.'_'.$i.'.'.$ext;
	file_put_contents('pictures/'.$image_name,$temp_img);


	//Resize image if needed
	if($image['size'] != 100){
		// *** 1) Initialize / load image
		$resizeObj = new resize('pictures/'.$image_name);
		
		// *** 2) Resize image (options: exact, portrait, landscape, auto, crop)
		$resizeObj->resizeImage(($image['width'] * ($image['size']/100)), ($image['height'] * ($image['size']/100)), 'auto');
	
		// *** 3) Save image
		$resizeObj->saveImage('pictures/'.$image_name);
	}

	
	//Rotate image if needed
	if($image['angle'] != -1){
		$filename = $tempname.'_'.$i.'.png';
		$rotang = 360-$image['angle'];
		
		switch($ext)
		{
			  case 'jpg':
			  case 'jpeg':
			  case 'JPG':
			  case 'JPEG':
				  $source = imagecreatefromjpeg('pictures/'.$image_name) or die('Error rotate file');
				  break;
			  case 'gif':
				  $source = imagecreatefromgif('pictures/'.$image_name) or die('Error rotate file');
				  break;
			  case 'png':
				  $source = imagecreatefrompng('pictures/'.$image_name) or die('Error rotate file');
				  break;
			  default:
				  $img = false;
				  break;
		}
		
		imagealphablending($source, false);
		imagesavealpha($source, true);
	
		$rotation = imagerotate($source, $rotang, imageColorAllocateAlpha($source, 0, 0, 0, 127));
		imagealphablending($rotation, false);
		imagesavealpha($rotation, true);
	
		imagepng($rotation,'pictures/'.$filename);
		imagedestroy($source);
		imagedestroy($rotation);
		
		unlink('pictures/'.$image_name);
		$image_name = $filename;
	}
	
	
	//Create image
	$watermarker = new PhpGdWatermarker('pictures/'.$image_name,$image['top'],$image['left'],0);
	$watermarker->applyWaterMark('pictures/'.$tempname.'_background.jpg',100);
	
	$i++;
}


//Add frame if specified
if($settings['frame_pic'] != ''){
	$watermarker = new PhpGdWatermarker($settings['frame_pic'],0,0,0);
	$watermarker->applyWaterMark('pictures/'.$tempname.'_background.jpg',100);
}

//Putting the repsonse together
$response['filename'] = 'pictures/'.$tempname.'_background.jpg';

echo $response['filename'];