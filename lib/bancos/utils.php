<?php
// bancos2, Creado el 27/01/2009
/** 
 * Utils
 * 
 * @package    Bancos
 * @author     mimeks <mimex@fipasoft.com.mx>
 * @copyright  2009 FiPa Software (contacto at fipasoft.com.mx)
 * @license    http://www.gnu.org/licenses/gpl.html  GNU General Public License (GPL)
 * 
 */
class Bancos{

	static function n2l_principal($n){
		$p_grupo[1] = "";
		$p_grupo[2] = "mil";
		$p_grupo[3] = "millon";
		$p_grupo[4] = "mil millon";
		$p_grupo[5] = "billon";
		$p_grupo[6] = "mil";
		
		$cantidad = '';
		$lon = strlen($n);
		$grupo = ceil($lon/3);
		if($lon%3==0){
			$digitos = substr($n,0,3);
			$n = substr($n, 3);
		}else{
			$digitos = substr($n,0,$lon%3);
			$n = substr($n,$lon%3);
		}
		$cantidad = self :: n2l_convertir($digitos).($digitos>0?$p_grupo[$grupo].($grupo>2 && $digitos>1?'es ':' '):'').($grupo>1 ? self :: n2l_principal($n):'').'';
		return $cantidad;
	}
	
	static function n2l_convertir($n){
		# banco de palabras
		$p_cen[1] = 'cien';		# centenas
		$p_cen[2] = 'doscientos';
		$p_cen[3] = 'trescientos';
		$p_cen[4] = 'cuatrocientos';
		$p_cen[5] = 'quinientos';
		$p_cen[6] = 'seiscientos';
		$p_cen[7] = 'setecientos';
		$p_cen[8] = 'ochocientos';
		$p_cen[9] = 'novecientos';
		
		$p_dec[1] = 'diez';		# decenas
		$p_dec[2] = 'veinte';
		$p_dec[3] = 'treinta';
		$p_dec[4] = 'cuarenta';
		$p_dec[5] = 'cincuenta';
		$p_dec[6] = 'sesenta';
		$p_dec[7] = 'setenta';
		$p_dec[8] = 'ochenta';
		$p_dec[9] = 'noventa';
		
		$p_uni[1] = 'un';		# unidades
		$p_uni[2] = 'dos';
		$p_uni[3] = 'tres';
		$p_uni[4] = 'cuatro';
		$p_uni[5] = 'cinco';
		$p_uni[6] = 'seis';
		$p_uni[7] = 'siete';
		$p_uni[8] = 'ocho';
		$p_uni[9] = 'nueve';
		
		$p_esp[11] = 'once';	# palabras especiales
		$p_esp[12] = 'doce';
		$p_esp[13] = 'trece';
		$p_esp[14] = 'catorce';
		$p_esp[15] = 'quince';
	
		$cantidad = '';
		$cen = intval($n/100);
		$n = $n - ($cen*100);
		$dec = intval($n/10);
		$uni = $n - ($dec*10);
		if($cen>0)  # centenas
			$cantidad .= $p_cen[$cen].($cen==1 && ($dec!=0 || $uni!=0)?'to':'').' ';
		if($dec>0){ # decenas
			if($dec == 1 && $uni>=1 && $uni<=5){
				$cantidad .= $p_esp[$dec.$uni].' ';
				$uni=0;
			}else
				$cantidad .= $p_dec[$dec].' ';
		}
		if($uni>0)  # unidades
			$cantidad .= ($dec>0?'y ':'').$p_uni[$uni].' ';
		return $cantidad;
	}
	
	static function n2l($n){
		if($n>=pow(10,12))
			return 'El numero debe ser menor que 10 elevado a la 12...';
		else
			return self :: n2l_principal($n);
	}
	
	/***
	fecha2letras
	recibe la fecha en formato mysql y lo devuelve con el mes en español
	***/
	static function fecha2letras( $f ){
		$fecha = explode('-',$f);
		switch($fecha[1]){
			case '01':	$mes = 'Enero';	break;
			case '02':	$mes = 'Febrero'; break;
			case '03':	$mes = 'Marzo'; break;		
			case '04':	$mes = 'Abril'; break;
			case '05':	$mes = 'Mayo'; break;
			case '06':	$mes = 'Junio'; break;
			case '07':	$mes = 'Julio'; break;
			case '08':	$mes = 'Agosto'; break;
			case '09':	$mes = 'Septiembre'; break;
			case '10':	$mes = 'Octubre'; break;
			case '11':	$mes = 'Noviembre'; break;
			case '12':	$mes = 'Diciembre'; break;
			default : $mes='';
		}
		$f = $fecha[2]." de ".$mes." de ".$fecha[0];
		return $f;
	}
	
	# x compatibilidad de scripts...
	static function NumerosALetras( $n ){
		return self :: n2l($n);
	}

 	
}
?>
