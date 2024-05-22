<?php
date_default_timezone_set('America/Lima');
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$fecha = date("d-m-Y h:i A");

require __DIR__ . '/autoload.php';
use Mike42\Escpos\Printer;
// use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector; // IMPRIMIR EN TABLET

$data = json_decode($_GET['data'],true);
// $connector = new WindowsPrintConnector("smb://SRVMOVIL/CAJAP1");
// $printer = new Printer($connector);

$printer_ip = '192.168.18.170';                       // IMPRIMIR EN TABLET
$connector = new NetworkPrintConnector($printer_ip);  // IMPRIMIR EN TABLET
$printer = new Printer($connector);                   // IMPRIMIR EN TABLET

try {
  	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(1,1);
	// $printer -> text("======================================\n");
	$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("RECIBO DE INGRESO\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("\n");	
	$printer -> text("FECHA:        ".date('d-m-Y h:i A',strtotime($data['fecha_reg']))."\n");
	$printer -> text("RECIBIDO DE:        ".utf8_decode($data['responsable'])."\n");
	$printer -> text("MOTIVO:        ".utf8_decode($data['motivo'])."\n");
	$printer -> text("IMPORTE DE:        S/.".utf8_decode($data['importe'])."\n");
	$printer -> text("\n");	
	$printer -> text("\n");
	$printer -> text("DATOS DE IMPRESION.\n");
	$printer -> text("FECHRA: ".$fecha."\n");
	$printer -> text("\n");
	$printer -> text("\n");
	$printer -> text("\n");
	$printer -> text("\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("_________________________\n");
	foreach($data['usuario'] as $d){
		$printer -> text("".$d['nombres'].' '.utf8_decode($d['ape_paterno']).' '.utf8_decode($d['ape_materno'])."\n");
			
	}
	$printer -> text("\n");
	$printer -> text("\n");
	$printer -> text("\n");
    $printer -> text("\n");
	$printer -> text("\n");
	$printer -> text("\n");
	$printer -> cut();
	$printer -> close();

} catch(Exception $e) {
	echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
}
?>
echo "<script lenguaje="JavaScript">window.close();</script>";