<?php
date_default_timezone_set('America/Lima');
setlocale(LC_ALL,"es_ES@euro","es_ES","esp");
$fecha = date("d/m/y");
$hora = date("g:i:s A");

require __DIR__ . '/num_letras.php';
require __DIR__ . '/autoload.php';
require __DIR__ . '/phpqrcode/qrlib.php';

use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
// use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector; // IMPRIMIR EN TABLET

$date = date('d-m-Y H:i:s');
$data = json_decode($_GET['data'],true);

// $printer_url = "smb://".$data['nombre_pc']."/".$data['Impresora']['nombre'];
// $connector = new WindowsPrintConnector($printer_url);
// $printer = new Printer($connector);

$printer_ip = $data['Impresora']['nombre'];           // IMPRIMIR EN TABLET
$connector = new NetworkPrintConnector($printer_ip);  // IMPRIMIR EN TABLET
$printer = new Printer($connector);                   // IMPRIMIR EN TABLET

$copias = 2;

try {

	$new_igvrc = ($data['Configuracion']['com_rc_val'] > '0')? ((1 + $data['igv'])+ ($data['Configuracion']['com_rc_val'] / 100) ) : (1 + $data['igv']);

	$new_igv = (1 + $data['igv']);
for($i = 0; $i < $copias; $i++){

	///////////////descomentar esto para logo
	 $logo = EscposImage::load("logo.png", false);
   	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	 $printer -> bitImage($logo);
	 // $printer -> feed();

	 $printer -> text("===============================================\n");
	///////////////hasta aqui descomentar esto para logo

	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setEmphasis(true);

	$printer -> text(utf8_decode($data['Empresa']['nombre_comercial'])."\n");
	$printer -> text(utf8_decode($data['Empresa']['razon_social'])."\n");
	$printer -> setEmphasis(false);
	$printer -> text("RUC: ".utf8_decode($data['Empresa']['ruc'])."\n");

	if ($data['Empresa']['direccion_comercial']!='-') {
		$printer -> text(utf8_decode($data['Empresa']['direccion_comercial'])."\n");
	}

	if ($data['Empresa']['celular']!='') {
		$printer -> text("TELF: ".utf8_decode($data['Empresa']['celular'])."\n");
	}

	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("-----------------------------------------------\n");

	$elec = (($data['id_tdoc'] == 1 || $data['id_tdoc'] == 2) && $data['Empresa']['sunat'] == 1) ? 'ELECTRONICA' : '';
	$printer -> text($data['desc_td']." ".$elec."\n");
	$printer -> text($data['ser_doc']."-".$data['nro_doc']."\n");
	$printer -> text("-----------------------------------------------\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("FECHA DE EMISION: ".date('d-m-Y h:i A',strtotime($data['fec_ven']))."\n");
	
	if($data['id_tped'] == 1){
		$tipo_atencion = utf8_decode($data['Pedido']['desc_salon']).' - MESA: '.utf8_decode($data['Pedido']['nro_mesa']);
	}else if ($data['id_tped'] == 2){
		$tipo_atencion = "MOSTRADOR";
	}else if ($data['id_tped'] == 3){
		$tipo_atencion = "DELIVERY";
	}
	$printer -> text("TIPO DE ATENCION: ".$tipo_atencion."\n");
	$printer -> text("------------------------------------------------\n");

	$printer -> setEmphasis(true);
	$printer -> text("CLIENTE: ".utf8_decode($data['Cliente']['nombre'])."\n");


	if ($data['Cliente']['tipo_cliente'] == 1){
		$printer -> text("DNI: ".$data['Cliente']['dni']."\n");
	}else if ($data['Cliente']['tipo_cliente'] == 2){
		$printer -> text("RUC: ".$data['Cliente']['ruc']."\n");
	}
	$printer -> setEmphasis(false);

	if ($data['Cliente']['direccion']!='-') {
		$printer -> text("DIRECCION: ".utf8_decode($data['Cliente']['direccion'])."\n");
	}

	if ($data['Cliente']['telefono']!='0') {
		$printer -> text("TELEFONO: ".$data['Cliente']['telefono']."\n");
	}

	if ($data['Cliente']['referencia']!='') {
		$printer -> text("REFERENCIA: ".utf8_decode($data['Cliente']['referencia'])."\n");
	}

	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("------------------------------------------------\n");
	$printer -> text("PRODUCTO               CANT  P.U  DESCT. IMPORTE\n");
	$printer -> text("------------------------------------------------\n");
	
	$total = 0;
	foreach($data['Detalle'] as $d){
		if($d['cantidad'] > 0){

			// $printer -> text(utf8_decode($d['nombre_producto'])."  ".$d['cantidad']."   ".utf8_decode($d['precio_unitario'])."   ".number_format(($d['cantidad'] * $d['precio_unitario']),2)."\n");
			// $total = ($d['cantidad'] * $d['precio_unitario']) + $total;

	$limite = 23;
	$listItems = '';

	// $descripcionprod = utf8_decode($d['nombre_producto']); // PRODUCTO Y PRESENTACION
	$descripcionprod = utf8_decode($d['nombre_presentacion']); //PRESENTACION

  $division = round(strlen($descripcionprod)/$limite, 0, PHP_ROUND_HALF_UP);
  if ($division<1) {
  	$division=1;
  }
	// echo	$division;
	// echo "---11111---";
  $cont = -$limite;
  for ($i = 0; $i < $division; $i++) {
    $cont = $cont+$limite;
      $contar = ($limite)-(strlen($descripcionprod)+2);
      $espacios='';
      for ($f = 0; $f < $contar; $f++) {
        $espacios .= ' ';
      }
	
	if($data['consumo'] == '0'){  
    if($i===0){
      $listItems .= "".substr($descripcionprod,$cont, $limite)." ".$espacios." ".$d['cantidad']."   ".number_format(($d['precio_original']),2)."  ".number_format(($d['descuento']),2)."  ".number_format(($d['cantidad'] * $d['precio_unitario']),2)."\n";
			$printer -> text("".$listItems."");
    }else{
      $listItems = "".substr($descripcionprod,$cont, $limite)."\n";
			$printer -> text($listItems);
    }
	}

  }

	$listItems = '';

			// $printer -> text("  ".$d['cantidad'].' '.utf8_decode($d['Producto']['pro_pre']).' | '.number_format(($d['precio_unitario']),2).'  '.number_format(($d['cantidad'] * $d['precio_unitario']),2)."\n");
			
			$total = ($d['cantidad'] * $d['precio_unitario']) + $total;


		}
	}
	if($data['consumo'] == '1'){
		$listItems .= "POR CONSUMO                    1   ".number_format(($data['total']),2)."  ".number_format($data['total'],2)."\n";
		$printer -> text("".$listItems."");
	}

	
	$printer -> text("-----------------------------------------------\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);

	$sbt = (($data['total'] + $data['comis_tar'] + $data['comis_del'] - $data['desc_monto']) / $new_igv);
	$igv = ($sbt * $data['igv']);

	$igvv = $new_igvrc;
	$new_subtotal = number_format(((($data['total']) +$data['comis_del']) / $igvv),2, '.', '');

	$printer -> text("SUB TOTAL:                            S/ ".$new_subtotal."\n");
	if($data['id_tped'] == 3){
	$printer -> text("COSTO DELIVERY:                       S/ ".number_format(($data['comis_del']),2)."\n");
	}

	if ($data['desc_monto']!=0) {
		$printer -> text("DESCUENTO:                            S/ ".number_format(($data['desc_monto']),2)."\n");
	}

	$printer -> text("IGV (".(($new_igv-1)*100)."%):                             S/ ".number_format(($new_subtotal * ($new_igv - 1)),2)."\n");

	if($data['comis_rc'] > 0){
		$datorc = ($data['rc'] > 0) ? ($data['rc']*100) : $data['Configuracion']['com_rc_val'];
		$printer -> text("REC. CONS: ".$datorc."%                          S/ ".number_format(($data['comis_rc']),2)."\n");
	}

	$printer -> text("IMPORTE A PAGAR:                      S/ ".number_format(($data['total'] + $data['comis_del'] - $data['desc_monto']),2)."\n");
	$printer -> text("\n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);

	$total_letras = $data['total'] + $data['comis_del'] - $data['desc_monto'];
	$printer -> text("SON: ".numtoletras(number_format(($total_letras),2))."\n");
	// $printer -> text("SON: ".numtoletras($data['total'] + $data['comis_del'] - $data['desc_monto'])."\n");
	$id_tipo_pago = $data["id_tipo_venta"];
	if($id_tipo_pago == 1){
		$tipo_pago = "CONTADO";
	}else if($id_tipo_pago == 2){
		$tipo_pago = "CREDITO";
	}
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("------------ FORMA DE PAGO :".$tipo_pago." ------------ \n");
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	if($data['propina'] != '0.00'){
		$printer -> text("PROPINA: S/".number_format($data['propina'],2)."\n");
	}
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
	if($id_tipo_pago == 2){
		$jsonCuotas =  $data["cuotas"];
		$cuotasDecodificadas = json_decode($jsonCuotas, true);
		foreach ($cuotasDecodificadas as $nombreCuota => $datosCuota) {
			$texto = '- '.$nombreCuota.' / Fecha: ' . str_replace('/', '-', $datosCuota["date"]) . ' / Monto: S/'. $datosCuota["monto"];
			$printer -> text($texto."\n");
		}
	}

if ($data['id_tdoc']=="1" || $data['id_tdoc']=="2") {
	//codigo qr //inicio
	$codesDir = "codes/";   

    if ($data['desc_td']=="BOLETA DE VENTA") {
    	$tipo_doc = '03';
    }else{
    	$tipo_doc = '01';
    }
    $total_qr = $data['total'] + $data['comis_del'] - $data['desc_monto'];

    if ($igv==null) {
    	$igv = 0;
    }
    $dataqr = "".$data['Empresa']['ruc']."|".$tipo_doc."|".$data['ser_doc']."|".$data['nro_doc']."|".number_format(($igv),2)."|".$total_qr."|".date('d-m-Y',strtotime($data['fec_ven']))."|".$data['Cliente']['tipo_cliente']."|".$data['Cliente']['dni']."".$data['Cliente']['ruc']."";

    $codeFile = $data['ser_doc'].'-'.$data['nro_doc'].'.png';

    QRcode::png($dataqr, $codesDir.$codeFile, "H", 4); 
	$qr = EscposImage::load("".$codesDir.$codeFile."", true);

  	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> bitImage($qr);
	// $printer -> feed();

	//codigo qr //final
	// $printer -> text("\n");
	// $printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("Autorizado mediante Resolucion\n");
	$printer -> text("Nro. 034-005-0005655/SUNAT\n");
	$printer -> text("Consulta CPE en:\n");
	// $printer -> text("demo.brainpos.pe/consulta\n");
	$printer -> text("\n");
	$printer -> text("Emitido por: www.brainpos.pe\n");
	if($data['Configuracion']['status_print_dedicatoria'] == 1){
		$printer -> text("\n".$data['Configuracion']['print_dedicatoria']."\n");
	}else{
		$printer -> text("!GRACIAS POR SU PREFERENCIA¡\n");
	}
	$printer -> text("===============================================\n");
	$printer -> text("\n");
}
	$printer -> cut();

}

if ($data['id_tped'] == 3){
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> setTextSize(1,1);
	$printer -> text("======================================\n");
	// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("TICKET\n");
	$printer -> text("PEDIDO NRO.: ".utf8_decode($data['TicketReparto']['nro_pedido'])."\n");
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
	$printer -> selectPrintMode();
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("\n");
	$printer -> text("FECHA: ".$fecha."\n");
	$printer -> text("HORA: ".$hora."\n");
	$printer -> text("_______________________________________________\n");
	$printer -> text("CANT     PRODUCTO                P.U   IMPORTE\n");
	// PRODUCTO                    CANT   P.U   IMPORTE
	$printer -> text("-----------------------------------------------\n");
	foreach($data['TicketRepartoDetalle'] as $d){
		if($d['cantidad'] > 0){


			$limite = 26;
			$listItems = '';
			$descripcionprod = utf8_decode($d['Producto']['pro_nom']).' '.utf8_decode($d['Producto']['pro_pre']);
		  	$division = round(strlen($descripcionprod)/$limite, 0, PHP_ROUND_HALF_UP);
		  	if ($division<1) {
			  	$division=1;
		  	}
		  	$cont = -$limite;
		  	for ($i = 0; $i < $division; $i++) {
				$cont = $cont+$limite;
			  	$contar = ($limite)-(strlen($descripcionprod)+2);
			  	$espacios='';
			  	for ($f = 0; $f < $contar; $f++) {
					$espacios .= ' ';
			  	}
 
				if($i===0){
				$listItems .= $d['cantidad']."   ".substr($descripcionprod,$cont, $limite)." ".$espacios."   ".number_format(($d['precio']),2)."  ".number_format(($d['cantidad'] * $d['precio']),2)."\n";
						$printer -> text("".$listItems."");
				}else{
				$listItems = "".substr($descripcionprod,$cont, $limite)."\n";
						$printer -> text($listItems);
				}
			}			
			$total = ($d['cantidad'] * $d['precio']) + $total;
		}
	}
	$printer -> text("----------------------------------------------\n");
	$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	$printer -> text("IMPORTE TOTAL: S/".number_format(($data['total']),2)."\n");
	$printer -> text("_______________________\n");
	$printer -> text("\n");
	// if($data['id_tpag'] == 1 || $data['id_tpag'] == 3){
		// $vuelto = $data['pago_efe_none'] - $data['pago_efe'];
		// $printer -> text("PAGO CON: S/".number_format($data['pago_efe_none'],2)."\n");
		// $printer -> text("VUELTO: S/".number_format($vuelto,2)."\n");
	// } else {
		// $printer -> text("PAGO CON: ".$data['desc_tp']."\n");
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
	if($data['Configuracion']['status_print_dedicatoria'] == 1){
		$printer -> text("\n".$data['Configuracion']['print_dedicatoria']."\n");
	}else{
		$printer -> text("Gracias por su preferencia\n");
	}
	$printer -> text("\n");
	$printer -> cut();
}
	$printer->pulse();

	$printer -> close();

} catch(Exception $e) {
	echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
}

?>
echo "<script>window.close();</script>";