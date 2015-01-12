<?php

	include_once("connection.php");

	switch ($_GET['function']) // switch statement to determine function to run
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

	function dateToCheck()	// function to retrieve from SQL all IPOs on a given date
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

	function monthToCheck() // function to retrieve from SQL all IPOs in a given month
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

	function dateExists()	// function to check if any IPOs exist in the database for a given date
	{
		$date = date('Y-m-d', strtotime($_GET['date']));

		$sql = "SELECT COUNT(*) AS count FROM ipodate WHERE ipodate = '" . mysql_escape_string($date) . "'";

		$con = new connection();
		$con->execute($sql);

		die($con->fetch());
	}
?>