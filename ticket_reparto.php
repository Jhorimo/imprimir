<?php
date_default_timezone_set('America/Lima');
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$hora = date("g:i:s A");
$fecha = date("d/m/y");

require __DIR__ . '/autoload.php';
use Mike42\Escpos\Printer;
// use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector; // IMPRIMIR EN TABLET

$data = json_decode($_GET['data'],true);
//considerar nombre de pc y nombre de ticketera
// $connector = new WindowsPrintConnector("smb://DESKTOP-MF64RVT/CAJA");
// $printer = new Printer($connector);

$printer_ip = $data['Impresora']['nombre'];           // IMPRIMIR EN TABLET
$connector = new NetworkPrintConnector($printer_ip);  // IMPRIMIR EN TABLET
$printer = new Printer($connector);                   // IMPRIMIR EN TABLET

try {
  	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(1,1);
	$printer -> text("======================================\n");
	$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("TICKET\n");
	$printer -> text("PEDIDO NRO.: ".utf8_decode($data['nro_pedido'])."\n");
	$printer -> text("TELEFONO: ".utf8_decode($data['Cliente']['telefono'])."\n");
	$printer -> selectPrintMode();
	$printer -> text("======================================\n");
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("CLIENTE\n");
	// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text('NOMBRE: '.utf8_decode($data['Cliente']['nombre'])."\n");
	$printer -> text('DIRECCION: '.utf8_decode($data['Cliente']['direccion'])."\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("\n");
	$printer -> text('REFERENCIA: '.utf8_decode($data['Cliente']['referencia']));
	$printer -> text("\n");
	$printer -> text('REPARTIDOR: '.utf8_decode($data['Repartidor']['desc_repartidor']));
	$printer -> text("\n");
	$printer -> text('PREFERENCIA: '.utf8_decode($data['Cliente']['preferencia']));
	$printer -> text("\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("\n");
	$printer -> text("FECHA: ".$fecha."\n");
	$printer -> text("HORA: ".$hora."\n");
	$printer -> text("_______________________________________________\n");
	$printer -> text("CANT     PRODUCTO             PRECIO\n");
	$printer -> text("-----------------------------------------------\n");
	//$printer -> setFont( Printer :: FONT_B );
	//$printer -> setTextSize(1,1);
	$total = 0;
	foreach($data['Detalle'] as $d){
		if($d['cantidad'] > 0){
			$printer -> text("  ".$d['cantidad'].'    '.utf8_decode($d['Producto']['pro_nom']).' '.utf8_decode($d['Producto']['pro_pre']).' | '.number_format(($d['cantidad'] * $d['precio']),2)."\n");
			
			$total = ($d['cantidad'] * $d['precio']) + $total;
		}
	}
	$printer -> text("----------------------------------------------\n");
	// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	// $printer -> text("IMPORTE TOTAL: S/".number_format(($data['total']),2)."\n");
	// $printer -> text("_______________________\n");
	// if($data['id_tpag'] == 1){
	// $vuelto = $data['pago_efe_none'] - $data['pago_efe'];
	// $printer -> text("                             PAGO CON: S/".number_format($data['pago_efe_none'],2)."\n");
	// $printer -> text("                                VUELTO: S/".number_format($vuelto,2)."\n");
	// } else {
	// 	$printer -> text("                             PAGO CON: ".$data['desc_tp']."\n");
	// }
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("------------ FORMA DE PAGO ------------ \n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	foreach($data['Pagos'] as $d){
		$printer -> text($d['nombre'].":  S/ ".number_format($d['monto'],2)."\n");
	}
	if ($data['pago_efe_none'] > 0) {
		$printer -> text("PAGO CON: S/".number_format($data['pago_efe_none'],2)."\n");
		$printer -> text("VUELTO: S/".number_format($data['pago_efe_vuelto'],2)."\n");
	}

	if($data['comis_tar'] > 0){
		$comistarj = number_format(($data['total'] + $data['comis_del'] - $data['desc_monto']+$data['comis_tar'] ),2);
		$printer -> text("COM.TARJETA (".number_format(($data['comis_tar']),2).")                     S/ ".$comistarj."\n");
	}
	$printer -> text("_______________________\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("Gracias por su preferencia\n");
	$printer -> text("\n");
	$printer -> cut();
	$printer -> close();

} catch(Exception $e) {
	echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
}
?>
echo "<script lenguaje="JavaScript">window.close();</script>";