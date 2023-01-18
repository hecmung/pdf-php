<?php
require('fpdf.php');

class PDF extends FPDF
{
    // Header
    function Header()
    {
        // Logo
        $this->Image('Logo_IPN.png', 11, 5, 25);
        $this->Image('EscudoESCOM.png', 165, 7, 35);
        // Arial bold 15
        $this->SetFont('Arial', 'B', 15);
        // Mueve la celda a la derecha (30)
        $this->Cell(30);
        // Título centrado
        $text = utf8_decode("Comprobante de invitación");
        $this->Cell(125, 10, $text, 0, 0, 'C');
        // Salto de línea
        $this->Ln(40);
    }

    // Pie de página
    function Footer()
    {
        // Margen inferior de 2.5 cm 
        $this->SetY(-25);
        //Fecha con arial cursiva de tamaño 8 alineado a la izquierda
        $this->SetFont('Arial','I',8);
        $fechaActual = date('d/m/y');
        $text = utf8_decode("Fecha de generación de PDF: " . $fechaActual);
        $this->Cell(0,10,$text,0,1,'L');
        // Número de página centrado
        $text = utf8_decode("Página ");
        $this->Cell(0, 10, $text . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

//Conexión a la bd de prueba
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$db = "test";

$conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);

/*  Query, las tablas están de la siguiente forma:
    La tabla "user" contiene: idUser, statusGuet (es un booleano que confirma si el usuario tiene o no invitado), name & email
    La tabla "guest" contiene: idGuest, name, email & idUser (Llave foránea que relaciona al invitado con el usuario, este existe siempre y cuando el "statusGuest" de la tabla "user" haya sido un valor true)
    En el query se usa un LEFT JOIN porque así siempre traemos al usuario sin importar que este tenga o no invitado
    La busqueda es por el email, se espera que este se extraida de la sesión que haya sido iniciado

*/
$sql = "SELECT U.idUser, U.statusGuest, U.name AS userName, U.email AS userEmail, G.idGuest ,G.name AS guestName, G.email AS guestEmail FROM User U LEFT JOIN Guest G ON G.idUser = U.idUser WHERE U.email = 'test@test.com'";//WHERE U.email = 'prueba@test.com'
//La consulta se guarda quí
$datos = mysqli_query($conn,$sql);

$conn->close();
// Declararión de la clase para generar el pdf con un tamaño de 21x26.5 cm
$pdf = new PDF('P', 'mm',array(210,265));
$pdf->AliasNbPages();
$pdf->AddPage();
//Comenzamos a imprimir los datos del query en la hoja del pdf
while($row = mysqli_fetch_array($datos)){
    //$pdf->Cell(0,10,$row['statusGuest'],0,1);
    //Datos estáticos
    $pdf->SetFont('Arial','B',16);
    $text = utf8_decode("¡Felicidades!");
    $pdf->Cell(0,10,$text,0,1,'C');
    $pdf->SetFont('Arial','',16);
    $text = utf8_decode("Fuiste galardonado en el proceso institucional");
    $pdf->Cell(0,10,$text,0,1,'C');
    $text = utf8_decode("de \"Distinciones al Mérito Politécnico.\"");
    $pdf->Cell(0,10,$text,0,1,'C');
    $pdf->SetFont('Arial','I',10);
    $text = utf8_decode("*Descarga este comprobante siempre que tus datos sean correctos*");
    $pdf->Cell(0,10,$text,0,1,'C');
    //Formato con invitado
    if($row['statusGuest']){
        $pdf->SetFont('Arial','U',14);
        $pdf->Ln(15);
        $pdf->Cell(100,10,"Datos del galardonado",0,1,'L');
        $pdf->SetFont('Arial','',14);
        $pdf->Cell(100,10,"    Id: " . $row['idUser'],0,1,'L');
        $text = utf8_decode($row['userName']);
        $pdf->Cell(100,10,"    Nombre: " . $text,0,1);
        $text = utf8_decode("    Correo electrónico: ");
        $pdf->Cell(100,10,$text . $row['userEmail'],0,1);
        $pdf->Ln(15);
        $pdf->SetFont('Arial','U',14);
        $pdf->Cell(100,10,"Datos del invitado",0,1,'L');
        $pdf->SetFont('Arial','',14);
        $pdf->Cell(0,10,"    Id: " . $row['idGuest'],0,1);
        $text = utf8_decode($row['guestName']);
        $pdf->Cell(0,10,"    Nombre: " . $text,0,1);
        $text = utf8_decode("    Correo electrónico: ");
        $pdf->Cell(0,10,$text . $row['guestEmail'],0,1);
    } 
    //Formato sin invitado
    else {
        $pdf->SetFont('Arial','U',14);
        $pdf->Ln(15);
        $pdf->Cell(100,10,"Datos del galardonado",0,1,'L');
        $pdf->SetFont('Arial','',14);
        $pdf->Cell(100,10,"    Id: " . $row['idUser'],0,1,'L');
        $text = utf8_decode($row['userName']);
        $pdf->Cell(100,10,"    Nombre: " . $text,0,1);
        $text = utf8_decode("    Correo electrónico: ");
        $pdf->Cell(100,10,$text . $row['userEmail'],0,1);
        $pdf->Ln(15);
        $pdf->SetFont('Arial','u',14);
        $pdf->Cell(100,10,"Datos del invitado",0,1,'L');
        $pdf->SetFont('Arial','I',10);
        $pdf->Cell(100,10,"*No cuenta con invitado*",0,1,'L');
    }
}
$pdf->Output();
?>