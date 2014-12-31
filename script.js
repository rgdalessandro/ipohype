$(document).ready(function() {
    var todayDate = new Date(); // Default to today's date with leading zeros on page load   
    var today = ('0' + (todayDate.getMonth() + 1)).slice(-2) + '/' + ('0' + todayDate.getDate()).slice(-2) + '/' + todayDate.getFullYear();
    // $('#date').html(today);
    
    fetchMonth(todayDate.getMonth()+1, todayDate.getFullYear(), function(msg){
        goodDays = [];

        for (i in msg.results) {
            dateParts = msg.results[i]["ipodate"].split("-");
            dateMonth = parseInt(dateParts[1]); 
            dateDate = parseInt(dateParts[2]); 
            goodDays.push( dateMonth + "-" + dateDate );
        } 
        $.globalDatepicker = $('#datepicker').datepicker({ // jQuery UI's Datepicker initialized to a global variable
            beforeShowDay: function (e){
                var t=e.getDay();
                var d=e.getDate();
                var m=e.getMonth();

                return[(t>0&&6>t)&&(goodDays.indexOf(parseInt(m)+1 + "-" + (parseInt(d)))!=-1),""];
            },

            showOtherMonths: true,
            selectOtherMonths: true,
            minDate: new Date(2014, 1 - 1, 10),
            maxDate: "0",
            onChangeMonthYear: changeMonth
        });

        displayResults(msg);
    });

    function changeMonth( year, month )
    {
        fetchMonth(month, year, function(msg){
            goodDays = [];

            for (i in msg.results) {
                dateParts = msg.results[i]["ipodate"].split("-");
                dateMonth = parseInt(dateParts[1]); 
                dateDate = parseInt(dateParts[2]); 
                goodDays.push( dateMonth + "-" + dateDate );
            }

            $("#datepicker").datepicker("refresh");

        });
    }
    

    $('#datepicker').change(function() {
        $('#date').html($("#datepicker").val()); // Use datepicker to select new date
        fetchIPOs($("#datepicker").val());
    });

    // $('.ui-ico n-circle-triangle-w').html("").removeClass('ui-icon-circle-triangle-w').addClass('glyphicon glyphicon-chevron-left');

    $(document).on("click", ".ipoResultRow", function() {
        $(this).find(".ipoResultRowSlider").slideToggle();
    });

    $(document).on("click", ".ui-datepicker-month", function() {
        month = $.globalDatepicker.datepicker('getDate').getMonth() + 1;
        year = $.globalDatepicker.datepicker('getDate').getFullYear();

        fetchMonth(month, year, displayResults);
    });
});

function displayResults(msg) {
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

            hypeVal = Math.floor(((result.hype - 40) / 25) * 10);

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
                            <div class="pull-right"><div class="growth ' + offerGrowth + '">' + (((result.closeprice / result.ipoprice) * 100) - 100).toFixed(2) + '%</div></div>\
                            <div class="clearfix"></div>\
                        </div>\
                        <div class="market">\
                            <div class="pull-left">\
                                <div class="sliderHeader pull-left">Market: </div><div class="prices">$' + result.openprice + ' <span class="glyphicon glyphicon-arrow-right"></span>  $' + result.closeprice + '</div>\
                            </div>\
                            <div class="pull-right"><div class="growth ' + marketGrowth + '">' + (((result.closeprice / result.openprice) * 100) - 100).toFixed(2) + '%</div></div>\
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