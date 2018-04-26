<?php
namespace getcne;

class CNE
{
    private $ci;
    private $timeout = 10;
    private $curl = false;

    public function __construct(){

    }

    public function _getUrl($url) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        if (curl_exec($curl) === false) {
            return false;
        } else {
            $return = curl_exec($curl);
        }
        curl_close($curl);

        return $return;
    }

    //Retorna los Datos de un usuario desde el CNE con su Cédula de Identidad
	public function limpiarCampo($valor) {
		$rempl = array('\n', '\t');
		$r = trim(str_replace($rempl, ' ', $valor));
		return str_replace("\r", "", str_replace("\n", "", str_replace("\t", "", $r)));
    }//
    
    //Retorna el número especial del rif
    public function _verificador($X) {
	
		$n = explode("|",$X);
		if(strlen($n[1])==7){
			$c = "0".$n[1];//Cédulas de 7 Digitos
		}else if(strlen($n[1])==6){
			$c = "00".$n[1];//Cédulas de 6 digitos
		}else{
			$c = $n[1];//Cedula normal
		}
	
			$digitos = str_split($n[0].$c);
			$digitos[8] *= 2;
			$digitos[7] *= 3;
			$digitos[6] *= 4;
			$digitos[5] *= 5;
			$digitos[4] *= 6;
			$digitos[3] *= 7;
			$digitos[2] *= 2;
			$digitos[1] *= 3;
	
			switch ($digitos[0]) {
				case 'V':
					$digitoEspecial = 1;
					break;
				case 'E':
					$digitoEspecial = 2;
					break;
				case 'C':
				case 'J':
					$digitoEspecial = 3;
					break;
				case 'P':
					$digitoEspecial = 4;
					break;
				case 'G':
					$digitoEspecial = 5;
					break;
			}
			$suma = (array_sum($digitos)) + ($digitoEspecial*4);
			$residuo = $suma % 11;
			$resta = 11 - $residuo;
			$digitoVerificador = ($resta >= 10) ? 0 : $resta;
			
		return $digitoVerificador;
    }//
    
    //Retorna la informacion del registrado al CNE Venezuela
    public function getCNE($X){
		    //Obtenemos el HTML del CNE
				$context = stream_context_create(array(
					'http' => array(
						'timeout' => $this->timeout
					)
                ));
                if($this->curl){
                    $html = $this->_getUrl('http://www.cne.gov.ve/web/registro_electoral/ce.php?nacionalidad='.$X[0].'&cedula='.$X[1]);
                }else{
                    $html = file_get_contents('http://www.cne.gov.ve/web/registro_electoral/ce.php?nacionalidad='.$X[0].'&cedula='.$X[1] , 0 , $context);
                }
                
		//Eliminamos las etiquetas HTML
			$html = strip_tags($html);
		//Datos a buscar en el texto generado
			$rempl = array('Cédula:', 'Nombre:', 'Estado:', 'Municipio:', 'Parroquia:', 'Centro:', 'Dirección:', 'SERVICIO ELECTORAL', 'Mesa:');
		//Reemplazamos dichos datos por caracter de control
			$r = trim(str_replace($rempl, '|', $this->limpiarCampo($html)));
		//Gebneramos el array desde el caracter de control
			$recurso = explode("|", $r);
		//Verificamos que el resultado sea válido
			if(strlen($recurso[1])>20){
				//Si no es válido o la cédula no existe
				$datos = (object) array(
					"cod"	=>	"201",
					"msg"	=>	"La cédula no se encuentra Registrada o Se envión un dato errado, por favor, verifique"
				);
			}else{
				//Si es válido preparamos el objeto de salida
			$n = explode("-",$recurso[1]);//separamos la cédula
            $nn = explode(" ",$recurso[2]);//Separamos el nombre
            
            //Preparamos el Estado
			$est = explode(" ",$recurso[3]);
			unset($est[0]);
			$estFinal = (count($est)>1) ? implode(" ",$est) : implode("",$est);
            
            //Preparamos el Municipio
			$cid = explode(" ",$recurso[4]);
			unset($cid[0]);
			$cidFinal = (count($cid)>1) ? implode(" ",$cid) : implode("",$cid);
            
            //Preparamos la Parroquia
			$parr = explode(" ",$recurso[5]);
			unset($parr[0]);
			$parrFinal = (count($parr)>1) ? implode(" ",$parr) : implode("",$parr);

                //Preparamos el Objeto
				$datos = (object) array(
					"cod"            => "200",
					"nacionalidad"   => $n[0],
					"cedula"         => $n[1],
					"RIF"            => (strlen($n[1]) == 7 ) ? $n[0].'-0'.$n[1].'-'.$this->_verificador($n[0].'|'.$n[1]) : $n[0].'-'.$n[1].'-'.$this->_verificador($n[0].'|'.$n[1]),
					"cedCompleta"    => $recurso[1],
					"nombreCompleto" => $recurso[2],
					"estado"         => $estFinal,
					"municipio"      => $cidFinal,
					"parroquia"      => $parrFinal,
					"escuela"        => $recurso[6],
				);

			}
		//Retornamos la respuesta
			return $datos;
	}//
}
