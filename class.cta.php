<?php

/*
 *	This CTA class file is an API wrapper for interfacing with the Chicago Transit Authority's three web-based Application Programming
 *	Interfaces for Bus, Train ('El') and Service related information. 
 *
 *	Due to the evolution of the CTA's API interfaces, there are three distinct APIs, one for Bus, Train and Service information. As a
 *	result there are three distinct URI endpoints and two distinct API keys. 
 *
 *	This class brings all three APIs together into one object with related methods for accessing Bus, Train and Service information.
 *
 *	However, there is no requirement that both an Bus and Train API key are needed to use this class file. That is while this class 
 *	file helps to unify the three distinct APIs together for a PHP developer, this class can also be used to interface with just the 
 *	Bus, Train or Service API and nothing else.  
 *
 *	Thus the initiation of the object is done by simply providing a Bus and/or Train API Key.
 *
 *	More information about the CTA's APIs, including terms of use and including how to request API Keys, can be found at:
 *	http://www.transitchicago.com/developers/default.aspx
 * 
 *   @package CTA
 *   @author Paul Weinstein, <pdw@weinstein.org>
 *   @version 1.0
 *	@copyright Paul Weinstein 2011
 *
 *	Copyright (c) 2011 Paul Weinstein, <pdw@weinstein.org>
 *
 *	Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files 
 *	(the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, 
 *	publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do
 *	so, subject to the following conditions:
 *
 *	The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE 
 *	FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION 
 *	WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE. 
 *
 */

// Class is in session
class CTA {

	var $trainAPIURL = 'lapi.transitchicago.com/api/1.0/';
	var $busAPIURL = 'www.ctabustracker.com/bustime/api/v1/';
	var $statusAPIURL = 'www.transitchicago.com/api/1.0/';
	var $trainAPIKey = '';
	var $busAPIKey = '';
	var $timeout = '300';
	var $debug = false;

	/**
	 *	__construct function, the initiation of the object where developer's API key(s) are collected and stored for use
	 * 
	 *	@access	public
	 *	@param	string	$trainKey, A CTA Train API Key. Optional. (default: '')
	 *	@param	string	$busKey, A CTA Bus API Key. Optional. (default: '')
	 *	@param	bool		$debug, Enable/Disable debugging information. Optional. (default: false)
	 *	@return	void
	 *
	 */
	public function __construct( $trainKey = '', $busKey = '', $debug = false ) {
	
		$this->trainAPIKey = urlencode( $trainKey );
		$this->busAPIKey = urlencode( $busKey );	
		$this->debug = $debug;	
	
	}

	/**
	 *	statusRoutes function, uses the routes method of the customer alerts API to get status information about various CTA services
	 * 
	 *	@access	public
	 *	@param	array	$types, An array listing what types of service to request status information for. Required
	 *						bus, rail, station and systemwide are valid service types. Status of one or more service types can be requested
	 *	@param	array	$routeIDs, An array listing what routes to return status information for. Optional. (default: '')
	 *						Note for the unique identifier for routes for this method are defined by General Transit Feed Specification (GTFS)
	 *						See http://www.transitchicago.com/developers/gtfs.aspx for more information
	 *	@param	array	$stationIDs, An array provding a list of station ids to request status information about. Optional.  (default: '')
	 *						Note for the unique identifier for routes for this method are defined by General Transit Feed Specification (GTFS)
	 *						See http://www.transitchicago.com/developers/gtfs.aspx for more information
	 * 	@return	array	Returns an array of results from the XML result of the API call or false if an error is detected.
	 */
	public function statusRoutes( $types, $routeIDs = array(), $stationIDs = array() ) {

		$t = '';
		$r = '';
		$s = '';
		
		// $types, routeIDs and $stationIDs are csv in GET arguments so
		// validate, format and pass on
		if ( is_array( $types ) AND is_array( $routeIDs ) AND is_array( $stationIDs )){
		
			foreach( $types as $type ){
			
				$t .= urlencode( $type ). ',';
			
			}
			
			foreach( $routeIDs as $route ){
			
				$r .= urlencode( $route ). ',';
			
			}
			
			foreach( $stationIDs as $station ){
			
				$s .= urlencode( $station ). ',';
			
			}
		
		} else {
		
			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'Unknown parameter. Lists of Service Type, Route IDs and station IDs need to be provided in an array.';
		
			}
			
			return false;
		
		}
		
		$args = 'type=' .$t. '&routeid=' .$r. '&stationid=' .$s;
		$response = $this->httpRequest( $this->statusAPIURL. 'routes.aspx?', '', $args );
		return simplexml_load_string( $response );
		
	}
	
	/**
	 * 	statusAlerts function, uses the alerts method of the customer alerts API to get alerts about various CTA services
	 * 
	 * 	@access	public
	 * 	@param	string	$active, true/false string denotes if the request is for only active service alerts. Optional. (default: 'false')
	 * 	@param	string	$accessibility, true/false string dentoes if the request is for service alerts regarding accessibility. Optional.
	 *						(default: 'true')
	 * 	@param	string	$planned, true/false string denotes if the request is to include common, planned alerts. Optional. (default: 'true')
	 *	@param	array	$routeIDs, An array listing what routes to return status information for. Optional. (default: '')
	 *						Note for the unique identifier for routes for this method are defined by General Transit Feed Specification (GTFS)
	 *						See http://www.transitchicago.com/developers/gtfs.aspx for more information
	 *	@param	array	$stationIDs, An array provding a list of station ids to request status information about. Optional.  (default: '')
	 *						Note for the unique identifier for routes for this method are defined by General Transit Feed Specification (GTFS)
	 *						See http://www.transitchicago.com/developers/gtfs.aspx for more information
	 * 	@param	string	$byStartDate, An date formatted string that provides alerts starting prior to the date given. Optional.
	 *						Date Format is yyyyMMdd
	 * 	@param	int		$recentDays, An interger denoting the number of days before current date. Optional.
	 * 	@return	array	Returns an array of results from the XML result of the API call or false if an error is detected.
	 *
	 */
	public function statusAlerts( $active = 'false', $accessibility = 'true', $planned = 'true', $routeIDs = array(), $stationIDs = array(), $byStartDate = '', $recentDays = '' ){

		$r = '';
		$s = '';

		// RouteIDs and $stationIDs become csv strings for GET
		// validate, format and pass on

		if ( is_array( $routeIDs ) AND is_array( $stationIDs )){
			
			foreach( $routeIDs as $route ){
			
				$r .= urlencode( $route ). ',';
			
			}
			
			foreach( $stationIDs as $station ){
			
				$s .= urlencode( $station ). ',';
			
			}
		
		} else {

			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'Unknown parameter. Lists of Route IDs and station IDs need to be provided in an array.';
		
			}
					
			return false;
		
		}
	
		$args = 'activeonly=' .urlencode( $active ). '&accessibility=' . urlencode( $accessibility ). '&planned=' .urlencode( $planned ). '&routeid=' .$r. '&stationid=' .$s. '&bystartdate=' . urlencode( $byStartDate ). '&recentdays=' . urlencode( $recentdays );
		$response = $this->httpRequest( $this->statusAPIURL. 'alerts.aspx?', '', $args );
		return simplexml_load_string( $response );
	
	}
	
	/**
	 * 	train function, Using the Train API and Key, provides a list of predictions of train arrivles based on provided arguments
	 * 
	 *	@access	public
	 *	@param	string	$mapID, An identifier denoting what station to provide predictions for. Required if no $stopID is provided
	 *						Note for the unique identifier for stations are defined by General Transit Feed Specification (GTFS)
	 *						See http://www.transitchicago.com/developers/gtfs.aspx for more information
	 *	@param	string	$stopID, An identifier denoting what platform or platform side to provide predictions for. 
	 *						Required id no $mapID is provided 
	 *						Note for the unique identifier for stations are defined by General Transit Feed Specification (GTFS)
	 *						See http://www.transitchicago.com/developers/gtfs.aspx for more information
	 *	@param	string	$maxResults, The maximum number of results desired. Optional. (default: '')
	 *	@param	string	$routeCode, Allows for the specification of a single route (default: '')
	 * 	@return	array	Returns an array of results from the XML result of the API call.
	 *
	 */
	public function train( $mapID = '', $stopID = '', $maxResults = '', $routeCode = '' ) {

		// Validate our arguments and prep for GET Request
		$args = '&mapid=' . urlencode( $mapID ). '&stpid=' .urlencode( $stopID ). '&max=' .urlencode( $maxResults ). '&rt=' . urlencode( $routeCode ); 
		
		$response = $this->httpRequest( $this->trainAPIURL. 'ttarrivals.aspx?key=' .$this->trainAPIKey, $args );
	
		// Parse our XML into array and return 
		return simplexml_load_string( $response );
		
	}
	
	/**
	 * 	bustime function, get the local date and time as defined by the CTA Bus Tracker system/API
	 * 
	 * 	@access 	public
	 * 	@return	array	Returns an array of results from the XML result of the API call.
	 *
	 */
	public function bustime(){
	
		$response = $this->httpRequest( $this->busAPIURL. 'gettime?key=' .$this->busAPIKey );
		return simplexml_load_string( $response ); 
		
	}

	/**
	 * 	busGetVehicles function, that provides vehicle information via bus API
	 * 
	 *	@access	public
	 *	@param	array	$vehicleID, An array of vehicle ids. A maximum of 10 ids can be provided. Required if routeNo(s) are not provide
	 *						(default: array())
	 *	@param	array	$routeNo, An array of bus route number(s). A maimum of 10 routes can be provided. Required if vechicle id(s) 
	 *						are not provided. (default: array())
	 * 	@return	array	Returns an array of results from the XML result of the API call or false if an error is detected.
	 *
	 */
	public function busGetVehicles( $vehicleIDs = array(), $routeNos = array() ) {
	
		// Note arguemtns are either or, either vehicle id or route number
		if ((( !$vehicleIDs ) AND ( !$routeNos )) OR (( $vehicleIDs ) AND ( $routeNos ))) {
		
			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'Too many or too few arguments. Need to provide vehicle IDs or route number.';
		
			}
			
			return false;
		
		} else if ( is_array( $vehicleIDs ) AND sizeof( $vehicleIDs ) > 0 ) {
			echo 'here';
			$v = '';
			
			foreach( $vehicleIDs as $vehicle ){
			
				$v .= urlencode( $vehicle ). ',';
			
			}

			$args = '&vid=' .$v; 
			$reponse = $this->httpRequest( $this->busAPIURL. 'getvehicles?key=' .$this->busAPIKey, $args );
			return simplexml_load_string( $reponse );		
		
		} else if ( is_array( $routeNos ) AND sizeof( $routeNos ) > 0 ) {

			$r = '';
			
			foreach( $routeNos as $route ){
			
				$r .= urlencode( $route ). ',';
			
			}

			$args = '&rt=' .$r; 
			$reponse = $this->httpRequest( $this->busAPIURL. 'getvehicles?key=' .$this->busAPIKey, $args );
			return simplexml_load_string( $reponse );		
		
		} else {
		
			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'An unknown error occurred.';
		
			}
		
			return false;
		
		}
	
	}

	/**
	 *	busGetRoutes function, provides a list of routes currently in service
	 * 
	 *	@access 	public
	 * 	@return	array	Returns an array of results from the XML result of the API call.
	 *
	 */
	public function busGetRoutes(){
	
		$response = $this->httpRequest( $this->busAPIURL. 'getroutes?key=' .$this->busAPIKey );
		return simplexml_load_string( $response );
	
	}
	
	/**
	 *	busGetDirections function provides the bi-directional nature of the route. i.e, is the bus route East - West or North - South
	 * 
	 *	@access	public
	 *	@param	string	$routeNo is the route number to check. Required.
	 * 	@return	array	Returns an array of results from the XML result of the API call.
	 *
	 */
	public function busGetDirections( $routeNo ){
	
		$args = '&rt=' .urlencode( $routeNo );
	
		$response = $this->httpRequest( $this->busAPIURL. 'getdirections?key=' .$this->busAPIKey, $args );
		return simplexml_load_string( $response );
	
	}
 
	/**
	 * busGetStops function provides a list of stops for a specific route traveling in a single, specific direction.
	 * 
	 * 	@access public
	 * 	@param	string	$routeNo, is the route number be checked. Required
	 * 	@param	string	$direction is the direction of the route. Required.
	 * 	@return	array	Returns an array of results from the XML result of the API call.
	 *
	 */
	public function busGetStops( $routeNo, $direction ){
	
		$args = '&rt=' .urlencode( $routeNo ). '&dir=' .urlencode( $direction );
	
		$response = $this->httpRequest( $this->busAPIURL. 'getstops?key=' .$this->busAPIKey, $args );
		return simplexml_load_string( $response );
	
	}

	// Note: patternIDs is csv string
	/**
	 *	busGetPatterns function provides a set of geo-locations that can be connected to create a layout
	 * 
	 *	@access 	public
	 *	@param	array	$patternIDs is an array containing no more than 10 pattern ids. Required if route number is not provided.
	 *						(default: array())
	 *	@param	string	$routeNo a string of a single route number. Required if pattern id(s) are not provided. (default: '')
	 * 	@return	array	Returns an array of results from the XML result of the API call or false if an error is detected.
	 *
	 */
	public function busGetPatterns( $patternIDs = array(), $routeNo = '' ){
	
		// Note arguemtns are either or, either vehicle id or route number
		if ((( !$patternIDs ) AND ( !$routeNo )) OR (( $patternIDs ) AND ( $routeNo ))) {
		
			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'Too many or too few arguments. Need to provide pattern IDs or a route number.';
		
			}
			
			return false;
		
		} else if ( is_array( $patternIDs ) AND sizeof( $patternIDs ) > 0 ) {
		
			$p = '';
			
			foreach( $patternIDs as $pattern ){
			
				$p .= urlencode( $pattern ). ',';
			
			}

			$args = '&pid=' .$p; 
		
			$reponse = $this->httpRequest( $this->busAPIURL. 'getpatterns?key=' .$this->busAPIKey, $args );
			return simplexml_load_string( $reponse );		
		
		} else if ( $routeNo != '' ) {
	
			$args = '&rt=' .urlencode( $routeNo );
			$reponse = $this->httpRequest( $this->busAPIURL. 'getpatterns?key=' .$this->busAPIKey, $args );
			return simplexml_load_string( $reponse );		
		
		} else {
		
			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'An unknown error occurred.';
		
			}
		
			return false;
		
		}
	
	}

	/**
	 * busGetPredictions function for getting predictions of arrivals for one or more stops or one or more vehicles.  
	 * 
	 *	@access	public
	 *	@param	array	$stopIDs, Up to 10 stop IDs to get predictions for. Required if no vehicle id is provided. (default: array())
	 *	@param	array	$routeNos, Up to 10 route numbers for matching with stop id(s). Optional with $stopIDs. (default: array())
	 *	@param	array	$vehicleIDs, Up to 10 vechicle IDs. Required if no stop id(s) are provided. (default: array())
	 *	@param	string	$limit of prodictions to return. Optional (default: '')
	 * 	@return	array	Returns an array of results from the XML result of the API call or false if an error is detected.
	 *
	 */
	public function busGetPredictions( $stopIDs = array(), $routeNos = array(), $vehicleIDs = array(), $limit = '' ){
	
		// routeNos and $vehicleIDs can be comebined together, but not with vehicleIDs
		if ( sizeof( $stopIDs ) > 0 AND sizeof( $vehicleIDs ) > 0 ) {
	
			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'Conflicting arguments. Cannot provide both stop id(s) and vehicle id(s).';
		
			}
			
			return false;
			
		}
	
		if ( is_array( $stopIDs ) AND sizeof( $stopIDs ) > 0 AND is_array( $routeNos ) AND sizeof( $routeNos ) > 0 ) {
		
			$s = '';
			
			foreach( $stopIDs as $stop ){
			
				$s .= urlencode( $stop ). ',';
			
			}
			
			$r = '';
			
			foreach( $routeNos as $route ){
			
				$r .= urlencode( $route ). ',';
			
			}
		
			$args = '&stpid=' .$s. '&rt=' .$r. '$top=' . urlencode( $limit );
			$response = $this->httpRequest( $this->busAPIURL. 'getpredictions?key=' .$this->busAPIKey, $args );
		
		} else if ( $vehicleIDs ) {

			$v = '';
			
			foreach( $vehicleIDs as $vehicle ){
			
				$r .= urlencode( $vehicle ). ',';
			
			}
		
			$args = '&vid=' .$v. '&top=' .urlencode( $limit );
			$response = $this->httpRequest( $this->busAPIURL, 'getprediections?key=' .$this->busAPIKey, $args );
		
		} else {

			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'An unknown error occurred.';
		
			}
			
			return false;
			
		}
		
		return simplexml_load_string( $response );
	
	}
	
	/**
	 * busGetServiceBulletins function, provides bus related service information on for a list of routes or stops
	 * 
	 *	@access 	public
	 *	@param 	array 	$routeNos, an array of route numbers. Required if no stop id(s) are provided. (default: array())
	 *	@param 	array 	$stopIDs, an array of stop id(s). Required id no route numbers are provided. (default: array())
	 *	@param 	string 	$direction, is simplly a single route direction and is required with route number. (default: '')
	 * 	@return	array	Returns an array of results from the XML result of the API call or false if an error is detected.
	 *
	 */
	public function busGetServiceBulletins( $routeNos = array(), $stopIDs = array(), $direction = '' ){
	
		if ( is_array( $stopIDs ) AND sizeof( $stopIDs ) > 0 OR is_array( $routeNos ) AND sizeof( $routeNos ) > 0 ) {
		
			$s = '';
			
			foreach( $stopIDs as $stop ){
			
				$s .= urlencode( $stop ). ',';
			
			}
			
			$r = '';
			
			foreach( $routeNos as $route ){
			
				$r .= urlencode( $route ). ',';
			
			}
	
			$args = '&rt=' .$r. '&rtdir=' .urlencode( $direction ). '&stpid=' .$s;
			echo 'Here is: ' .$args;
			$response = $this->httpRequest( $this->busAPIURL. 'getservicebulletins?key=' .$this->busAPIKey, $args );
			return simplexml_load_string( $response ); 
		
		} else {

			// An error occured are we in debugging mode?
			if ( $this->debug ) {
		
				echo 'Too few arguments. Need to provide at least one stop id or one route id.';
		
			}
			
			return false;		
		
		}
	
	}

	/**
	 * 	httpRequest function, our function for using the cURL libraries and sending the HTTP request.
	 * 
	 *	@access	private
	 *	@param	string	$reqURL, The URI endpoing and API Key, if required, of API. Required.
	 *	@param	string	$args, The GET arguments, after the API Key, required for a sucessful request. Optional. (default: '')
	 *	@return	string	The results of the HTTP request
	 *
	 */
	private function httpRequest( $reqURL, $args = '' ) {


		// Configure cURL for our request
		$curl_handle = curl_init();

		// Set for GET
		curl_setopt( $curl_handle, CURLOPT_HTTPGET, 1 );
		
		$reqURL .= $args;
		$reqURL = 'http://' .$reqURL;
		//$reqURL = 'http://' .urlencode( $reqURL );
		
		// Provide our URL
		curl_setopt ( $curl_handle, CURLOPT_URL, $reqURL );	
		
		// Set add'l cURL headers
		curl_setopt( $curl_handle, CURLOPT_HEADER, 0 );
		curl_setopt( $curl_handle, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $curl_handle, CURLOPT_TIMEOUT, $this->timeout ); 
		curl_setopt( $curl_handle, CURLOPT_CONNECTTIMEOUT, 1 );

		// Debug?
		if ( $this->debug ) {
		
			echo 'This is request URL ' .$reqURL;
		
		}
		
		// And execute
		$response = curl_exec( $curl_handle );
		$code = curl_getinfo( $curl_handle, CURLINFO_HTTP_CODE );
		
		// Close up shop
		curl_close( $curl_handle );
					
		if (( $code != '200' ) OR ( $this->debug )) {
		
			
		
		}

		return $response;

	}
	
}
// Class Dismissed

?>
