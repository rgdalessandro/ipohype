#!/usr/bin/php
<?php
	// script to scrape for tweets and topsy.com sentiment value

	error_reporting( E_ERROR | E_PARSE ); // this line facilitates debugging this script

	include_once( 'simple_html_dom.php' ); // scraping script
	include_once( 'connection.php' );

	$symbols = symbolsNoScore();
	$con = new connection();

	foreach ( $symbols as $symbol )
	{
		$backhypehtml = file_get_contents('http://otter.topsy.com/sentiment.js?q=%24' . $symbol["symbol"] . '&mintime=' . ($symbol["ipodate"] - 691200) . '&maxtime=' . ($symbol["ipodate"] - 86400) . '&apikey=09C43A9B270A470B8EB8F2946A9369F3');		
		$backhypedata = json_decode($backhypehtml, true);

		$tweet = $backhypedata["response"]["results"][0]["stats"]["total"]["mentions"];
		$hype = floor( $backhypedata["response"]["results"][0]["stats"]["average"]["sentiment_score"] );

		$sql = "UPDATE ipo SET tweet = '" . mysql_escape_string($tweet) . "', hype = '" . mysql_escape_string($hype) . "' WHERE symbol = '" . $symbol['symbol'] . "'";
		$con->execute( $sql );

		sleep ( 1 );
	}

	function symbolsNoScore() { // get a list of IPOs and their dates from the database that are missing tweets/hype or are in the future
		$sql = "SELECT DISTINCT symbol, ipodate FROM ipo WHERE hype IS NULL OR ipodate >= CURDATE()";

		$con = new connection();
		$con->execute($sql);

		$symbols = array();

		while ( $con->fetch() )
		{
			$symbol = array();
			$symbol["symbol"] = $con->rows["symbol"];
			$symbol["ipodate"] = strtotime( $con->rows["ipodate"] );

			$symbols[] = $symbol;
		}

		return $symbols;
	}
?>