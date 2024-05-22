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
impresion en diferentes ticketeras de un area de produccion 
*/
$activar = false;
if($activar)
{
	$printer = 'CAJAP1';  // AREA DONDE SE TRABAJARA 

	// piso  => ticketera
	// BAR 		= 192.168.1.10
	// BARDOS 	= 192.168.1.14

	// BAR PISO 1  -BARONE
	// BAR PISO 2 - BARDOS
	$array = [
		"P1" => "BAR",
		"P2" => "BARDOS",
		"P3" => "BARDOS"
	];

	if ( $data['nombre_imp'] == $printer ) {

	
		if(isset($array[$data['ip_pc_set']])){

			$printer_url = "smb://".$data['nombre_pc']."/".$array[$data['ip_pc_set']];

		}else{
			
			$printer_url = "smb://".$data['nombre_pc']."/".$data['nombre_imp'];

		}
	} else {
		
		$printer_url = "smb://".$data['nombre_pc']."/".$data['nombre_imp'];
	}

	// $connector = new WindowsPrintConnector($printer_url);
	
	$printer_ip = $data['nombre_imp'];                    // IMPRIMIR EN TABLET
	$connector = new NetworkPrintConnector($printer_ip);  // IMPRIMIR EN TABLET

} else {

	// $connector = new WindowsPrintConnector("smb://".$data['nombre_pc']."/".$data['nombre_imp']);

	$printer_ip = $data['nombre_imp'];                    // IMPRIMIR EN TABLET
	$connector = new NetworkPrintConnector($printer_ip);  // IMPRIMIR EN TABLET
}

$printer = new Printer($connector);

try {
	// espacio en blanco parte superior
	// $printer -> text("\n\n\n\n");
  	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	
	if($data['codigo_anulacion'] == 1){
		// $printer -> setTextSize(1,1);
		$printer -> text("***************\n");
		$printer -> text("ANULADO\n");
		$printer -> text("***************\n");
		$printer -> selectPrintMode();
	}
		
	if($data['pedido_tipo'] == 1){
		$printer -> text("======================================\n");
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		// $printer -> text("MESA\n");
		$printer -> text("MESA - AREA: ".$data['nombre_area']."\n");
	}elseif($data['pedido_tipo'] == 2){
		$printer -> text("======================================\n");
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		// $printer -> text("PARA LLEVAR\n");
		$printer -> text("LLEVAR - AREA: ".$data['nombre_area']."\n");
	}elseif($data['pedido_tipo'] == 3){
		$printer -> text("======================================\n");
		$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		// $printer -> text("DELIVERY\n");
		$printer -> text("DELIVERY - AREA: ".$data['nombre_area']."\n");
	}
	
	if($data['codigo_anulacion'] <> 1){
		$printer -> text("Comanda #".$data['correlativo_imp']."\n");
		// $printer -> text("Area : ".$data['nombre_area']."\n");
		$printer -> selectPrintMode();
		$printer -> text("======================================\n");
	}
	
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	//$printer->selectPrintMode(Printer::MODE_FONT_A);
	$printer->selectPrintMode();
	$printer -> text("".$fecha." - ".$hora."\n");
	// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
	if($data['pedido_tipo'] == 1){
		$printer -> text($data['pedido_numero']." - ".$data['pedido_cliente']."\n");
		// $printer->selectPrintMode(Printer::MODE_FONT_A);
		$printer->selectPrintMode();
		$printer -> text("MOZO:".$data['pedido_mozo']."\n");
	}elseif($data['pedido_tipo'] == 2){
		// $printer->selectPrintMode(Printer::MODE_FONT_A);
		$printer->selectPrintMode();
		$printer -> text("PARA LLEVAR #".$data['pedido_numero']." - CLIENTE:".$data['pedido_cliente']."\n");
	}elseif($data['pedido_tipo'] == 3){
		$printer -> text("DELIVERY #".$data['pedido_numero']." - CLIENTE:".$data['pedido_cliente']."\n");
		if(isset($data['pedido_telefono'])){
		$printer -> text("TELEFONO #".$data['pedido_telefono']."\n");
		}
		if(isset($data['pedido_direccion'])){
		$printer -> text("DIRECCION #".$data['pedido_direccion']."\n");
		}
	}
	// $printer -> setJustification(Printer::JUSTIFY_CENTER);
	// $printer->selectPrintMode(Printer::MODE_FONT_A);
	// $printer -> text("________________________\n");
	// $printer -> text("\n");
	$printer->selectPrintMode(Printer::MODE_EMPHASIZED);
	$printer -> setJustification(Printer::JUSTIFY_LEFT);
	$printer -> text("------------------------------\n");
	$printer -> text("CANT.         DESCRIPCION\n");
	$printer -> selectPrintMode();
	$printer -> text("------------------------------\n");

	if(isset($data['combo_presentacion'])){
		$printer -> text($data['combo_presentacion']."\n");
	}
	foreach ($data['items'] as $value) {
		$categoria = explode(" ", $value['categoria']);
		$precio_pro = "s/".$value['precio'];
		$printer -> setEmphasis(true);
		// IMPRIME CATEGORIA, PRODUCTO, PRESENTACION Y PRECIO.
		//$printer -> text($value['cantidad']." ".$categoria[0]." ".$value['producto']." | ".$value['presentacion']."\n");  // Imprime la primera palabra de la categoria.
		// $printer -> text($value['cantidad']." ".$value['categoria']." | ".$value['producto']." | ".$value['presentacion']."\n");

		// IMPRIME PRODUCTO, PRESENTACION Y PRECIO.
		// $printer -> text($value['cantidad']." ".$value['producto']." | ".$value['presentacion']." - ".$precio_pro."\n");
		// SOLO IMPRIME PRODUCTO Y PRESENTACION
		   $printer -> text($value['cantidad']." ".$value['producto']." | ".$value['presentacion']."\n");
		// SOLO IMPRIME PRESENTACION
		// $printer -> text($value['cantidad']." | ".$value['presentacion']."\n");
		if($value['comentario']) $printer -> text(" *".$value['comentario']."\n");
		if ($value['toppings'] == 1 && isset($value['toppings_descripcion']) && !empty($value['toppings_descripcion'])) {				
			$printer -> text(" TOPPINGS - EXTRAS\n");
			$descripcion_array = explode("=", $value['toppings_descripcion']);
			$precio_array = explode("=", $value['toppings_precio']);
			$printer->selectPrintMode();
			for ($i = 0; $i < count($descripcion_array); $i++) {
				$descripcion = $descripcion_array[$i];
				$precio = $precio_array[$i];
				$printer -> text("  -- (".$descripcion.")\n");
			}
		}
	}
	$printer -> setJustification(Printer::JUSTIFY_CENTER);
	$printer -> text("________________________\n");
	$printer -> text("************************\n");
	$printer -> text("\n");
	$printer -> cut();
	$printer -> close();

} catch(Exception $e) {
	echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
}

if($data['nombre_imp_2'] != ""){
	
	// $connector = new WindowsPrintConnector("smb://".$data['nombre_pc']."/".$data['nombre_imp_2']);
	// $printer = new Printer($connector);

	$printer_ip = $data['nombre_imp_2'];                    // IMPRIMIR EN TABLET
	$connector = new NetworkPrintConnector($printer_ip);    // IMPRIMIR EN TABLET
	$printer = new Printer($connector);                     // IMPRIMIR EN TABLET

	try {
		// espacio en blanco parte superior
		// $printer -> text("\n\n\n\n");
		$printer -> setJustification(Printer::JUSTIFY_CENTER);
		
		if($data['codigo_anulacion'] == 1){
			// $printer -> setTextSize(1,1);
			$printer -> text("***************\n");
			$printer -> text("ANULADO\n");
			$printer -> text("***************\n");
			$printer -> selectPrintMode();
		}
			
		if($data['pedido_tipo'] == 1){
			$printer -> text("======================================\n");
			$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
			// $printer -> text("MESA\n");
			$printer -> text("MESA - AREA: ".$data['nombre_area']."\n");
		}elseif($data['pedido_tipo'] == 2){
			$printer -> text("======================================\n");
			$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
			// $printer -> text("PARA LLEVAR\n");
			$printer -> text("LLEVAR - AREA: ".$data['nombre_area']."\n");
		}elseif($data['pedido_tipo'] == 3){
			$printer -> text("======================================\n");
			$printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
			// $printer -> text("DELIVERY\n");
			$printer -> text("DELIVERY - AREA: ".$data['nombre_area']."\n");
		}
		
		if($data['codigo_anulacion'] <> 1){
			$printer -> text("Comanda #".$data['correlativo_imp']."\n");
			// $printer -> text("Area : ".$data['nombre_area']."\n");
			$printer -> selectPrintMode();
			$printer -> text("======================================\n");
		}
		
		$printer -> setJustification(Printer::JUSTIFY_LEFT);
		// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		//$printer->selectPrintMode(Printer::MODE_FONT_A);
		$printer->selectPrintMode();
		$printer -> text("".$fecha." - ".$hora."\n");
		// $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
		if($data['pedido_tipo'] == 1){
			$printer -> text($data['pedido_numero']." - ".$data['pedido_cliente']."\n");
			// $printer->selectPrintMode(Printer::MODE_FONT_A);
			$printer->selectPrintMode();
			$printer -> text("MOZO:".$data['pedido_mozo']."\n");
		}elseif($data['pedido_tipo'] == 2){
			// $printer->selectPrintMode(Printer::MODE_FONT_A);
			$printer->selectPrintMode();
			$printer -> text("PARA LLEVAR #".$data['pedido_numero']." - CLIENTE:".$data['pedido_cliente']."\n");
		}elseif($data['pedido_tipo'] == 3){
			$printer -> text("DELIVERY #".$data['pedido_numero']." - CLIENTE:".$data['pedido_cliente']."\n");
			if(isset($data['pedido_telefono'])){
			$printer -> text("TELEFONO #".$data['pedido_telefono']."\n");
			}
			if(isset($data['pedido_direccion'])){
			$printer -> text("DIRECCION #".$data['pedido_direccion']."\n");
			}
		}
		// $printer -> setJustification(Printer::JUSTIFY_CENTER);
		// $printer->selectPrintMode(Printer::MODE_FONT_A);
		// $printer -> text("________________________\n");
		// $printer -> text("\n");
		$printer->selectPrintMode(Printer::MODE_EMPHASIZED);
		$printer -> setJustification(Printer::JUSTIFY_LEFT);
		$printer -> text("------------------------------\n");
		$printer -> text("CANT.         DESCRIPCION\n");
		$printer -> selectPrintMode();
		$printer -> text("------------------------------\n");

		if(isset($data['combo_presentacion'])){
			$printer -> text($data['combo_presentacion']."\n");
		}
		foreach ($data['items'] as $value) {
			$categoria = explode(" ", $value['categoria']);
			$precio_pro = "s/".$value['precio'];
			$printer -> setEmphasis(true);
			// IMPRIME CATEGORIA, PRODUCTO, PRESENTACION Y PRECIO.
			//$printer -> text($value['cantidad']." ".$categoria[0]." ".$value['producto']." | ".$value['presentacion']."\n");  // Imprime la primera palabra de la categoria.
			// $printer -> text($value['cantidad']." ".$value['categoria']." | ".$value['producto']." | ".$value['presentacion']."\n");
			// IMPRIME PRODUCTO, PRESENTACION Y PRECIO.
			// $printer -> text($value['cantidad']." ".$value['producto']." | ".$value['presentacion']." - ".$precio_pro."\n");
			// SOLO IMPRIME PRODUCTO Y PRESENTACION
			   $printer -> text($value['cantidad']." ".$value['producto']." | ".$value['presentacion']."\n");
			// SOLO IMPRIME PRESENTACION
			// $printer -> text($value['cantidad']." | ".$value['presentacion']."\n");
			if($value['comentario']) $printer -> text(" *".$value['comentario']."\n");
			if ($value['toppings'] == 1 && isset($value['toppings_descripcion']) && !empty($value['toppings_descripcion'])) {				
				$printer -> text(" TOPPINGS - EXTRAS\n");
				$descripcion_array = explode("=", $value['toppings_descripcion']);
				$precio_array = explode("=", $value['toppings_precio']);
				$printer->selectPrintMode();
				for ($i = 0; $i < count($descripcion_array); $i++) {
					$descripcion = $descripcion_array[$i];
					$precio = $precio_array[$i];
					$printer -> text("  -- (".$descripcion.")\n");
				}
			}
		}
		$printer -> setJustification(Printer::JUSTIFY_CENTER);
		$printer -> text("________________________\n");
		$printer -> text("************************\n");
		$printer -> text("\n");
		$printer -> cut();
		$printer -> close();

	} catch(Exception $e) {
		echo "No se pudo imprimir en esta impresora " . $e -> getMessage() . "\n";
	}
}

?>
echo "<script lenguaje="JavaScript">window.close();</script>";

