$( document ).ready( function() {
	$( '#main' ).html( Date() );							// Default to today's date

	$( '#datepicker' ).datepicker({							// jQuery UI's Datepicker
		showOtherMonths: true,
		selectOtherMonths: true,
		minDate: "-7Y",
		maxDate: "+1W",
		altField: "#invisibleDate",
	});

	$( '#datepicker' ).change(function() {
		$( '#main' ).html( $( "#datepicker" ).val() );		// Use datepicker to select new date
	});
});