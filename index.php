<?php

include("Foto.php");
Foto::$larguraPequena = 150;
Foto::$alturaPequena = 100;
Foto::$larguraGrande = 900;
Foto::$alturaGrande = 600;
if(Foto::prepara("img/galeriateste/")){


    $fotos = new Foto(Foto::$total, Foto::$alturaPequena, Foto::$mostragem, Foto::$linha, Foto::$sigla, "galeriateste", Foto::$tamanhos, "ilhafoto");

    if(@$_GET["a"]=="foto"){
        $fotos->monta($_GET["n"], 1);
    }else{
        $fotos->monta(@$_GET["p"]);
    }

}else{

    ?>
    <h1>Aconteceu um erro ao abrir o &aacute;lbum de fotos! Tente atualizar a p&aacute;gina ou tente mais tarde.</h1><?php

}
?>