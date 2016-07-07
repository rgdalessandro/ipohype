$(document).ready(function() {
    var todayDate = new Date();
    fetchMonth(todayDate.getMonth() + 1, todayDate.getFullYear(), function(msg) { // Call the function to show this month's IPOs
        goodDays = [];

        for (i in msg.results) {
            dateParts = msg.results[i]["ipodate"].split("-");
            dateMonth = parseInt(dateParts[1]);
            dateDate = parseInt(dateParts[2]);
            goodDays.push(dateMonth + "-" + dateDate);
        }
        $.globalDatepicker = $('#datepicker').datepicker({ // jQuery UI's Datepicker initialized to a global variable
            beforeShowDay: function(e) {
                var t = e.getDay();
                var d = e.getDate();
                var m = e.getMonth();

                return [(goodDays.indexOf(parseInt(m) + 1 + "-" + (parseInt(d))) != -1), ""]; // Only allow dates containing IPOs
            },

            showOtherMonths: true,
            selectOtherMonths: true,
            minDate: new Date(2014, 1 - 1, 10),
            maxDate: new Date(2015, 8 - 1, 31),
            onChangeMonthYear: changeMonth
        });

        displayResults(msg);
    });

    fixArrows();

    function changeMonth(year, month) // Display all IPOs in new month as the month is changed
        {

            fetchMonth(month, year, function(msg) {
                goodDays = [];

                for (i in msg.results) {
                    dateParts = msg.results[i]["ipodate"].split("-");
                    dateMonth = parseInt(dateParts[1]);
                    dateDate = parseInt(dateParts[2]);
                    goodDays.push(dateMonth + "-" + dateDate);
                }

                $("#datepicker").datepicker("refresh");
                displayResults(msg);
            });

            fixArrows();
        }

    $('#datepicker').change(function() {
        $('#date').html($("#datepicker").val()); // Use datepicker to select new date and display the new date's IPOs
        fetchIPOs($("#datepicker").val());
    });


    $(document).on("click", ".ipoResultRow", function() {
            $(this).find(".ipoResultRowSlider").slideToggle();
    });

    $(document).on("click", ".ui-datepicker-month", function() {
        month = $.globalDatepicker.datepicker('getDate').getMonth() + 1;
        year = $.globalDatepicker.datepicker('getDate').getFullYear();

        fetchMonth(month, year, displayResults);
    });
});

function fixArrows() // Function to change the default datepicker arrows to nicer Bootstrap chevrons
    {
        setTimeout(function() {
            $('.ui-icon-circle-triangle-w').hide().html("").removeClass('ui-icon-circle-triangle-w').removeClass("ui-icon").addClass('glyphicon glyphicon-chevron-left').fadeIn();
            $('.ui-icon-circle-triangle-e').hide().html("").removeClass('ui-icon-circle-triangle-e').removeClass("ui-icon").addClass('glyphicon glyphicon-chevron-right').fadeIn();
        }, 500);
    }

function displayResults(msg) { // Function to display IPOs fetched from the database
    if (msg.results.length) {
        for (i in msg.results) {
            result = msg.results[i];

            if (parseFloat(result.closeprice) > parseFloat(result.ipoprice)) {
                offerGrowthClass = "gain";
            } else if (parseFloat(result.closeprice) < parseFloat(result.ipoprice)) {
                offerGrowthClass = "loss";
            } else {
                offerGrowthClass = "wash";
            }

            if (parseFloat(result.closeprice) > parseFloat(result.openprice)) {
                marketGrowthClass = "gain";
            } else if (parseFloat(result.closeprice) < parseFloat(result.openprice)) {
                marketGrowthClass = "loss";
            } else {
                marketGrowthClass = "wash";
            }

            hypeVal = Math.floor(((result.hype - 40) / 25) * 10); // My own secret sauce to normalize hype
            if (hypeVal < 0 | !parseInt(result.tweet) ) {hypeVal = ' -';} // Hype cannot be less than zero or missing
            if ( !(result.ipoprice > 0) ) {result.ipoprice = ' N/A';} // ipoprice cannot be less than zero
            if ( !(result.closeprice > 0) ) {result.closeprice = ' N/A';} // closeprice cannot be less than zero
            if ( !(result.openprice > 0) ) {result.openprice = ' N/A';} // openprice cannot be less than zero
            if ( !result.openprice ) {result.openprice = ' N/A';} // openprice cannot be less than zero

            offerGrowth = (((result.closeprice / result.ipoprice) * 100) - 100).toFixed(2);
            marketGrowth = (((result.closeprice / result.openprice) * 100) - 100).toFixed(2);

            if ( isNaN(offerGrowth) ) {offerGrowth = '';} // offerGrowth cannot NaN
            if ( isNaN(marketGrowth) ) {marketGrowth = '';} // marketGrowth cannot NaN

            $("#results").append(
                '<div class="ipoResultRow" title="Click for details">\
                    <div class="pull-left">\
                        <div class="symbol">' + result.symbol + '</div>\
                        <div class="companyName">' + result.company + '</div>\
                    </div>\
                    <div class="pull-right">\
                        <div class="hype hypeVal_' + hypeVal + '">' + hypeVal + '</div>\
                    </div>\
                    <div class="clearfix"></div>\
                    <div class="ipoResultRowSlider" title="First Day Performance">\
                        <div class="offer">\
                            <div class="pull-left">\
                                <div class="sliderHeader pull-left">IPO: </div><div class="prices">$' + result.ipoprice + ' <span class="glyphicon glyphicon-arrow-right"></span>  $' + result.closeprice + '</div>\
                            </div>\
                            <div class="pull-right"><div class="growth ' + offerGrowthClass + '">' + offerGrowth + '%</div></div>\
                            <div class="clearfix"></div>\
                        </div>\
                        <div class="market">\
                            <div class="pull-left">\
                                <div class="sliderHeader pull-left">Market: </div><div class="prices">$' + result.openprice + ' <span class="glyphicon glyphicon-arrow-right"></span>  $' + result.closeprice + '</div>\
                            </div>\
                            <div class="pull-right"><div class="growth ' + marketGrowthClass + '">' + marketGrowth + '%</div></div>\
                            <div class="clearfix"></div>\
                        </div>\
                    </div>\
                </div>');
        }
    } else {
        $('#noResults').show();
    }
}

function fetchIPOs(date) // Function to pull a single date from the ipo table
    {
        $('#results').html('');
        $('#noResults').hide();
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
                displayResults(msg);
            }
        });
        
        fixArrows();

    }

function fetchMonth(month, year, callback) // Function to pull a whole month from the ipo table
    {
        $('#results').html('');
        $('#noResults').hide();
        $.ajax({
            type: "GET",
            url: "control.php",
            dataType: "json",
            async: true,

            data: {
                "function": "monthToCheck",
                "month": month,
                "year": year
            }

        }).done(function(msg) {
            if (msg.status == "OK") {
                callback(msg);
            }
        });
    }