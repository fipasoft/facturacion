<?php

/** KumbiaForms - PHP Rapid Development Framework *****************************
*
* Copyright (C) 2005-2007 Andr�s Felipe Guti�rrez (andresfelipe at vagoogle.net)
*
* This framework is free software; you can redistribute it and/or
* modify it under the terms of the GNU Lesser General Public
* License as published by the Free Software Foundation; either
* version 2.1 of the License, or (at your option) any later version.
*
* This framework is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
* Lesser General Public License for more details.
*
* You should have received a copy of the GNU Lesser General Public
* License along with this framework; if not, write to the Free Software
* Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*
* Este framework es software libre; puedes redistribuirlo y/o modificarlo
* bajo los terminos de la licencia p�blica general GNU tal y como fue publicada
* por la Fundaci�n del Software Libre; desde la versi�n 2.1 o cualquier
* versi�n superior.
*
* Este framework es distribuido con la esperanza de ser util pero SIN NINGUN
* TIPO DE GARANTIA; dejando atr�s su LADO MERCANTIL o PARA FAVORECER ALGUN
* FIN EN PARTICULAR. Lee la licencia publica general para m�s detalles.
*
* Debes recibir una copia de la Licencia P�blica General GNU junto con este
* framework, si no es asi, escribe a Fundaci�n del Software Libre Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
****************************************************************************
* Some PHP Utility Functions
****************************************************************************/

class Utils{

/**
 * Merge Two Arrays Overwriting Values $a1
 * from $a2
 *
 * @param array $a1
 * @param array $a2
 * @return array
 */
static function array_merge_overwrite($a1, $a2){
	foreach($a2 as $key2 => $value2){
	  	if(!is_array($value2)){
		    $a1[$key2] = $value2;
		} else {
		  if(!is_array($a1[$key2]))
			  	$a1[$key2] = $value2;
		  else $a1[$key2] = array_merge_overwrite($a1[$key2], $a2[$key2]);
		}
	}
	return $a1;
}

/**
 * Inserts a element into a defined position
 * in a array
 *
 * @param array $form
 * @param mixed $index
 * @param mixed $value
 * @param mixed $key
 */
static function array_insert(&$form, $index, $value, $key=null){
	$ret = array();
	$n = 0;
	$i = false;
	foreach($form as $keys => $val){
		if($n!=$index){
			$ret[$keys] = $val;
		} else {
		  	if(!$key){
				$ret[$index] = $value;
				$i = true;
			} else {
				$ret[$key] = $value;
				$i = true;
			}
			$ret[$keys] = $val;
		}
		$n++;
	}
	if(!$i){
		if(!$key){
			$ret[$index] = $value;
			$i = true;
		} else {
			$ret[$key] = $value;
			$i = true;
		}
	}
	$form = $ret;
}

/**
 * Las siguientes funciones son utilizadas para la generaci�n
 * de versi�nes escritas de numeros
 *
 * @param numeric $a
 * @return string
 */
static function value_num($a){
  if($a<=21){
	switch ($a){
	  	case 1: return 'UNO';
	  	case 2: return 'DOS';
	  	case 3: return 'TRES';
	  	case 4: return 'CUATRO';
	  	case 5: return 'CINCO';
	  	case 6: return 'SEIS';
	  	case 7: return 'SIETE';
	  	case 8: return 'OCHO';
	  	case 9: return 'NUEVE';
	  	case 10: return 'DIEZ';
	  	case 11: return 'ONCE';
	  	case 12: return 'DOCE';
	  	case 13: return 'TRECE';
	  	case 14: return 'CATORCE';
	  	case 15: return 'QUINCE';
	  	case 16: return 'DIECISEIS';
	  	case 17: return 'DIECISIETE';
	  	case 18: return 'DIECIOCHO';
	  	case 19: return 'DIECINUEVE';
	  	case 20: return 'VEINTE';
	  	case 21: return 'VEINTIUN';
	}
  } else {
    if($a<=99){
	    if($a>=22&&$a<=29)
	  		return "VENTI".value_num($a % 10);
	  	if($a==30) return  "TREINTA";
	  	if($a>=31&&$a<=39)
	  		return "TREINTA Y ".value_num($a % 10);
	  	if($a==40) $b = "CUARENTA";
	  	if($a>=41&&$a<=49)
	  		return "CUARENTA Y ".value_num($a % 10);
	  	if($a==50) return "CINCUENTA";
	  	if($a>=51&&$a<=59)
	  		return "CINCUENTA Y ".value_num($a % 10);
	  	if($a==60) return "SESENTA";
	  	if($a>=61&&$a<=69)
	  		return "SESENTA Y ".value_num($a % 10);
	  	if($a==70) return "SETENTA";
	  	if($a>=71&&$a<=79)
	  		return "SETENTA Y ".value_num($a % 10);
	  	if($a==80) return "OCHENTA";
	  	if($a>=81&&$a<=89)
  			return "OCHENTA Y ".value_num($a % 10);
	  	if($a==90) return "NOVENTA";
	  	if($a>=91&&$a<=99)
  			return "NOVENTA Y ".value_num($a % 10);
	} else {
	  	if($a==100) return "CIEN";
	  	if($a>=101&&$a<=199)
	  		return "CIENTO ".value_num($a % 100);
	  	if($a>=200&&$a<=299)
	  		return "DOSCIENTOS ".value_num($a % 100);
	  	if($a>=300&&$a<=399)
	  		return "TRECIENTOS ".value_num($a % 100);
	  	if($a>=400&&$a<=499)
	  		return "CUATROCIENTOS ".value_num($a % 100);
	  	if($a>=500&&$a<=599)
	  		return "QUINIENTOS ".value_num($a % 100);
	  	if($a>=600&&$a<=699)
	  		return "SEICIENTOS ".value_num($a % 100);
	  	if($a>=700&&$a<=799)
	  		return "SETECIENTOS ".value_num($a % 100);
	  	if($a>=800&&$a<=899)
	  		return "OCHOCIENTOS ".value_num($a % 100);
	  	if($a>=901&&$a<=999)
	  		return "NOVECIENTOS ".value_num($a % 100);
	}
  }
}

static function millones($a){
  	$a = $a / 1000000;
  	if($a==1)
		return "UN MILLON ";
	else
		return value_num($a)." MILLONES ";
}

static function miles($a){
  	$a = $a / 1000;
  	if($a==1)
		return "MIL";
	else
		return value_num($a)."MIL ";
}

static function numlet($a, $p, $c){
  	$val = "";
  	$v = $a;
  	$a = (int) $a;
  	$d = round($v - $a, 2);
	if($a>=1000000){
	  	$val = millones($a - ($a % 1000000));
	  	$a = $a % 1000000;
	}
	if($a>=1000){
	  	$val.= miles($a - ($a % 1000));
	  	$a = $a % 1000;
	}
	$val.= value_num($a)." $p ";
	if($d){
		$d*=100;
		$val.=" CON ".value_num($d)." $c ";
	}
	return $val;
}

static function money_letter($valor, $moneda, $centavos){
	return numlet($valor, $moneda, $centavos);
}


static function to_human($num){
	if($num<1024){
		return $num." bytes";
	} else {
		if($num<1024*1024){
			return round($num/1024, 2)." kb";
		} else {
			return round($num/1024/1024, 2)." mb";
		}
	}
}

static function fecha_hora_convertir($f){
	$tmp = explode(' ', $f);
	$fecha = str_replace('-', '/', self :: fecha_convertir($tmp[0]));
	$hora = substr($tmp[1], 0, 5);
	return $fecha . ' ' . $hora;
}

static function fecha_convertir($f){
	$fecha = '';
	if(substr_count($f,'-') > 0){
		$f = explode('-', $f);
		$fecha = $f[2] . '-' . $f[1] . '-' .$f[0];
	}else if(substr_count($f,'/') > 0){
		$f = explode('/', $f);
		$fecha = $f[2] . '/' . $f[1] . '/' .$f[0];		
	}
	return $fecha;
}

	static function convierteFechaMySql($fecha){
		$i=explode("/",$fecha);
		$i=$i[2]."-".$i[1]."-".$i[0];
		return $i;
	}


	static function convierteFecha($fecha){
		$i=explode("-",$fecha);
		$i=$i[2]."/".$i[1]."/".$i[0];
		return $i;
	}
	

static function fecha_espanol( $f ){
	
	$fecha = explode('-',$f);
	$mes = '';
	switch( intval($fecha[1], 10) ){
		case  1:	$mes = 'enero';	break;
		case  2:	$mes = 'febrero'; break;
		case  3:	$mes = 'marzo'; break;
		case  4:	$mes = 'abril'; break;
		case  5:	$mes = 'mayo'; break;
		case  6:	$mes = 'junio'; break;
		case  7:	$mes = 'julio'; break;
		case  8:	$mes = 'agosto'; break;
		case  9:	$mes = 'septiembre'; break;
		case 10:	$mes = 'octubre'; break;
		case 11:	$mes = 'noviembre'; break;
		case 12:	$mes = 'diciembre'; break;
		default : $mes='';
	}
	
	$f = intval($fecha[2], 10) . " de " . $mes . " de " . intval($fecha[0], 10);
	return $f;
}

static function fecha_espanol_periodo($ini,$fin){
	if($ini->format("m")==$fin->format("m")){
		return intval($ini->format("d"), 10)." al ".intval($fin->format("d"), 10)." de ".Utils::mes_espanol($fin->format("m"));
	}else{
		
		return intval($ini->format("d"), 10)." de ".Utils::mes_espanol($ini->format("m"))." al ".intval($fin->format("d"), 10)." de ".Utils::mes_espanol($fin->format("m"));
	}
}

static function fecha_espanol_periodo_acortada($ini,$fin){
	if($ini->format("m")==$fin->format("m")){
		return substr(Utils::mes_espanol($ini->format('m')),0,3) . "." . intval($ini->format("d"), 10) . "-" . intval($fin->format("d"), 10);
		//return intval($ini->format("d"), 10)." al ".intval($fin->format("d"), 10)." de ".Utils::mes_espanol($fin->format("m"));
	}else{
		
		return substr(Utils::mes_espanol($ini->format('m')),0,3) . "." . intval($ini->format("d"), 10) . "-" . substr(Utils::mes_espanol($fin->format('m')),0,3) . "." . intval($fin->format("d"), 10);
		//return intval($ini->format("d"), 10)." de ".Utils::mes_espanol($ini->format("m"))." al ".intval($fin->format("d"), 10)." de ".Utils::mes_espanol($fin->format("m"));
	}
}

static function fecha_espanol_sin_anio( $f ){
	
	$fecha = explode('-',$f);
	$mes = '';
	switch( intval($fecha[1], 10) ){
		case  1:	$mes = 'enero';	break;
		case  2:	$mes = 'febrero'; break;
		case  3:	$mes = 'marzo'; break;
		case  4:	$mes = 'abril'; break;
		case  5:	$mes = 'mayo'; break;
		case  6:	$mes = 'junio'; break;
		case  7:	$mes = 'julio'; break;
		case  8:	$mes = 'agosto'; break;
		case  9:	$mes = 'septiembre'; break;
		case 10:	$mes = 'octubre'; break;
		case 11:	$mes = 'noviembre'; break;
		case 12:	$mes = 'diciembre'; break;
		default : $mes='';
	}
	
	$f = intval($fecha[2], 10) . " de " . $mes;
	return $f;
}

static function fecha_convertir_hibrida( $f ){
	$fecha = '';
	if(substr_count($f,'-') > 0){
		$f = explode('-', $f);
		$fecha = $f[2] . '-' . substr( strtolower( self :: mes_espanol( $f[1] ) ), 0, 3 ) . '-' .$f[0];
	}else if(substr_count($f,'/') > 0){
		$f = explode('/', $f);
		$fecha = $f[2] . '/' . substr( strtolower( self :: mes_espanol( $f[1] ) ), 0, 3 ) . '/' .$f[0];		
	}
	return $fecha;
}

static function fecha_convertir_hibrida_corta( $f ){
	$fecha = '';
	if(substr_count($f,'-') > 0){
		$f = explode('-', $f);
		$fecha = substr( intval( $f[2] ), 0, 2 ) . '-' . substr( strtolower( self :: mes_espanol( $f[1] ) ), 0, 3 ) . '-' . substr( $f[0], 2, 2 );
	}else if(substr_count($f,'/') > 0){
		$f = explode('/', $f);
		$fecha = substr( intval( $f[2] ), 0, 2 ) . '/' . substr( strtolower( self :: mes_espanol( $f[1] ) ), 0, 3 ) . '/' . substr( $f[0], 2, 2 );		
	}
	return $fecha;
}


static function mes_espanol( $m ){
	
	switch( intval( $m, 10 ) ){
		case 1:		$mes = 'Enero';	break;
		case 2:		$mes = 'Febrero'; break;
		case 3:		$mes = 'Marzo'; break;
		case 4:		$mes = 'Abril'; break;
		case 5:		$mes = 'Mayo'; break;
		case 6:		$mes = 'Junio'; break;
		case 7:		$mes = 'Julio'; break;
		case 8:		$mes = 'Agosto'; break;
		case 9:		$mes = 'Septiembre'; break;
		case 10:	$mes = 'Octubre'; break;
		case 11:	$mes = 'Noviembre'; break;
		case 12:	$mes = 'Diciembre'; break;
		default:	$mes='';
	}
	
	return $mes;
	
}

static function mes_espanol_clave( $m, $l = 3 ){
	
	return
		substr( strtoupper( self :: mes_espanol( $m ) ), 0, $l );
	
}

static function dia_espanol($d){
	$d = intval( $d, 10 );
	
	$d = ( $modo == 'iso' && $d == 7 ? 0 : $d );
	
	switch( $d ){
		case 0:	$dia = 'Domingo'; break;
		case 1:	$dia = 'Lunes';	break;
		case 2:	$dia = 'Martes'; break;
		case 3:	$dia = 'Mi&eacute;rcoles'; break;
		case 4:	$dia = 'Jueves'; break;
		case 5:	$dia = 'Viernes'; break;
		case 6:	$dia = 'S&aacute;bado'; break;
		default : $dia='';
	}
	
	return $dia;
	
}

static function dia_espanol_id( $d, $modo = '' ){
	$d = intval( $d, 10 );
	
	$d = ( $modo == 'iso' && $d == 7 ? 0 : $d );
	
	switch( $d ){
		case 0:	$dia = 'D'; break;
		case 1:	$dia = 'L';	break;
		case 2:	$dia = 'M'; break;
		case 3:	$dia = 'I'; break;
		case 4:	$dia = 'J'; break;
		case 5:	$dia = 'V'; break;
		case 6:	$dia = 'S'; break;
		default : $dia='';
	}
	
	return $dia;
	
}

static function dia_semana($datetime){
$arr = explode("-", $datetime);
$d = date("w", mktime(0,0,0, $arr[1], $arr[2], $arr[0]));
return self :: dia_espanol($d);
}

static function ordinales($n){
	switch($n){
		case 1:
			return 'primer';
			break;
		case 2:
			return 'pegundo';
			break;
		case 3:
			return 'tercer';
			break;
		case 4:
			return 'cuarto';
			break;
		case 5:
			return 'quinto';
			break;
		case 6:
			return 'sexto';
			break;
	}
}

static function textoPlano($text){
$tofind = "ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ";
$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
$text=(strtr($text,$tofind,$replac));

$acentos=array("á","é","í","ó","ú","Ã¡","Ã©","Ã","Ã³","Ãº");
$rem=array("a","e","i","o","u","a","e","i","o","u");
$text=str_replace($acentos,$rem,$text);
return $text;
}

static function idValido($text){
$text=textoPlano($text);
$text=str_replace(" ","_",$text);
return $text;
}

static function getRealIP()
{
   if( $_SERVER['HTTP_X_FORWARDED_FOR'] != '' )
   {
      $client_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR']
               :
               "unknown" );
      // los proxys van añadiendo al final de esta cabecera
      // las direcciones ip que van "ocultando". Para localizar la ip real
      // del usuario se comienza a mirar por el principio hasta encontrar
      // una dirección ip que no sea del rango privado. En caso de no
      // encontrarse ninguna se toma como valor el REMOTE_ADDR
      $entries = split('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);
      reset($entries);
      while (list(, $entry) = each($entries))
      {
         $entry = trim($entry);
         if ( preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/", $entry, $ip_list) )
         {
            // http://www.faqs.org/rfcs/rfc1918.html
            $private_ip = array(
                  '/^0\./',
                  '/^127\.0\.0\.1/',
                  '/^192\.168\..*/',
                  '/^172\.((1[6-9])|(2[0-9])|(3[0-1]))\..*/',
                  '/^10\..*/');
            $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);
            if ($client_ip != $found_ip)
            {
               $client_ip = $found_ip;
               break;
            }
         }
      }
   }
   else
   {
      $client_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
            $_SERVER['REMOTE_ADDR']
            :
            ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
               $_ENV['REMOTE_ADDR']
               :
               "unknown" );
   }
   return $client_ip;
}

static function move($origen,$destino){
  copy($origen,$destino);
  unlink($origen);
}  

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
recibe la fecha en formato mysql y lo devuelve con el mes en espaÃ±ol
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

static function iniciales($nombre){
	$iniciales = "";
	
	$nombres = explode(" ", $nombre);
	foreach($nombres as $n){
		$iniciales .= $n[0];
	}
	
	return strtoupper($iniciales);
}

static function startsWith($haystack,$needle,$case=true) {
    if($case){return (strcmp(substr($haystack, 0, strlen($needle)),$needle)===0);}
    return (strcasecmp(substr($haystack, 0, strlen($needle)),$needle)===0);
}

static function endsWith($haystack,$needle,$case=true) {
    if($case){return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);}
    return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
}


}
?>