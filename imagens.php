<?php

// Passando cabeçalho para JPEG, pois o retorno do arquivo será a imagem.

header("content-type: image/jpeg");

// Nome do álbum para as fotos
$fotosAlbum = @$_GET["a"];
if($fotosAlbum==""){
	// Álbum padrão se o mesmo não for passado
	$fotosAlbum="galeriateste";
}
$dirImgs = "$fotosAlbum/";

//Ler os arquivos no diretório e organiza em órdem natural
$arquivos = scandir($dirImgs);
array_splice($arquivos,0,2);
natcasesort($arquivos);

// inicializa arrays
$imagens = [];
$imagemTamanhos = [];

// loop para pegar arquivos jpg ou jpeg
// e seus respectivos tamanhos

foreach($arquivos as $arquivo){
	if(preg_match("/^.+\.jpe?g$/",$arquivo)){
		$imagens[]=$arquivo;
		list($imagemLargura,$imagemAltura)=getimagesize($dirImgs.$arquivo);
		$imagemTamanhos[]="$imagemLargura,$imagemAltura";
	}
}

// total de fotos
$fotoTotal = count($imagens);

// foto a ser exibida e tamanho
$foto = @$_GET["n"]-1;
$fotoTamanho = @$_GET["t"];
if(strpos($fotoTamanho,"-")!==false){
	$fotoTamanho=explode("-",$fotoTamanho);
}elseif($fotoTamanho == ""){
	$fotoTamanho="nrm";
}

// usa logo?
$logoUsa = false;

// se vai querer pegar metade da foto
// metade 1, esquerda, ou 2, direita

if(@$_GET["p"]==1){
	$fotoParte=1;
}elseif(@$_GET["p"]==2){
	$fotoParte=2;
}else{
	$fotoParte=0;
}

// qualidade da foto
$fotoQualidade=@$_GET["q"];
if($fotoQualidade==""){
	$fotoQualidade=75;
}

// arredonda tamnho no caso de usar foto dividida
// em partes

$fotoArredondaTamanho=false;
if(@$_GET["f"]==1){
	$fotoArredondaTamanho=true;
}

// se irá fazer interlace na foto

$fotoInterlace=@$_GET["i"];
if($fotoInterlace==""||$fotoInterlace!=0){
	$fotoInterlace=1;
}else{
	$fotoInterlace=0;
}

// Tamanhos padrões de foto
// primeiro indice do array define a altura da imagem, a qual a logo original seguiu para ter seu tamanho definido.

$fotosTamanhos = array(1200
				,100,60
				,600,450
				,720,480
				,760,450
				,800,600
				,1280,720 
				,1920,1080
				);

// verificando se vai ser usado tamanho $logoPadrao
// ou personalizado

$fotoLogo=$fotosTamanhos[0];
if(is_array($fotoTamanho)){
	$fotoLargura=$fotoTamanho[0];
	$fotoAltura=$fotoTamanho[1];
}elseif($fotoTamanho=="peq"||$fotoTamanho=="p"){
	$fotoLargura=$fotosTamanhos[1];
	$fotoAltura=$fotosTamanhos[2];
}elseif($fotoTamanho=="nrm"){
	$fotoLargura=$fotosTamanhos[3];
	$fotoAltura=$fotosTamanhos[4];
}elseif($fotoTamanho=="dvd"){
	$fotoLargura=$fotosTamanhos[5];
	$fotoAltura=$fotosTamanhos[6];
}elseif($fotoTamanho=="wb1"){
	$fotoLargura=$fotosTamanhos[7];
	$fotoAltura=$fotosTamanhos[8];
}elseif($fotoTamanho=="pc1"){
	$fotoLargura=$fotosTamanhos[9];
	$fotoAltura=$fotosTamanhos[10];
}elseif($fotoTamanho=="7hd"){
	$fotoLargura=$fotosTamanhos[11];
	$fotoAltura=$fotosTamanhos[12];
}elseif($fotoTamanho=="fhd"){
	$fotoLargura=$fotosTamanhos[13];
	$fotoAltura=$fotosTamanhos[14];
}

// verifica se a foto esta no intervalo
if($foto<0||$foto>$fotoTotal){
	$foto=0;
}


// pegando a imagem requerida junto ao tamanho

$imagemOriginal = $dirImgs.$imagens[$foto];
$imagemOriginalTamanhos = explode(",",$imagemTamanhos[$foto]);

// logo padrao para a foto
$logoPadrao = 0;

if(is_numeric(@$_GET["l"])){
	$logoPadrao = @$_GET["l"];
}
if($logoPadrao>0&&$logoPadrao<10){
	$logoUsa=true;
}

// ajustando imagens
list($imagemOriginalLargura,$imagemOriginalAltura) = $imagemOriginalTamanhos;
$imagemAltura = $fotoAltura;
$imagemLargura = round($imagemOriginalLargura * $imagemAltura / $imagemOriginalAltura);
if($imagemLargura>$fotoLargura){
	$imagemLargura = $fotoLargura;
	$imagemAltura = round($imagemOriginalAltura * $imagemLargura / $imagemOriginalLargura);
}
$imagemLarguraParte=$imagemLargura;
if($fotoParte>0){
	$imagemLarguraParte = floor($imagemLargura/2);
}
if($fotoArredondaTamanho){
	$imagemLarguraParte = floor($imagemLarguraParte/2)*2;
}elseif(@$_GET["f"]>10){
	$imagemLarguraParte = $imagemLargura = @$_GET["f"];
}

$imgP = imagecreatetruecolor($imagemLarguraParte,$imagemAltura);
$imgPP = imagecreatefromjpeg($imagemOriginal);
$fotoParteX=0;
if($fotoParte==2){
	$fotoParteX=$imagemLarguraParte*-1;
}
imagecopyresampled($imgP,$imgPP,$fotoParteX,0,0,0,$imagemLargura,$imagemAltura,$imagemOriginalLargura,$imagemOriginalAltura);



// se usa logo
if($logoUsa){
	$imgLogoO = "logo.png";
	list($imgLOW,$imgLOH) = getimagesize($imgLogoO);
	$imagemLarguraL = $imgLOW * ($imagemAltura / $fotoLogo);
	$imagemAlturaL = $imgLOH * ($imagemAltura / $fotoLogo);
	$intPXY = 3;
	$imgLogo = imagecreatefrompng($imgLogoO);
	$logoPadraoX = $imagemLargura / ($intPXY-1) * (($intPXY - 1 + $logoPadrao) % $intPXY) - $imagemLarguraL / ($intPXY-1) * (($intPXY - 1 + $logoPadrao) % $intPXY);
	$logoPadraoY = $imagemAltura / ($intPXY-1) * (ceil($logoPadrao / $intPXY) - 1) - $imagemAlturaL / ($intPXY-1) * (ceil($logoPadrao / $intPXY) - 1);
	if($fotoParte==2){
		$logoPadraoX -= $imagemLargura / 2;
	}
	imagecopyresampled($imgP,$imgLogo,$logoPadraoX,$logoPadraoY,0,0,$imagemLarguraL,$imagemAlturaL,$imgLOW,$imgLOH);
	imagedestroy($imgLogo);
}

// Cria as imagens e depois destroi os objetos usados.
imageinterlace($imgP,$fotoInterlace);
imagejpeg($imgP,null,$fotoQualidade);

imagedestroy($imgP);
imagedestroy($imgPP);

?>