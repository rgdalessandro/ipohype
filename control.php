<?php

	include_once("connection.php");

	switch ($_GET['function'])
	{
		case "dateToCheck":
			dateToCheck();
			break;
		case "monthToCheck":
			monthToCheck();
			break;	
		case "dateExists":
			dateExists();
			break;
	}

	function dateToCheck()
	{
		$date = date('Y-m-d', strtotime($_GET['date']));

		$sql = "SELECT * FROM ipo WHERE ipodate = '" . mysql_escape_string($date) . "' ORDER BY hype DESC";

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

	function monthToCheck()
	{
		$month = $_GET['month'];
		$year = $_GET['year'];

		$sql = "SELECT * FROM ipo WHERE month(ipodate)='" . mysql_escape_string($month) . "' AND year(ipodate)='" . mysql_escape_string($year) . "' ORDER BY ipodate";

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

	function dateExists()
	{
		$date = date('Y-m-d', strtotime($_GET['date']));

		$sql = "SELECT COUNT(*) AS count FROM ipodate WHERE ipodate = '" . mysql_escape_string($date) . "'";

		$con = new connection();
		$con->execute($sql);

		die($con->fetch());
	}
?>