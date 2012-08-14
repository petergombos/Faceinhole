<?php
/**
 * PHP GD Water Marker
 * 
 * <code>
 * require_once 'phpgdwatermarker.php';
 * $watermarker = new PhpGdWatermarker('transwm.png');
 * 
 * if($watermarker->applyWaterMark('Jellyfish.jpg')){
 *     echo "APPLIED WATERMARK on TIMESTAMP-" . time();
 * } else {
 *     echo $watermarker->getLastErrorMessage();
 * }
 * 
 * @author Hossain Khan
 * @link http://www.hossainkhan.info/
 * @version v1.01
 * @license MIT License - see LICENSE.TXT for more details
 */
class PhpGdWatermarker {
    // Class variables
    protected $watermarkVerticalPosition;
    protected $watermarkHorizontalPosition;
    protected $watermarkFile;
    protected $watermarkEdgePadding;
    
    protected $errorMessage = ''; // saves last error message
    protected $newMarkedImagePostfix = '_watermarked'; // pre/post-fix for image file name 
    
    protected $isOverWrite = TRUE; // Default is overwrite image, else new watermarked image will be created
    
    // Constants for positioning 
    const VALIGN_TOP    = 'top';
    const VALIGN_CENTER = 'center';
    const VALIGN_BOTTOM = 'bottom';
    const HALIGN_LEFT   = 'left';
    const HALIGN_CENTER = 'center';
    const HALIGN_RIGHT  = 'right';
    
    /**
     * Constructs Watermarker object
     * 
     * @param object $watermarkImageFile
     * @param object $vPosition [optional] Vertical Position
     * @param object $hPosition [optional] Horizontal Position
     * @param object $watermarkPadding [optional]
     * @return 
     */
    function __construct($watermarkImageFile, $vPosition=self::VALIGN_CENTER , $hPosition=self::HALIGN_CENTER, $watermarkPadding=5){
        $this->watermarkFile = $watermarkImageFile;
        $this->watermarkVerticalPosition = $vPosition;
        $this->watermarkHorizontalPosition = $hPosition;
        $this->watermarkEdgePadding = $watermarkPadding;
    }
    
    /**
     * Updates watermark image file
     * 
     * @param string $imagePath Filename of watermark image
     * @return 
     */
    public function setWatermarkFile($imagePath){
        $this->watermarkFile = $imagePath;
    }
    
    
    /**
     * Get last error message
     * 
     * @return string Error message 
     */
    public function getLastErrorMessage(){
        return $this->errorMessage;
    }
    
    
    /**
     * Sets if image will be overwritten or not
     * 
     * @param object $boolean
     * @return void
     */
    public function setImageOverwrite($boolean){
        if(TRUE===$boolean){
            $this->isOverWrite = $boolean;
        } else {
            $this->isOverWrite = FALSE;
        }
    }
    
    /**
     * Add/Change the postfix text for newly watermarked image
     * 
     * @param string $postfixValue
     * @return void
     */
    public function setWatermarkedImageNamePostfix($postfixValue){
        $this->newMarkedImagePostfix = strval($postfixValue);
    }
    
    /**
     * Updates padding value for watermark 
     * <code>
     * $watermarker = new PhpGdWatermarker('transparent-watermark.png');
     * $watermarker->setEdgePadding(10);
     * // apply watermark
     * </code>
     * 
     * @param object $paddingVal
     * @return void 
     */
    public function setEdgePadding($paddingVal){
        if(!is_int($paddingVal)){
            $this->watermarkEdgePadding = intval($paddingVal);
        } else {
            $this->watermarkEdgePadding = $paddingVal;
        }
    }
    
    
    /**
     * Applies watermark to image provided.
     * 
     * @param string $unmarkedImagePath Path+Filename of image to apply watermark
     * @param integer $quality [optional] Quality - 0 to 100
     * @return boolean Success/Failure 
     */
    public function applyWaterMark($unmarkedImagePath, $quality=100){
        /*
         * Before getting in to operation, make sure we have everything:
         * >> Check if PHP Gd library loaded
         * >> Valid Watermark Image and thats PNG/JPG
         * >> Valid Target image is also PNG/JPG
         */
        if(!($this->_checkGdLibrary())){
            $this->errorMessage = "You php does not have GD image library enabled/loaded. Watermark can not be applied.";
            return FALSE;
        } // end of IF - GD lib check
        
        if(empty($this->watermarkFile) || !is_file($this->watermarkFile) || !is_readable($this->watermarkFile)){
            $this->errorMessage = "Invalid watermark file({$this->watermarkFile}) provided. Could not use this watermark image.";
            return FALSE;
        }
        
        // Do similar check for un-marked image
        if(empty($unmarkedImagePath) || !is_file($unmarkedImagePath) || !is_readable($unmarkedImagePath)){
            $this->errorMessage = "Invalid image file({$unmarkedImagePath}) provided. Could not add watermark to this image.";
            return FALSE;
        } // end of checking file existance */
        
        // Set final watermarked image path
        $finalWatermarkedImageFile = '';
        if($this->isOverWrite){
            $finalWatermarkedImageFile = $unmarkedImagePath;    
        } else {
            if(($finalWatermarkedImageFile = $this->_backupAndCreateNewImage($unmarkedImagePath))===FALSE){
               return FALSE; // error occured and err-message saved in that function 
            }    
        }
        
        $edgePadding = $this->watermarkEdgePadding;
        $targetImageSize=getimagesize($unmarkedImagePath);
        
        if($targetImageSize[2]==2 || $targetImageSize[2]==3){
            // Only JPEG or PNG image can be processed
            
            $originalImageName=$unmarkedImagePath; // backup image name
            
            $watermark=$this->watermarkFile;
            $wmTarget= $this->watermarkFile;

            $origInfo = getimagesize($originalImageName); 
            $origWidth = $origInfo[0]; 
            $origHeight = $origInfo[1]; 

            $waterMarkInfo = getimagesize($watermark);
            $waterMarkDestWidth = $waterMarkInfo[0];
            $waterMarkDestHeight = $waterMarkInfo[1];
           
            $differenceX = $origWidth - $waterMarkDestWidth;
            $differenceY = $origHeight - $waterMarkDestHeight;

            // where to place the watermark?
            switch($this->watermarkHorizontalPosition){
                // find the X coord for placement
                case self::HALIGN_LEFT :
                    $placementX = $edgePadding;
                    break;
                case self::HALIGN_CENTER :
                    $placementX =  round($differenceX / 2);
                    break;
                case self::HALIGN_RIGHT :
                    $placementX = $origWidth - $waterMarkDestWidth - $edgePadding;
                    break;
				default:
    				$placementX = $this->watermarkHorizontalPosition;
            } // end switch - horizontal

            switch($this->watermarkVerticalPosition){
                // find the Y coord for placement
                case self::VALIGN_TOP :
                    $placementY = $edgePadding;
                    break;
                case self::VALIGN_CENTER :
                    $placementY =  round($differenceY / 2);
                    break;
                case self::VALIGN_BOTTOM :
                    $placementY = $origHeight - $waterMarkDestHeight - $edgePadding;
                    break;
				default:
    				$placementY = $this->watermarkVerticalPosition;
            } // end switch - vertical
   
            if($targetImageSize[2]==3){
                // PNG Image
                $resultImage = imagecreatefrompng($originalImageName);
            }
            else {
                // JPEG Image
                $resultImage = imagecreatefromjpeg($originalImageName);
            }
            
            imagealphablending($resultImage, TRUE);
    
            $finalWaterMarkImage = imagecreatefrompng($wmTarget);
            $finalWaterMarkWidth = imagesx($finalWaterMarkImage);
            $finalWaterMarkHeight = imagesy($finalWaterMarkImage);
    
            imagecopy($resultImage, $finalWaterMarkImage, $placementX, $placementY, 0, 0, $finalWaterMarkWidth, $finalWaterMarkHeight);
            
            if($targetImageSize[2]==3){
                // PNG Image
                imagealphablending($resultImage,FALSE);
                imagesavealpha($resultImage,TRUE);
                imagepng($resultImage,$finalWatermarkedImageFile,$quality);
            } else {
                // JPEG Image
                imagejpeg($resultImage,$finalWatermarkedImageFile,$quality); 
            }
            
            imagedestroy($resultImage);
            imagedestroy($finalWaterMarkImage);
            
            // Finally return true if nothing went wrong
            return TRUE;
        } else {
            // The image was not a JPEG or PNG image.
            $this->errorMessage = 'The target image was not a JPEG or PNG image';
            return FALSE;
        }
    } // end of 'applyWaterMark' function
    
    
    /**
     * Checks if GD Library is loaded and enabled before execution task
     * 
     * @return boolean TRUE if GD available, FALSE otherwise 
     */
    private function _checkGdLibrary(){
        if (extension_loaded('gd') && function_exists('gd_info')) {
            return TRUE;
        } else {
            return FALSE;
        }
    }
    
    
    /**
     * Creates a new image file if overwrite was disabled
     * 
     * @param object $imageFile
     * @return boolean|string FALSE on failure, else the newly created file-name
     */
    private function _backupAndCreateNewImage($imageFile){
        $imageFileInfo = pathinfo($imageFile);
        if(is_array($imageFileInfo) && (count($imageFileInfo)>=3) ){
            // we've got - dirname, basename, extension, and filename(since PHP 5.2.0)
             if(!isset($imageFileInfo['filename'])) {
                 // set the filename if we dont have it - we'll need this :p
                 $imageFileInfo['filename'] = substr($imageFileInfo['basename'], 0, strrpos($imageFileInfo['basename'], '.'));
             }
             
             // dirname returns '.' if its current directory. So, it's safe to add '/' after dirname 
             $backUpImageFile = $imageFileInfo['dirname'] .'/'. $imageFileInfo['filename'] . $this->newMarkedImagePostfix .'.'. $imageFileInfo['extension'];
             
             // now we have the backupfile name - check if it exists
             if(file_exists($backUpImageFile)){
                 // IF file backup file already exists, then we dont have right to overwrite that
                 $this->errorMessage = "A file named ({$backUpImageFile}) already exists. Could not backup file. Process aborted.";
                 return FALSE;
             } else {
                 if(copy($imageFile, $backUpImageFile)){
                     return $backUpImageFile;
                 } else {
                     // could not copy file as backup - abort
                     $this->errorMessage = "Could not create a new image copy before applying watermark. Process aborted.";
                     return FALSE;
                 }
             }
        } else {
            $this->errorMessage = 'Could not create backup image. Process aborted.';
            return FALSE;
        }
    }
} // end of class