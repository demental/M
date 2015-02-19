<?php
/**
* M PHP Framework
*
* Wow this is my first PHP class (lol)
* Is used in M/DB/DataObject/Plugins/Image.php
* We should think about refactoring this file as it's only a useless proxy for PEAR_Image_Transform
* I left the comments below as an historical purpose (not accurate anymore...)
*
* @package      M
* @subpackage   traitephoto
* @author       Arnaud Sellenet <demental@sat2way.com>
* @license      http://opensource.org/licenses/lgpl-license.php GNU Lesser General Public License
* @version      0.1
*/

//////////////////////////////


class traitephoto
{
	public $photo;//// Chemin du fichier photo a traiter
	public $nomimg;//// debut du nom du fichier, si $nomsouhaite n'est pas renseigne
	public $path;//// Chemin ou enregistrer le fichier traite
	public $qualite; //// qualite JPEG
	public $nomsouhaite; //// si ce champ est renseigne le nom de l'export sera exactement celui-ci

  /// ---- Redimensionnement ---- //
  //// si tous les champs sont renseign�s, seuls les plus "prioritaires" sont pris en compte
  //// $pourcent prend le pas sur $width et $height
  //// mais peut ensuite �tre trait� par les param�tres suivants
  //// $maxx et $maxy prennent le pas sur $perimetre qui prend le pas sur $surface

	public $width;//// Largeur souhait�e. Si la hauteur n'est pas sp�cifi�e, le redimensionnement sera proportionnel
	public $height;//// Hauteur souhait�e. Si la largeur n'est pas sp�cifi�e, le redimensionnement sera proportionnel
	public $pourcent;//// redimensionnement en pourcentage
	public $maxx;//// redimensionnement seuil en largeur - toujours proportionnel
	public $maxy;//// redimensionnement seuil en hauteur - toujours proportionnel
	public $perimetre;//// redimensionnement en p�rim�tre - toujours proportionnel
	public $surface;//// redimensionnement en surface - toujours proportionnel
	public $imgsize = Array();//// variable 'priv�e', o� sont stock�es les informations de la photo originale (0=>x,1=>y,2=>format) - avec GD
						//// on peut �ventuellement la r�cup�rer en dehors de l'objet
	public $newimgsize = Array();///// tableau ou sont stock�s les informations de la nouvelle photo
/// ----- Recadrage ----- ///
	public $gauche;
	public $droit;
	public $haut;
	public $bas;
	public $docsize = Array();/// Var priv�e, utilis�e pour le recadrage
/// ----- Rotation ----- ///
/// Uniquement par pas de 90� //
	public $angle;///
	public $imgT;// objet PEAR_Image_Transform
	public $server; /////// chemin pour arriver au dossier depuis la racine serveur (si on utilise imagemagick)
	public $gd; ////// 1 pour redim gd, 0 pour redim imagemagick
	public function __construct() {
		$this -> photo="";
		$this -> nomimg="";
		$this -> width=0;
		$this -> height=0;
		$this -> path="";
		$this -> pourcent=0;
		$this -> maxx=0;
		$this -> maxy=0;
		$this -> perimetre=0;
		$this -> gauche=0;
		$this -> haut=0;
		$this -> droit=0;
		$this -> bas=0;
		$this -> angle=0;
		$this -> surface=0;
		$this -> qualite=60;
		$this -> nomsouhaite="";
		$this -> gd=1;
		$this->server="";

    if(extension_loaded('imagick') || (function_exists('dl') && @dl('imagick'))) {
		  $this->imgT= Image_Transform::factory("Imagick3");
      if(PEAR::isError($this->imgT)) {
        Log::error($this->imgT->getMessage());
      }
      Log::info('Using Imagick3 as image driver');
    } else {
		  $this->imgT= Image_Transform::factory("GD");
      Log::info('Using GD as image driver');
    }
	}
	public function crop(){
		if($this->droit-$this->gauche!=0){
			$this->docsize[0]=$this->droit-$this->gauche;
			$this->docsize[1]=$this->bas-$this->haut;
			return true;
		} else {
			return false;
		}
	}
	function getSize(){
		$this->imgsize=getimagesize($this->photo);
		return $this->imgsize;
	}

	function resize() {
		$this->imgT->load($this->photo);
		if(PEAR::isError($this->imgT)) {
      throw new Exception($this->imgT->getMessage());
		}
		$this->imgT->setOptions(array("quality"=>$this->qualite));
		if($this->width){
			if($this->height){
				$this->imgT->resize($this->width,$this->height);
			} else {
				$this->imgT->scaleByX($this->width);
			}
		}elseif($this->height){
				$this->imgT->scaleByY($this->height);
		}elseif($this->maxx){
			if($this->maxy){
				$this->imgT->fit($this->maxx,$this->maxy);
			} else {
				$this->imgT->scaleMaxLength($this->maxx);
			}
		}elseif($this->maxy){
				$this->imgT->scaleMaxLength($this->maxy);
		}
	}

	public function processGD(){
		if($this -> imgsize[2]=="2") {
      $im_in = ImageCreateFromJpeg($this->photo);
    }
		if($this -> imgsize[2]=="1") {
			$im_in = ImageCreateFromGif($this->photo);
			$white = imagecolortransparent($im_in);
		}
		if(($this->angle % 360)!=0){
			$im_in = self::rotation($im_in,$this->angle);
		}
    if($this->crop()){
			$im_out = ImageCreate($this -> docsize[0],$this -> docsize[1]);
		} else {
			$im_out = ImageCreate($this -> newimgsize[0],$this -> newimgsize[1]);
		}
		if($this->newimgsize[0]==$this->imgsize[0] && $this->newimgsize[0]==$this->imgsize[0]){
			ImageCopy($im_out, $im_in, 0, 0, $this->gauche, $this->haut, $this -> newimgsize[0],$this -> newimgsize[1]);
		} else {
			self::ImageCopyResampleBicubic($im_out, $im_in, 0, 0, $this->gauche, $this->haut, $this -> newimgsize[0],$this -> newimgsize[1],$this -> imgsize[0], $this -> imgsize[1]);
		}
		if($im_in) {ImageDestroy($im_in);}
		return $im_out;
	}

	public function output(){
		header("Content-Type:image/gif");
		$im_out=$this->processGD();
		if($this -> imgsize[2]=="2") { imagejpeg($im_out,'',$this->qualite); }
		if($this -> imgsize[2]=="1") { imagegif($im_out); }
  	imagedestroy($im_out);
	}

  /**
   * Saves processed image and returns file name
   * @return string
   */
	public function save($type=null){
    $this->path = FileUtils::ensure_trailing_slash($this->path);
		if($type) {
			$this->nomsouhaite = eregi_replace('[[:alnum:]]+$',$type,$this->nomsouhaite);
		}
		$this->imgT->save($this->path.$this->nomsouhaite,$type?$type:$this->imgT->getImageType());
		return $this->nomsouhaite;
	}

  /*
  port to PHP by John Jensen July 10 2001 (updated 4/21/02) -- original code
  (in C, for the PHP GD Module) by jernberg@fairytale.se
  I have added various optimization updates to my PHP bicubic function, along
  with a few fixes. Thanks to scott@pawprint.net for pointing out that
  src_x/y were being factored into the placement on the dst_img.
  */
  public static function ImageCopyResampleBicubic(&$dst_img, &$src_img, $dst_x,$dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h) {
    $palsize = ImageColorsTotal ($src_img); // retourne le nombre de couleur de la palette
    for ($i = 0; $i < $palsize; $i++) {  // get palette.
      $colors = ImageColorsForIndex ($src_img, $i);
      ImageColorAllocate ($dst_img, $colors['red'], $colors['green'], $colors['blue']);
    }

    $scaleX = ($src_w - 1) / $dst_w;
    $scaleY = ($src_h - 1) / $dst_h;

    $scaleX2 = (int) ($scaleX / 2);
    $scaleY2 = (int) ($scaleY / 2);

    for ($j = $src_y; $j < $dst_h; $j++) {
      $sY = (int) ($j * $scaleY);
      $y13 = $sY + $scaleY2;

      for ($i = $src_x; $i < $dst_w; $i++) {
        $sX = (int) ($i * $scaleX);
        $x34 = $sX + $scaleX2;

        $color1 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX,$y13));

        $color2 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX,$sY));

        $color3 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34,$y13));

        $color4 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34,$sY));


        $red = ($color1['red'] + $color2['red'] + $color3['red'] + $color4['red']) / 4;
        $green = ($color1['green'] + $color2['green'] + $color3['green'] + $color4['green']) / 4;
        $blue = ($color1['blue'] + $color2['blue'] + $color3['blue'] + $color4['blue']) / 4;
        ImageSetPixel($dst_img, $i + $dst_x - $src_x, $j + $dst_y - $src_y,
        ImageColorClosest($dst_img, $red, $green, $blue));
      }
    }
  }

  /* @params
  * $pic : resource identifier returned by imagecreate()
  * $angle : rotation angle, in degrees (90, 180, 270)
  */
  public static function rotation($pic, $angle) {
    $width = imagesx($pic);
    $height = imagesy($pic);
    if ($angle==180)
    $p = imagecreate($width, $height);
    else
    $p = imagecreate($height, $width);
    if ($angle<0)
    $angle = 360 + $angle % 360;
    if ($angle>360)
    $angle = $angle % 360;

    if ($angle==180)  {
      for($i=0;$i<$width;$i++)  {
        for($j=0;$j<$height;$j++) {
           imagecopy($p, $pic, $i, $j, $width-$i, $height-$j, 1, 1);
        }
      }
    }
    if ($angle==90)  {
      for($i=0;$i<$width;$i++) {
        for($j=0;$j<$height;$j++){
           imagecopy($p, $pic, $j, $i, $width-$i, $j, 1, 1);
        }
      }
    }
    if($angle==270) {
      for($i=0;$i<$width;$i++) {
        for($j=0;$j<$height;$j++) {
          imagecopy($p, $pic, $j, $i, $i, $height-$j, 1, 1);
        }
      }
    }
    return $p;
  }
}
