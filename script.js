$( document ).ready( function() {
	var today = new Date();									// Default to today's date with leading zeros on page load	 
	var today = ('0' + (today.getMonth()+ 1)).slice(-2) + '-' + ('0' + today.getDate()).slice(-2) + '-' + today.getFullYear();
	 
	$( '#date' ).html( today );

	$( '#datepicker' ).datepicker({							// jQuery UI's Datepicker
		showOtherMonths: true,
		selectOtherMonths: true,
		minDate: "-7Y",
		maxDate: "+1W",
	});

	$( '#datepicker' ).change(function() {
		$( '#date' ).html( $( "#datepicker" ).val() );		// Use datepicker to select new date
	});
});