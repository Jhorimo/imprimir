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

/*
impresion en diferentes precuentas
*/


$activar = true;

$caja = 'CAJA';
if($activar)
{
	// if ($data['rol_sa'] == '5') {
	if ($data['rol_sa'] == '2' || $data['rol_sa'] == '3' || $data['rol_sa'] == '5' ) {

		$array = [
			"SALON_PRINCIPAL" => "CAJA",
			"TERRAZA" => "CAJA",  
			"SEGUNDO_PISO" => "CAJA",
			"RESTAURANTE" => "COCINA"
		];
		
		if(isset($array[$data['ip_pc_set']])){

			$printer_url = "smb://".$data['nombre_pc']."/".$array[$data['ip_pc_set']];

		}else{
			
			$printer_url = "smb://".$data['nombre_pc']."/".$caja;

		}
		
	}else{

		$printer_url = "smb://".$data['nombre_pc']."/".$caja;
	}
} else {
	$printer_url = "smb://".$data['nombre_pc']."/".$caja;
}

// $connector = new WindowsPrintConnector($printer_url);
// $printer = new Printer($connector);

$printer_ip = '192.168.18.170';             // IMPRIMIR EN TABLET
$connector = new NetworkPrintConnector($printer_ip);  // IMPRIMIR EN TABLET
$printer = new Printer($connector);                   // IMPRIMIR EN TABLET

try {
  	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(1,1);
	$printer -> text("======================================\n");
	$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("PRE-CUENTA\n");
	$printer -> selectPrintMode();
	// if ($this->dato->pr == 'pc2') {
		if ($data['pr'] == 'pc2') {
	$printer -> text("CLIENTE: " .utf8_decode($data['nombre_cliente'])."\n");
	}
	if ($data['pr'] == 'pc1') {
	$printer -> text("SALON: ".utf8_decode($data['desc_salon'])."\n");
	$printer -> text("MESA: ".utf8_decode($data['nro_mesa'])."\n");
	$printer -> text("======================================\n");
	}

	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	if ($data['pr'] == 'pc1') {
	$printer -> text("MOZO: " .utf8_decode($data['nombre_mozo'])."\n");
	}
	$printer -> text("FECHA: ".$fecha." HORA: ".$hora."\n");

	// if ($this->dato->pr == 'pc2') {
		if ($data['pr'] == 'pc2') {
		$printer -> text("TIPO: MOSTRADOR\n");
		$printer -> text("======================================\n");
	}

	$printer -> text("_________________________________________\n");
	// $printer -> text("CANT   PRODUCTO           IMPORTE\n");
	$printer -> text("CANT   PRODUCTO               P.U   TOTAL\n");
	$printer -> text("-----------------------------------------\n");
	$total = 0;
	//$printer -> setFont( Printer :: FONT_B );
	//$printer -> setTextSize(1,1);
	foreach($data['Detalle'] as $d){
		if($d['cantidad'] > 0){
			$printer -> text("  ".$d['cantidad'].'    '.utf8_decode($d['Producto']['pro_nom']).' '.utf8_decode($d['Producto']['pro_pre']).' | '.number_format($d['precio'],2).' | '.number_format(($d['cantidad'] * $d['precio']),2)."\n");
			$total = ($d['cantidad'] * $d['precio']) + $total;
		}
	}
	$printer -> text("-----------------------------------------\n");
	if($data['descuento']){
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		$printer -> text("Sub TOTAL: S/".number_format(($total),2)."\n");
		$printer -> text("Descuento: S/".number_format(($data['descuento']),2)."\n");
	}
	if($data['descuento']){
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		$printer -> text("IMPORTE TOTAL: S/".number_format(($total- $data['descuento']),2)."\n");
	}else{
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		$printer -> text("IMPORTE TOTAL: S/".number_format(($total),2)."\n");
	}

	$printer -> text("\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("Este no es un comprobante de Pago.\n");
	$printer -> text("Ingrese su DNI o RUC si desea Boleta o Factura\n\n");
	$printer -> text("RUC: _______________________________\n \n");
	$printer -> text("RZ : _______________________________\n");
	$printer -> text("\n");
	$printer -> text("Emitido por: www.brainpos.pe\n");
	$printer -> text("Gracias por su gentil preferencia\n");
	$printer -> text("\n");
	$printer -> text("Observación :\n");
	$printer -> text("\n\n\n\n");

	$printer -> cut();
	$printer -> close();

} catch(Exception $e) {
	echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
}
?>
echo "<script lenguaje="JavaScript">window.close();</script>";