#!/usr/bin/php
<?php
	// script to populate datebase with the last 12 months of IPOs in case of fresh deployment

	error_reporting( E_ERROR | E_PARSE ); // this line facilitates debugging this script

	include_once( 'simple_html_dom.php' ); // scraping script
	include_once( 'connection.php' );

	$symbols = getSymbols();

	$ipohtml = file_get_html('http://www.iposcoop.com/index.php?option=com_content&task=view&id=1544&Itemid=147');
	$ipoRet = $ipohtml->find("table",14);

	$ipos = array();
	$counter = 0;

	foreach ( $ipoRet->find("tr") as $bigrow )
	{
		$counter++;

		if ( $counter == 1 ) continue; // this if statement with counter is used to skip the header row of the table

		$t = 0;

		foreach ( $bigrow->find("tr") as $row ) // loop through all IPOs in the table and scrape values
		{
			$ipo = array();

			$symbol = $row->find("td",1)->plaintext;
			$company = $row->find("td",0)->plaintext;
			$company = trim($company);
			$ipodate = $row->find("td",3)->plaintext;
			$ipodate = date('Y-m-d', strtotime($ipodate));		
			$ipoprice = $row->find("td",5)->plaintext;

			if ( !empty( $symbol ) && !in_array($symbol, $symbols) ) // only write to database if the IPOs are not already in database
			{
				$markethtml = file_get_contents('https://query.yahooapis.com/v1/public/yql?q=select%20*%20from%20yahoo.finance.historicaldata%20where%20symbol%20%3D%20%22' . $symbol . '%22%20and%20startDate%20%3D%20%22' . $ipodate . '%22%20and%20endDate%20%3D%20%22' . $ipodate . '%22&format=json&diagnostics=false&env=store%3A%2F%2Fdatatables.org%2Falltableswithkeys&callback=');
				$marketdata = json_decode($markethtml, true);

				$ipo["symbol"] = $symbol;
				$ipo["company"] = $company;
				$ipo["ipodate"] = $ipodate;
				$ipo["ipoprice"] = substr($ipoprice, 7);
				$ipo["openprice"] = $marketdata["query"]["results"]["quote"]["Open"];
				$ipo["closeprice"] = $marketdata["query"]["results"]["quote"]["Close"];
				$ipo["tweet"] = $tweetRet;
				$ipo["hype"] = $hypeRet;
				$ipos[] = $ipo;
			}
		}
	}

	$con = new connection();
	foreach ($ipos as $ipo) // insert values into database
	{
		$sql = "INSERT INTO ipo (symbol, company, ipodate, ipoprice, openprice, closeprice) values ('" . mysql_escape_string($ipo['symbol']) . "', '" . mysql_escape_string($ipo['company']) . "', '" . mysql_escape_string($ipo['ipodate']) . "', '" . mysql_escape_string($ipo['ipoprice']) . "', '" . mysql_escape_string($ipo['openprice']) . "', '" . mysql_escape_string($ipo['closeprice']) . "')";
		$con->execute($sql);
	}

	function getSymbols() { // get the full list of IPOs in the database
		$sql = "SELECT DISTINCT symbol FROM ipo";

		$con = new connection();
		$con->execute($sql);

		$symbols = array();

		while ( $con->fetch() )
		{
			$symbols[] = $con->rows["symbol"];
		}

		return $symbols;
	}

?>