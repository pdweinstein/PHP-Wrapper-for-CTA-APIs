<?php

	// An collection of simple examples of using class.cta.php
	// For more information about the CTA's APIs, including terms of use and including how to request API Keys, can be found at:
	// http://www.transitchicago.com/developers/default.aspx

	// Load our class file and create our object.
	include_once( 'class.cta.php' );
	$transit = new CTA( 'YOUR-TRAIN-API_KEY_HERE', 'YOUR-BUS-API-KEY-HERE', false );

	$status = array( 0 => 'bus', 1 => 'train' );
	echo 'Here is the array containing current status information for CTA buses and trains:<br/>';
	echo '<pre>';
	print_r( $transit->statusRoutes( $status ));
	echo '</pre>';

	echo 'Here is the array containing current alerts for CTA:<br/>';
	echo '<pre>';	
	print_r( $transit->statusAlerts() );
	echo '</pre>';

	echo 'Here is the array containing current prediction for El station 40360:<br/>';
	echo '<pre>';		
	print_r( $transit->train( '40360', '', '1' ));
	echo '</pre>';
	
	echo 'Here is the array containing current system for buses:<br/>';
	echo '<pre>';
	print_r( $transit->bustime());
	echo '</pre>';

	echo 'Here is the array containing vehicle information for buses on routes 3 and 81 :<br/>';
	echo '<pre>';
	print_r( $transit->busGetVehicles( array(), array( 0 => '3', 1 => '81' )));
	echo '</pre>';

	echo 'Here is the array containing the list of current routes:<br/>';
	echo '<pre>';
	print_r( $transit->busGetRoutes() );
	echo '</pre>';
	
	echo 'Here is the array containing the directions routes 81 travels:<br/>';
	echo '<pre>';
	print_r( $transit->busGetDirections( '81' ));
	echo '</pre>';

	echo 'Here is the array containing stops for route 81 in going East Bound:<br/>';
	echo '<pre>';
	print_r( $transit->busGetStops( '81', 'East Bound' ));
	echo '</pre>';

	echo 'Here is the array containing a set of geo-position patterns for route 81:<br/>';
	echo '<pre>';
	print_r( $transit->busGetPatterns( '', '81' ));
	echo '</pre>';

	echo 'Here is the array containing a set of predictions of arrivals for route 20 at stop 456:<br/>';
	echo '<pre>';
	print_r( $transit->busGetPredictions( array( 0 => '456' ), array( 0 => '20' )));
	echo '</pre>';

	echo 'Here is the array containing a set of route service announcements for route 11 in North Bound:<br/>';
	echo '<pre>';
	print_r( $transit->busGetServiceBulletins( array( 0 => '11' ), array( ), 'North Bound' ));
	echo '</pre>';

?>
