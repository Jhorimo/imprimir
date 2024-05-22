<?php
date_default_timezone_set('America/Lima');
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$hora = date("g:i:s A");

require __DIR__ . '/autoload.php';
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$connector = new WindowsPrintConnector("smb://Lenovo/COCINA");
$printer = new Printer($connector);

try {
  	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(1,1);
	$printer -> text("======================================\n");
	$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("PRE-CUENTA\n");
	$printer -> selectPrintMode();
	$printer -> text("SALON: A\n");
	$printer -> text("MESA: 01\n");
	$printer -> text("======================================\n");
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("CLIENTE: PUBLICO EN GENERAL\n");
	$printer -> text("HORA: 09:30\n");
	$printer -> text("_________________________________________\n");
	$printer -> text("CANT   PRODUCTO           IMPORTE\n");
	$printer -> text("-----------------------------------------\n");
	//$printer -> setFont( Printer :: FONT_B );
	//$printer -> setTextSize(1,1);
	$printer -> text("1  Pizza  50.00\n");
	$printer -> text("-----------------------------------------\n");
	$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("IMPORTE TOTAL: S/100.00\n");
	$printer -> text("\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("Este no es un comprobante de Pago.\n");
	$printer -> text("Ingrese su RUC si desee Factura\n\n");
	$printer -> text("_______________________________\n");
	$printer -> text("Gracias por su gentil preferencia\n");
	$printer -> text("\n");
	$printer -> cut();
	$printer -> close();

} catch(Exception $e) {
	echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
}
?>
<!--echo "<script lenguaje="JavaScript">window.close();</script>";-->