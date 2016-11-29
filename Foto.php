<?php

class Foto{

	public function __construct($fotoTotal, $fotoAlturaPequena, $mostragemPorSecao, $mostragemPorLinha, $fotoSigla, $fotoSecao, $tamanhos, $containerID, $layout = "layout.php"){

		global $strPaginaDir;

		$this->total = $fotoTotal;
		self::$alturaPequena = $fotoAlturaPequena;
		$this->mostragem = $mostragemPorSecao;
		$this->linha = $mostragemPorLinha;
		$this->sigla = $fotoSigla;
		$this->secao = $fotoSecao;
		$this->secaoNome = $this->secao;
		$this->tamanhos = $tamanhos;
		$this->container = $containerID;
		$this->diretorio = $strPaginaDir . $this->diretorio . "l=9&a=" . $this->secao."&s=".$this->sigla;
		$this->fotoAmplia = $this->secaoNome."Amplia";
		$this->fotoMonta = $this->secaoNome."Secao";
		$this->verTodas = $this->secaoNome."Todas";
		$this->layout = str_replace("Sigla", $this->sigla, str_replace("coringa", $this->coringa, fread(fopen($layout, "r"), filesize($layout))));
	}

	public static function prepara($albumDiretorio){
		$arquivos =  glob("$albumDiretorio*.jp*g", GLOB_NOSORT);

		foreach($arquivos as $nome){
			list($largura,$altura)=getimagesize($nome);
			$tamanhos[]="$largura,$altura";
		}
		$fotoTotal = count($tamanhos);

		natcasesort($tamanhos);
		$tamanhosQuantidade = array_count_values($tamanhos);
		asort($tamanhosQuantidade);
		asort($tamanhos);
		$tamanhosNumerados = "";
		foreach($tamanhosQuantidade as $k=>$v){
			$numeros="";
			if($v != end($tamanhosQuantidade)){
				$numerosArray = array_keys($tamanhos,$k);
				asort($numerosArray);
				foreach($numerosArray as $vv){
					$n1=$vv+1;
					$numeros.="-$n1";
				}
				$n1=0;
				$n2=0;
				$numeros.="-0";
				$numerosArray = explode("-",$numeros);
				array_shift($numerosArray);
				$numeros="";
				foreach($numerosArray as $vv){
					if(is_numeric($vv)&&$n1!=0){
						if($n1+1==$vv&&$n2==0){
							$n2=$n1;
							$numeros.="({$n2}a";
						}elseif($n1+1==$vv){// verificar
						}elseif($n2!=0){
							$numeros.="$n1)";
							$n2=0;
						}else{
							$numeros.="-$n1";
						}
					}
					$n1=$vv;
				}
				$numeros.=",$k,";
			}else{
				$numeros.=$fotoTotal;
				$numeros.=",$k";
			}
			$tamanhosNumerados.=$numeros;
		}

		self::$total = $fotoTotal;
		self::$tamanhos = explode(",", $tamanhosNumerados);

		for($i=0; $i<count(self::$tamanhos); $i+=3){
			$imagemOriginalLargura = (int) self::$tamanhos[$i+1];
			$imagemOriginalAltura = (int) self::$tamanhos[$i+2];
			self::$tamanhos[$i+1] = ceil($imagemOriginalLargura * self::$alturaGrande / $imagemOriginalAltura);
			self::$tamanhos[$i+2] = self::$alturaGrande;
			if(self::$tamanhos[$i+1]>self::$larguraGrande){
				self::$tamanhos[$i+1] = self::$larguraGrande;
				self::$tamanhos[$i+2] = ceil($imagemOriginalAltura * self::$larguraGrande / $imagemOriginalLargura);
			}
		}

		return true;
	}

	public function monta($intNum, $intTipo=0){
		$intNum = (int) $intNum;
		if($intTipo==0){

			if($this->mostragem > $this->total){
				$this->mostragem = $this->total;
			}
			
			$this->secaoTotal = (int) ($this->total / $this->mostragem);
			if($this->total % $this->mostragem>0){
				$this->secaoTotal+=1;
			}

			($intNum=="")
				?$this->secaoNumero=1
				:($intNum<1||$intNum>$this->secaoTotal)
					?$this->secaoNumero=1
					:$this->secaoNumero=$intNum;

			$this->num = (int) ($this->secaoNumero*$this->mostragem - $this->mostragem+1);
			($this->secaoTotal>$this->secaoNumero)
				?$this->mini($this->num, $this->secaoNumero * $this->mostragem)
				:$this->mini($this->num, $this->total);
		}else{

			$this->amplia($intNum);

		}

	}

	public function fotoConfigura($intNum){
		if($intNum<1||$intNum>$this->total||$intNum==""){
			$intNum=1;
		}
		$achou = 0;
		$i=0;
		while($i<count($this->tamanhos) && $achou!=1){
			if(strpos($this->tamanhos[$i], "-")>-1 || strpos($this->tamanhos[$i], "(") >-1){
				$arrT = str_replace("-", ",", $this->tamanhos[$i]);
				$arrT = str_replace("(", ",-", $arrT);
				$arrT = str_replace("a",",",$arrT);
				$arrT = str_replace(")","",$arrT);
				$arrT = explode(",", $arrT);
				$j=1;
				while($j<count($arrT) && $achou!=1){
					if($arrT[$j]>0){
						if($arrT[$j]==$intNum){
							self::$larguraGrande = $this->tamanhos[$i+1];
							self::$alturaGrande = $this->tamanhos[$i+2];
							$achou=1;
						}
					}else{
						if(($arrT[$j]*-1)<=$intNum&&$intNum<=$arrT[$j+1]){
							self::$larguraGrande = $this->tamanhos[$i+1];
							self::$alturaGrande = $this->tamanhos[$i+2];
							$achou=1;
						}
						$j++;
					}
					$j++;
				}
			}elseif(is_numeric($this->tamanhos[$i])){
				if($this->tamanhos[$i]>= $intNum){
					self::$larguraGrande = $this->tamanhos[$i+1];
					self::$alturaGrande = $this->tamanhos[$i+2];
				}
			}
			self::$larguraPequena = round(self::$larguraGrande * self::$alturaPequena / self::$alturaGrande);
			$this->diretorioGrandes = $this->diretorio ."&n=". $intNum;
			$this->diretorioPequenas = $this->diretorio . "&n=" . $intNum . "&t=p";
			$this->titulo = (isset($this->titulos[$intNum]))
								?$this->titulos[$intNum]
								:$this->titulos[0];
			$this->autor = (isset($this->autor[$intNum]))
								?$this->autores[$intNum]
								:$this->autores[0];
			$i+=3;
		}
	}

	public function mini($intIni, $intFim){
		$intNum = $intIni;
		$fotosMiniaturas = strPegaValor("<miniaturas>","</miniaturas>",$this->layout);

		$layout = "<div id=\"".$this->container."\">\n" . strPegaValor("<miniaturas-secao>", "<miniaturas-secao-".$this->coringa.">", $fotosMiniaturas);

		for($i=1; $i<=$this->secaoTotal;$i++){
			if($i==$this->secaoNumero){
				$layout .= "\n".str_replace("<!-- texto-secao-numero-selecionada -->", numCasas($i, $this->numCasas), strPegaValor("<miniaturas-secao-selecionada>", "</miniaturas-secao-selecionada>", $fotosMiniaturas));
			}else{
				$layout .= "\n".str_replace("<!-- texto-secao-numero-nao-selecionada -->", numCasas($i, $this->numCasas), strPegaValor("<miniaturas-secao-nao-selecionada>", "</miniaturas-secao-nao-selecionada>", $fotosMiniaturas));
			}
		}

		$layout .= "\n".strPegaValor("</miniaturas-secao-nao-selecionada>", "</miniaturas-secao>", $fotosMiniaturas);
		$layout .= "\n".strPegaValor("</miniaturas-secao>", "<miniaturas-".$this->coringa.">", $fotosMiniaturas)."\n";

		while($intNum<=$intFim){
			if($intNum%$this->linha==1){
				$layout .= strPegaValor("<miniaturas-foto-linha-inicio>", "</miniaturas-foto-linha-inicio>", $fotosMiniaturas)."\n";
			}
			$layout .= strPegaValor("<miniaturas-foto>", "</miniaturas-foto>", $fotosMiniaturas)."\n";
			$this->fotoConfigura($intNum);
			$layout = str_replace("<!-- miniatura-foto-largura-pequena -->", self::$larguraPequena, $layout);
			$layout = str_replace("<!-- miniatura-foto-altura-pequena -->", self::$alturaPequena, $layout);
			$layout = str_replace("<!-- miniatura-foto-diretorio-pequena -->", $this->diretorioPequenas, $layout);
			$layout = str_replace("<!-- miniatura-foto-numero -->", $intNum, $layout);

			if($intNum%$this->linha==0||$intNum==$intFim){
				$layout .= strPegaValor("<miniaturas-foto-linha-final>", "</miniaturas-foto-linha-final>", $fotosMiniaturas)."\n";
			}
			$intNum++;
		}
		
		$layout .= strPegaValor("</miniaturas-foto-linha-final>", "</miniaturas>", $fotosMiniaturas)."\n</div>";

		$layout = str_replace("<!-- miniatura-foto-secao-nome -->", $this->secaoNome, $layout);
		$layout = str_replace("<!-- miniatura-foto-amplia -->", $this->fotoAmplia, $layout);
		$layout = str_replace("<!-- texto-secao-nome -->", $this->secaoNome, $layout);
		$layout = str_replace("<!-- texto-secao-foto-monta -->", $this->fotoMonta, $layout);
		$layout = str_replace("\\/", "/", $layout);
		echo $layout;
	}

	public function amplia($intNum){
		$this->num = $intNum;
		$this->secaoNumero = ((int) (($intNum-1)/$this->mostragem+1));

		$fotosAmpliadas = "<ampliadas>".strPegaValor("<ampliadas>", "</ampliadas>", $this->layout);

		$layout = "<div id=\"".$this->container."\">\n" . strPegaValor("<ampliadas>", "<ampliadas-anterior>", $fotosAmpliadas);
		$layout .= ($intNum>1)
						?strPegaValor("<ampliadas-anterior>", "</ampliadas-anterior>", $fotosAmpliadas)
						:" &nbsp; ";
		$layout .= strPegaValor("</ampliadas-anterior>", "<ampliadas-proxima>", $fotosAmpliadas);
		$layout .= ($intNum<$this->total)
						?strPegaValor("<ampliadas-proxima>", "</ampliadas-proxima>", $fotosAmpliadas)
						:" &nbsp; ";
		$this->fotoConfigura($intNum);
		$layout .= strPegaValor("</ampliadas-proxima>", "</ampliadas>", $fotosAmpliadas)."\n</div>";

		$layout = str_replace("<!-- secao-numero -->", numCasas($this->secaoNumero, $this->numCasas), $layout);
		$layout = str_replace("<!-- ampliada-foto-numero -->", $this->num, $layout);
		$layout = str_replace("<!-- ampliada-foto-secao-nome -->", $this->secaoNome, $layout);
		$layout = str_replace("<!-- ver-todas -->", $this->verTodas, $layout);
		$layout = str_replace("<!-- ampliada-foto-amplia -->", $this->fotoAmplia, $layout);
		$layout = str_replace("<!-- ampliada-foto-controle-numero-anterior -->", $intNum-1, $layout);
		$layout = str_replace("<!-- ampliada-foto-controle-numero-proximo -->", $intNum+1, $layout);
		$layout = str_replace("<!-- ampliada-foto-titulo -->", $this->titulo, $layout);
		$layout = str_replace("<!-- ampliada-foto-autor -->", $this->autor, $layout);
		$layout = str_replace("<!-- ampliada-foto-largura-grande -->", self::$larguraGrande, $layout);
		$layout = str_replace("<!-- ampliada-foto-altura-grande -->", self::$alturaGrande, $layout);
		$layout = str_replace("<!-- ampliada-foto-diretorio-grande -->", $this->diretorioGrandes, $layout);
		$layout = str_replace("\\/", "/", $layout);

		echo $layout;
	}

	public static $mostragem = 20;
	public static $linha = 5;
	public static $sigla = "ilhapqt";
	public static $larguraPequena = 90;
	public static $alturaPequena = 60;
	public static $larguraGrande = 600;
	public static $alturaGrande = 450;
	public static $total;
	public static $tamanhos;
	private $secao;
	private $container;
	private $imagens=null;
	private $secaoNumero=1;
	private $secaoTotal=1;
	private $secaoNome;
	private $num;
	private $coringa = "<!--djmibfotos-->";
	private $numCasas = 2;
	private $diretorio="imagens.php?";
	private $diretorioPequenas;
	private $diretorioGrandes;
	private $layout;
	private $fotoAmplia;
	private $fotoMonta;
	private $verTodas;



}


function strPegaValor($ini, $fim, $txt){
	$ini = (!is_integer($ini))
				?strpos($txt, $ini)+strlen($ini)
				:$ini;
	$fim = (!is_integer($fim))
				?(strrpos($txt, $fim)===false)
					?strlen($txt)
					:strrpos($txt, $fim)-strlen($txt)
				:$fim;
	return substr($txt, $ini, $fim);
}

function numCasas($intNum,$intCasa){
	$intNum = "000000000000$intNum";
	return substr($intNum,strlen($intNum)-$intCasa);
}

?>