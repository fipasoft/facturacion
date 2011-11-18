<?php

include_once "lib/fpdf/main.php";

class CoffeeReport extends FPDF {

	public $title;
	public $logo;
	public $date;
	public $note;
	public $show_file;
	public $company;

	function Header(){
		$this->Image("img/logo.jpg", 20, 20, 30, 20);
		print_r($this->FontFamily);
		$this->AddFont('Aquabase','','aquabase.php');
		$y = 25;
		if($this->company){
			$this->SetXY(55, 25);
			$this->SetFont("Aquabase", "", 22);
			$this->Write(2, ucwords(strtolower($this->company)));
			$y+=7;
		}
		if($this->title){
			$this->SetXY(55, $y);
			$this->SetFont("Aquabase", "", 18);
			$this->Write(2, ucwords(strtolower($this->title)));
			$y+=7;
		}
		if($this->note){
			$this->SetXY(55, $y);
			$this->SetFont("Aquabase", "", 12);
			$this->Write(2, ucwords(strtolower($this->note)));
		}
	}

	function CoffeeReport(){
		
		$orientation = "P";
		$sumArray = 120;		
		
		if($sumArray>250) $paper = 'legal';
		else $paper = 'letter';

		if($paper=='letter'&&$orientation=='P'){
			$widthPage = 220;
		}
		if($paper=='legal'&&$orientation=='L'){
			$widthPage = 350;
		}
		if($paper=='letter'&&$orientation=='L'){
			$widthPage = 270;
		}		
		$this->FPDF($orientation, 'mm', $paper);
			
	}
	
	function OpenPage(){
		$this->OpenPage();
		$this->AddPage();
	}

}


?>