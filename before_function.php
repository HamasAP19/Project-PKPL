<?php
require "config.php";
function Search($kunci, $table, $filter = null)
{
	if ($filter != null)
		$filter = "AND ZakatID = '$filter'";
	$query = "SELECT * FROM $table INNER JOIN penzakat USING(PenzakatID)
    INNER JOIN Zakat USING(ZakatID)
    INNER JOIN metode_bayar USING(PayID) WHERE Nama_lngkp LIKE '%$kunci%' $filter";
	$result = $GLOBALS['connect']->query($query);
	return $result;
}

function tanggal($waktu)
{
	return date("d F Y", strtotime($waktu));
}

function getProduk()
{
	$query = "SELECT * FROM zakat";
	$result = $GLOBALS['connect']->query($query);

	while ($data = mysqli_fetch_array($result)) {
		echo "<option value=$data[Ukuran]>$data[Nama_Zakat] @$data[Ukuran]</option>";
	}
}
function change($value)
{
	if ($value < 1000) {
		return $value . " Kg";
	} else {
		return "Rp. " . number_format($value, 0, ',', '.');
	}
}
function getApiData($format)
{
	if ($format == "JSON") {
		$url = "http://localhost/Pendaftaran-Zakat-Fitrah-Masjid%20Al-Muhajirin/api/getDataJSON.php";
		$request = curl_init($url);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($request);
		$result = json_decode($response, true);
	} else {
		$url = "http://localhost/Pendaftaran-Zakat-Fitrah-Masjid%20Al-Muhajirin/api/getDataXML.php";
		$result = simplexml_load_file($url);
	}
	return $result;
}

function getzakat()
{
	$query = "SELECT COUNT(*) AS Penzakat, (SELECT SUM(Jum_Bayar) FROM Daftar INNER JOIN zakat USING(ZakatID) WHERE ZakatID = 'ZB') AS ZB,
	(SELECT SUM(Jum_Bayar) FROM Daftar INNER JOIN zakat USING(ZakatID) WHERE ZakatID = 'ZU') AS ZU,
	(SELECT COUNT(*) FROM Confirm) AS Pendaftar  
	FROM Daftar INNER JOIN penzakat USING(PenzakatID)
    INNER JOIN zakat USING(ZakatID)
    INNER JOIN metode_bayar USING(PayID) ORDER BY Tanggal";
	$result = $GLOBALS['connect']->query($query);
	$data = mysqli_fetch_assoc($result);
	return $data;
}
function CheckNull($value)
{
	if ($value === NULL) {
		return 0;
	} else {
		return $value;
	}
}

function GetDataMYSQL($query)
{
	$result = $GLOBALS['connect']->query($query);
	$data = mysqli_fetch_assoc($result);
	return $data;
}

function validationLogin($data)
{
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

class Penyimpanan
{
	private $Nama_Lengkap;
	private $No_HP;
	private $Jumlah_jiwa;
	private $Methode_Pay;
	private $Jenis_Zakat;
	private $Total_Bayar;
	private $Result;
	private $DB;

	function __construct($nama, $sapaan, $id_no, $no_hp, $jumlah_jiwa, $metode_bayar, $jenis_zakat)
	{
		$this->Nama_Lengkap = $sapaan . $nama;
		$this->No_HP = $id_no . $no_hp;
		$this->Jumlah_jiwa = $jumlah_jiwa;
		$this->Methode_Pay = $metode_bayar;
		$this->Jenis_Zakat = $jenis_zakat;
		$this->Total_Bayar = $jumlah_jiwa * $jenis_zakat;
	}
	function SetDB($db)
	{
		$this->DB = $db;
	}
	function InsertPerson()
	{
		$Query = "INSERT INTO penzakat VALUES(NULL, '$this->Nama_Lengkap', $this->No_HP, $this->Jumlah_jiwa)";
		$this->Result = $this->DB->query($Query);
	}
	function InsertPendaftar()
	{
		$Query = "INSERT INTO Confirm VALUES (NULL, (SELECT PenzakatID FROM penzakat WHERE Nama_Lngkp = '$this->Nama_Lengkap' LIMIT 1), 
		(SELECT ZakatID FROM zakat WHERE Ukuran = '$this->Jenis_Zakat' LIMIT 1 ), 
		'$this->Methode_Pay', $this->Total_Bayar , CURRENT_TIMESTAMP())";
		$this->Result = $this->DB->query($Query);
	}
	function getResult()
	{
		return $this->Result;
	}
	function CekData()
	{
		echo $this->Nama_Lengkap;
		echo " ";
		echo $this->No_HP;
		echo " ";
		echo $this->Jumlah_jiwa;
		echo " ";
		echo $this->Methode_Pay;
		echo " ";
		echo $this->Jenis_Zakat;
		echo " ";
		echo $this->Total_Bayar;
		echo " ";
	}
}

function editData($jumAnggota, $jenis)
{
	$query = "SELECT * FROM Zakat WHERE ZakatID = '$jenis'";
	$result = $GLOBALS['connect']->query($query);
	$data = mysqli_fetch_assoc($result);
	$hasil = $jumAnggota * $data['Ukuran'];
	return $hasil;
}
