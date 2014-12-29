<?php

	include_once("connection.php");

	switch ($_GET['function'])
	{
		case "dateToCheck":
			dateToCheck();
			break;
		case "addIPO":
			addIPO();
			break;
		case "dateExists":
			dateExists();
			break;
	}

	function dateToCheck()
	{
		$date = date('Y-m-d', strtotime($_GET['date']));

		$sql = "SELECT * FROM ipo WHERE ipodate = '" . mysql_real_escape_string($date) . "'";

		$con = new connection();
		$con->execute($sql);

		$ipos = array();

		while ($con->fetch())
		{
			$ipos[] = $con->rows;
		}

		$json = json_encode(array("results" => $ipos, "status" => "OK"));

		die($json);
	}

	function addIPO()
	{
		$ipos = array( 
			array(
				"symbol" => "APPL", 
				"init_price" => 15.20, 
				"final_price" => 0.00
				) 
			);

		$con = new connection();
		foreach ($ipos as $ipo)
		{
			$sql = "INSERT INTO ipo (symbol, openprice, closeprice) values ('" . mysql_real_escape_string($ipo['symbol']) . "', '" . mysql_real_escape_string($ipo['init_price']) . "', '" . mysql_real_escape_string($ipo['final_price']) . "')";
			$con->execute($sql);
			echo "done bitch";
		}
	}

	function dateExists()
	{
		$date = date('Y-m-d', strtotime($_GET['date']));

		$sql = "SELECT COUNT(*) AS count FROM ipodate WHERE ipodate = '" . mysql_real_escape_string($date) . "'";

		$con = new connection();
		$con->execute($sql);

		die($con->fetch());
	}
?>