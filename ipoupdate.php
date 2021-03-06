#!/usr/bin/php
<?php
	// script to update IPOs with market open/close prices

	error_reporting( E_ERROR | E_PARSE ); // this line facilitates debugging this script

	include_once( 'simple_html_dom.php' ); // scraping script
	include_once( 'connection.php' );

	$symbols = getSymbols();
	$ipos = array();

	$con = new connection();

	foreach ( $symbols as $item )
	{
		$symbol = $item["symbol"];
		$ipodate = $item["ipodate"];

		$markethtml = file_get_contents('https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.historicaldata%20where%20symbol%20%3D%20%22' . $symbol . '%22%20and%20startDate%20%3D%20%22' . $ipodate . '%22%20and%20endDate%20%3D%20%22' . $ipodate . '%22&format=json&diagnostics=false&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=');
		$marketdata = json_decode($markethtml, true);

		$openprice = $marketdata["query"]["results"]["quote"]["Open"];
		$closeprice = $marketdata["query"]["results"]["quote"]["Close"];

		$sql = "UPDATE ipo SET openprice = '" . mysql_escape_string($openprice) . "', closeprice = '" . mysql_escape_string($closeprice) . "' WHERE symbol = '" . $symbol . "'";
		$con->execute($sql);
	}

	function getSymbols() { // get a list of all IPOs that don't have first day open/close price set 
		$sql = "SELECT DISTINCT symbol, ipodate FROM ipo WHERE closeprice = 0 OR openprice = 0";

		$con = new connection();
		$con->execute($sql);

		$symbols = array();

		while ( $con->fetch() )
		{
			$symbols[] = array("symbol" => $con->rows["symbol"], "ipodate" => $con->rows["ipodate"]);
		}

		return $symbols;
	}
?>