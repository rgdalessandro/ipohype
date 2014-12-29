$(document).ready(function() {
    var today = new Date(); // Default to today's date with leading zeros on page load	 
    var today = ('0' + (today.getMonth() + 1)).slice(-2) + '/' + ('0' + today.getDate()).slice(-2) + '/' + today.getFullYear();
    $('#date').html(today);
    fetchIPOs(today);

    $('#datepicker').datepicker({ // jQuery UI's Datepicker
        beforeShowDay: $.datepicker.noWeekends,
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: new Date(2014, 1 -1, 10),
        maxDate: "0",
    });

    $('#datepicker').change(function() {
        $('#date').html($("#datepicker").val()); // Use datepicker to select new date
        fetchIPOs($("#datepicker").val());
    });

    $(document).on("click", ".ipoResultRow", function() {
        $(this).find(".ipoResultRowSlider").slideToggle();
    });
});

function fetchIPOs(date) // Function to pull from the ipo table
    {
        $( '#results' ).html('');
        $( '#noResults' ).hide();
        $.ajax({
            type: "GET",
            url: "control.php",
            dataType: "json",
            async: true,

            data: {
                "function": "dateToCheck",
                "date": date
            }

        }).done(function(msg) {
            if (msg.status == "OK") {
                if (msg.results.length) {
                    console.log(msg.results);
                    for (i in msg.results) {
                        result = msg.results[i];

                        if (parseFloat(result.closeprice) > parseFloat(result.ipoprice)) {
                            offerGrowth = "gain";
                        } else if (parseFloat(result.closeprice) < parseFloat(result.ipoprice)) {
                            offerGrowth = "loss";
                        } else {
                            offerGrowth = "wash";
                        }

                        if (parseFloat(result.closeprice) > parseFloat(result.openprice)) {
                            marketGrowth = "gain";
                        } else if (parseFloat(result.closeprice) < parseFloat(result.openprice)) {
                            marketGrowth = "loss";
                        } else {
                            marketGrowth = "wash";
                        }

                        $("#results").append(
                            '<div class="ipoResultRow">\
							<div class="pull-left">\
								<div class="symbol">' + result.symbol + '</div>\
								<div class="companyName">' + result.company + '</div>\
							</div>\
							<div class="pull-right">\
								<div class="hype">' + result.hype + '</div>\
							</div>\
							<div class="clearfix"></div>\
							<div class="ipoResultRowSlider">\
								<div class="offer">\
									<div class="pull-left">\
										<div><div class="offeringPrice">Ipo Offering Price: </div><div class="offering">$' + result.ipoprice + '</div></div>\
										<div><div class="closingPrice">1st Day Closing Price: </div><div class="closing">$' + result.closeprice + '</div></div>\
									</div>\
									<div class="pull-right"><div class="growth ' + offerGrowth + '">' + (((result.closeprice / result.ipoprice) * 100) - 100).toFixed(2) + '%</div></div>\
									<div class="clearfix"></div>\
								</div>\
								<div class="market">\
									<div class="pull-left">\
										<div><div class="marketPrice">1st Day Opening Price: </div><div class="open">$' + result.openprice + '</div></div>\
										<div><div class="closingPrice">1st Day Closing Price: </div><div class="closing">$' + result.closeprice + '</div></div>\
									</div>\
									<div class="pull-right"><div class="growth ' + marketGrowth + '">' + (((result.closeprice / result.openprice) * 100) - 100).toFixed(2) + '%</div></div>\
									<div class="clearfix"></div>\
								</div>\
							</div>\
						</div>'
                        );
                    }
                } else {
                	$( '#noResults' ).show();
                }
            }
        });
    }