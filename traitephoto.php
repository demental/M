<?
// ========================================
// = Wow this is my first PHP class (lol)
// = Is used in M/DB/DataObject/Plugins/Image.php =
// = We should think about refactoring this file as it's only a useless proxy for PEAR_Image_Transform 
// = I left the comments below as an historical purpose (not accurate anymore...)
// ========================================
require_once("Image/Transform.php");
//// Classe de traitement photo.
//// Historique :
//// 10/2002 début du developpement
//// 11/2002 Ajout des fonctions maxx et maxy
//// 16/01/2003 Ajout de redimensionnement par surface ou perimetre seuil
//// 23/01/2003 Ajout de nomsouhaite pour avoir le nom exact
//// 29/01/2003 Ajout du support d'imagemagick pour le redim
//// 05/04/2004 Commentaire + ajout de la vérification du "/" à la fin de $path
////////////////////////////
////
//// Exemple d'utilisation :
//// $ph=new traitephoto;
//// $ph->path="photos/";
//// $ph->photo=$chemin_fichier_a_traiter;
//// $ph->nomsouhaite="bla.jpg";
//// $ph->width=100;
//// $ph->height=100;
//// $ph->redim();
//// $ph->sauvegarde();
////
//////////////////////////////
//// Limitations avec imagemagick :
//// l'exécutable "convert" doit être placé dans le dossier /bin et pas /sbin pour pouvoir être exécuté par php
//// Il doit certainement être en 755 (à vérifier)
//// Pas de rotation
//// Pas de redimensionnement en même temps que recadrage
//// le support d'imagemagick n'est pas implémenté dans la méthode "sortie()"
//////////////////////////////



class traitephoto
{
	var $photo;//// Chemin du fichier photo à traiter
	var $nomimg;//// début du nom du fichier, si $nomsouhaite n'est pas renseigné
	var $path;//// Chemin où enregistrer le fichier traité
	var $qualite; //// qualite JPEG
	var $nomsouhaite; //// si ce champ est renseigné le nom de l'export sera exactement celui-ci 

/// ---- Redimensionnement ---- //
//// si tous les champs sont renseignés, seuls les plus "prioritaires" sont pris en compte
//// $pourcent prend le pas sur $width et $height
//// mais peut ensuite être traité par les paramètres suivants
//// $maxx et $maxy prennent le pas sur $perimetre qui prend le pas sur $surface

	var $width;//// Largeur souhaitée. Si la hauteur n'est pas spécifiée, le redimensionnement sera proportionnel
	var $height;//// Hauteur souhaitée. Si la largeur n'est pas spécifiée, le redimensionnement sera proportionnel
	var $pourcent;//// redimensionnement en pourcentage
	var $maxx;//// redimensionnement seuil en largeur - toujours proportionnel
	var $maxy;//// redimensionnement seuil en hauteur - toujours proportionnel
	var $perimetre;//// redimensionnement en périmètre - toujours proportionnel
	var $surface;//// redimensionnement en surface - toujours proportionnel
	var $imgsize=Array();//// variable 'privée', où sont stockées les informations de la photo originale (0=>x,1=>y,2=>format) - avec GD
						//// on peut éventuellement la récupérer en dehors de l'objet
	var $newimgsize=Array();///// tableau ou sont stockés les informations de la nouvelle photo
/// ----- Recadrage ----- ///
	var $gauche;
	var $droit;
	var $haut;
	var $bas;
	var $docsize=Array();/// Var privée, utilisée pour le recadrage
/// ----- Rotation ----- ///
/// Uniquement par pas de 90° //
	var $angle;///
	var $imgT;// objet PEAR_Image_Transform
	var $server; /////// chemin pour arriver au dossier depuis la racine serveur (si on utilise imagemagick)
	var $gd; ////// 1 pour redim gd, 0 pour redim imagemagick
	function traitephoto(){
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
//		$this->imgT=& Image_Transform::factory("Imagick3");
		$this->imgT=& Image_Transform::factory("GD");		
	}
	function recadre(){
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
	function redim() {
		$this->imgT->load($this->photo);
		if(PEAR::isError($this->imgT)) {
			print_r($this->imgT);
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
	function processGD(){
		if($this -> imgsize[2]=="2") {$im_in = ImageCreateFromJpeg($this->photo);}
		if($this -> imgsize[2]=="1") {
			$im_in = ImageCreateFromGif($this->photo);
			$white=imagecolortransparent($im_in);
		}
		if(($this->angle % 360)!=0){
			$im_in=rotation($im_in,$this->angle);
		}
				if($this->recadre()){
			$im_out = ImageCreate($this -> docsize[0],$this -> docsize[1]);
		} else {
			$im_out = ImageCreate($this -> newimgsize[0],$this -> newimgsize[1]);
		}					
		if($this->newimgsize[0]==$this->imgsize[0] && $this->newimgsize[0]==$this->imgsize[0]){
			ImageCopy($im_out, $im_in, 0, 0, $this->gauche, $this->haut, $this -> newimgsize[0],$this -> newimgsize[1]);				
		} else {
			ImageCopyResampleBicubic($im_out, $im_in, 0, 0, $this->gauche, $this->haut, $this -> newimgsize[0],$this -> newimgsize[1],$this -> imgsize[0], $this -> imgsize[1]);
		}
		if($im_in) {ImageDestroy($im_in);}
		return $im_out;
	}		
	function sortie(){
		///// Permet d'appeler un fichier PHP en tant qu'image
		///// Fonctionne uniquement avec GD
		Header("Content-Type:image/gif");// TODO faire le header correct
		$im_out=$this->processGD();
		if($this -> imgsize[2]=="2") {imageJpeg($im_out,'',$this->qualite);}
		if($this -> imgsize[2]=="1") {imageGif($im_out);}
			ImageDestroy($im_out);

	}
	function sauvegarde($type=null){	
		/////////////////////// sauvegarde l'image finale et retourne son nom de fichier ////////////
		
		///// Petite vérification : y-a-t'il un "/" à la fin de $this->path ?
		if(!ereg("/$",$this->path)){$this->path.="/";}
		if($type) {
			$this->nomsouhaite = eregi_replace('[[:alnum:]]+$',$type,$this->nomsouhaite);
		}
		$this->imgT->save($this->path.$this->nomsouhaite,$type?$type:$this->imgT->getImageType());
		return $this->nomsouhaite;
/*
		///// Création du nom du fichier destination //////
		$randnum = md5(time());
		if($this -> imgsize[2]=="1") {
			$ext=".gif";
		} elseif ($this -> imgsize[2]=="2") {
			$ext=".jpg";
		} else {
			$badformat1 = "ok";
		}
		if($badformat1 == "ok") {return false;}	////// On arrête le traitement si le type d'image est non géré. 
		if(empty($this->nomsouhaite)){
			$this -> nomimg=$randnum.$ext;
		} else {
			$this -> nomimg=$this->nomsouhaite;
		}
		///////////////////////////////////////////////////
		
		////////////// Traitement avec GD //////////////////
		if($this->gd){
			$im_out=$this->processGD();
			if($this -> imgsize[2]=="2") {ImageJpeg($im_out,$this->path.$this->nomimg,$this->qualite);}
			if($this -> imgsize[2]=="1") {		
				imagecolortransparent($im_out,$white);
				ImageGif($im_out,$this->path.$this->nomimg);
			}
			ImageDestroy($im_out);
		//////////// Traitement avec imagemagick
		} else {
			$commande="convert $this->photo";
			if($this->recadre()){
				$commande.=" -crop ".$this->docsize[0]."x".$this->docsize[1]."+".$this->gauche."+".$this->haut;
			} else {	
				$commande.=" -resize ".$this->newimgsize[0]."x".$this->newimgsize[1];
			}
			$commande.=" -quality ".$this->qualite." ".$this->path.$this->nomimg;
			passthru($commande,$ok);
		}	
		return $this->nomimg;
		*/
	}
}
//////////// Fin de classe /////////////

//////////////////////////////////////// Nécessaire pour la classe ci-dessus ////////////////////////////////////////////
function ImageCopyResampleBicubic (&$dst_img, &$src_img, $dst_x,$dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h){
/*
port to PHP by John Jensen July 10 2001 (updated 4/21/02) -- original code
(in C, for the PHP GD Module) by jernberg@fairytale.se
I have added various optimization updates to my PHP bicubic function, along
with a few fixes. Thanks to scott@pawprint.net for pointing out that
src_x/y were being factored into the placement on the dst_img.
*/

        $palsize = ImageColorsTotal ($src_img); // retourne le nombre de couleur de la palette
        for ($i = 0; $i < $palsize; $i++) {  // get palette.
                $colors = ImageColorsForIndex ($src_img, $i);
                ImageColorAllocate ($dst_img, $colors['red'], $colors['green'], $colors['blue']);  // retourne un identifiant de couleur, reprÃˆsentant la couleur composÃˆe avec les couleurs RGB
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

                        $color1 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX,$y13));    // imagecolorsforindex retourne un tableau associatif avec les couleurs rouge (red) , vert (green), bleu (blue) qui contiennent les valeurs de la couleur correspondante.

                        $color2 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $sX,$sY));    // imagecolorat retourne l'index de la couleur du pixel situÃˆ aux coordonnÃˆes (x, y),

                        $color3 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34,$y13));

                        $color4 = ImageColorsForIndex ($src_img, ImageColorAt ($src_img, $x34,$sY));


                        $red = ($color1['red'] + $color2['red'] + $color3['red'] +
$color4['red']) / 4;
                        $green = ($color1['green'] + $color2['green'] + $color3['green'] +
$color4['green']) / 4;
                        $blue = ($color1['blue'] + $color2['blue'] + $color3['blue'] +
$color4['blue']) / 4;

                        ImageSetPixel ($dst_img, $i + $dst_x - $src_x, $j + $dst_y - $src_y,    // dessine un pixel au point (x,y) (le coin supÃˆrieur gauche est l'origine (0,0)) dans l'image avec la couleur.
ImageColorClosest ($dst_img, $red, $green, $blue)); // retourne l'index de la couleur de la palette qui est la plus proche de la valeur RGB passÃˆe.

                }
}

}

 function rotation($pic, $angle) {
 /* ParamÃ‹tres :
  * $pic : identifiant d'image obtenu par imagecreate()
  * $angle : angle de rotation (90âˆž, 180âˆž, 270âˆž)
  */

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
  if ($angle==270) {
     for($i=0;$i<$width;$i++) {
        for($j=0;$j<$height;$j++) {
           imagecopy($p, $pic, $j, $i, $i, $height-$j, 1, 1);
        }
     }
  }
return $p;
}
