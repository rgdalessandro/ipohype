#!/usr/bin/php
<?php
	// script to populate datebase with future IPOs as they are listed

	error_reporting( E_ERROR | E_PARSE ); // this line facilitates debugging this script

	include_once( 'simple_html_dom.php' ); // scraping script
	include_once( 'connection.php' );

	$symbols = getSymbols();

	$ipohtml = file_get_html('http://www.iposcoop.com/index.php?option=com_content&task=view&id=793&Itemid=99');
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
			$scrapedate = $row->find("td",7)->plaintext;
			$scrapedate = explode( "\n", $scrapedate );
			$ipodate = date('Y-m-d', strtotime($scrapedate[0]));		

			if ( !empty( $symbol ) && !in_array($symbol, $symbols) ) // only write to database if the IPOs are not already in database
			{
				$ipo["symbol"] = $symbol;
				$ipo["company"] = $company;
				$ipo["ipodate"] = $ipodate;
				$ipos[] = $ipo;
			}
		}
	}

	$con = new connection();
	foreach ($ipos as $ipo) // insert values into database
	{
		$sql = "INSERT INTO ipo (symbol, company, ipodate) values ('" . mysql_escape_string($ipo['symbol']) . "', '" . mysql_escape_string($ipo['company']) . "', '" . mysql_escape_string($ipo['ipodate']) . "')";
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