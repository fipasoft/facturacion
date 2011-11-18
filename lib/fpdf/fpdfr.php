<?php
require('rotation.php');

class FPDFR extends PDF_Rotate{

	public $font;
	public $font_size;

	public $titulo;
	public $subtitulo;
	
	public $wmark;
	public $watermark;

	public function FPDFR( 
			$fecha = '', $titulo = '', $subtitulo = '', $entidad = '', 
			$folio = '', $wmark=false, $watermark='', $header = 1, $footer = 1,
			$header2 = false, $fmensaje = ''
		){

		$this->FPDF( 'P', 'mm', 'Letter' );
		$this->SetLeftMargin( 13 );

		$this->entidad    =  $entidad;
		$this->fecha      =  $fecha;
		$this->folio      =  $folio;
		$this->footer     =  $footer;
		$this->header     =  $header;
		$this->header2    =  $header2;
		$this->titulo     =  $titulo;
		$this->subtitulo  =  $subtitulo;
		$this->wmark	  =	 $wmark;
		$this->watermark  =  $watermark;	
		$this->fmensaje	  =	 $fmensaje;

		$this->init();
		

	}

	public function init(){

		$this->AliasNbPages();

		$this->corp       =  Session :: get_data( 'sys.dependencia' );
		$this->dept       =  Session :: get_data( 'sys.departamento' );
		$this->font       =  'Arial';
		$this->font_size  =  8;
		$this->logo       =  getcwd() . '/public/img/system/udg.jpg';
		$this->logo2       =  getcwd() . '/public/img/system/header_udg.jpg';

	}


	public function Header()
	{
		
		//watermark
			$this->SetFont( $this->font,'B', $this->font_size + 40 );
			if($this->wmark){
				$this->SetTextColor(255, 0, 0);
				$this->RotatedText(80, 140, $this->watermark, 45);
				
				$this->SetFont( $this->font,'B', $this->font_size );
				$this->SetTextColor(0, 0, 0);
			}
			
		
		if( $this->header ){
			
			//Logo
			if( $this->logo ){
				$this->Image( $this->logo, 13, 9, 10 );
			}
		  
			//T�tulo
			$this->SetFont( $this->font,'B', $this->font_size );
			$this->Cell( 0, 0, strtoupper( $this->corp ), 0, 0, 'R' );
			$this->Ln( 5 );
			$this->Cell( 0, 0, strtoupper( $this->dept ) .($this->entidad != '' ?  ' / ' . $this->entidad : '' ), 0, 0, 'R' );
		  
			if( $this->fecha != '' ){
				$this->SetFont( $this->font,'', $this->font_size - 1);
				$this->Ln( 5 );
				$this->Cell( 0, 0, $this->fecha , 0, 0, 'R' );
			}
		  
			$this->SetFont( $this->font,'B', $this->font_size );
	
			if( $this->titulo != '' ){
				$this->Cell( 0, 0, $this->titulo, 0, 0, 'C' );
				$this->Ln( 5 );
			}
		  
			if( $this->subtitulo != '' ){
				$this->Cell( 0, 0, $this->subtitulo, 0, 0, 'C' );
			}
		  
			$this->Ln( 4 );
			
		}elseif($this->header2){
			//Logo
			if( $this->logo2 ){
				$this->Image( $this->logo2, 5, 0, 170 );
			}
		  
		}
	  
	  
	}

	function RotatedText($x, $y, $txt, $angle)
	{
		//Text rotated around its origin
		$this->Rotate($angle, $x, $y);
		$this->Text($x, $y, $txt);
		$this->Rotate(0);
	}

	public function Footer()
	{
		if( $this->footer ){
			//Posici�n: a 1,5 cm del final
			$this->SetY(-15);
			//Arial italic 8
			$usr = Session :: get_data( 'usr.login' );
			$date = date( 'd-m-Y H:i:s' );

			if($this->fmensaje == ''){
				
				$this->SetFont( $this->font, 'I', $this->font_size - 1);
				$this->Cell( 20, 5, $usr . ' @ ' . $date, 0, 0, 'L' );
				$this->SetFont( $this->font, 'I', $this->font_size - 1 );
				$this->Cell( 0, 5, utf8_decode($this->folio . ',  pág. ' . $this->PageNo().' de {nb}'), 0, 0, 'R' );
			}else{
				$this->SetFont( $this->font, 'I', $this->font_size - 1 );
				$this->Cell( 0, 5, utf8_decode('pág. ' . $this->PageNo().' de {nb}'), 0, 0, 'R' );
				$this->ln(3);
				$this->Cell( 0, 5, utf8_decode( $this->fmensaje ), 0, 0, 'R' );	
			}
			
		}
		
	}

	public function out( $n = '' ){

		$this->Output( $n, 'I' );

	}


}
?>