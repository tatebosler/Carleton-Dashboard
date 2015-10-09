var timer = 30;

var buildings = {buildings: {"Sayles-Hill Campus Center": ["sayles", "security", "security-office", "sayles-cafe", "bookstore", "post", "onecard", "ccce", "career", "sao", "info", "krlx"],
  "Dining": ["burton", "ldc", "east-express", "sayles-cafe", "weitz-cafe", "dominos"],
  "Laurence McKinley Gould Library": ["libe", "reference", "libe-it", "writing", "archives"],
  "Academic Support": ["msc", "writing", "language"],
  "Information Technology Services": ["its", "libe-it", "peps"],
  "Recreation": ["rec", "wall-bouldering", "cowling", "cowling-pool", "west", "west-pool", "stadium"],
  "Business and Administration": ["registrar", "business", "mail", "print"],
  "Other": ["shac", "dacie-moses", "perlman", "idealab"]}};

var displayData = {"open": {"icon": "done", "color": "green", "strong": "Open"}, "open-onecard": {"icon": "credit_card", "color": "green", "strong": "Open - OneCard required"}, "warning": {"icon": "access_time", "color": "orange", "strong": "Closes soon"}, "warning-onecard": {"icon": "credit_card", "color": "orange", "strong": "Closes soon - OneCard required"}, "closed": {"icon": "clear", "color": "red", "strong": "Closed"}, "24h": {"icon": "refresh", "color": "indigo", "strong": "Open 24 hours"}, "24h-onecard": {"icon": "credit_card", "color": "indigo", "strong": "Open 24 hours - OneCard required"}};

function updateData() {
  $.ajax({url: "api.php", async: true, method: "POST", data: buildings, success: function(result) {
    var jsonResult = jQuery.parseJSON(result);
    $("#facilityList").empty();
    for(var category in jsonResult) {
      $("#facilityList").append('<li class="collection-header"><h4>' + category + '</h4></li>');
      for(var facility in jsonResult[category]) {
        var htmlOut = '<a name="' + facility + '"></a><li class="collection-item"><strong>'
        + jsonResult[category][facility]["name"] + ': <span class="'
        + displayData[jsonResult[category][facility]["status"]]["color"]
        + '-text">'
        + displayData[jsonResult[category][facility]["status"]]["strong"]
        + '</span></strong>';
        
        if(jsonResult[category][facility]["status"] == "open" || jsonResult[category][facility]["status"] == "open-onecard") {
          htmlOut += " until " + jsonResult[category][facility]["closingTime"];
        } else if (jsonResult[category][facility]["status"] == "warning" || jsonResult[category][facility]["status"] == "warning-onecard") {
          htmlOut += " (" + jsonResult[category][facility]["closingTime"] + ") - " + jsonResult[category][facility]["next"];
        } else if (jsonResult[category][facility]["status"] == "closed") {
          htmlOut += " - " + jsonResult[category][facility]["next"];
        }
        
        htmlOut += '<strong><i class="hide-on-small-only left material-icons secondary-content '
        + displayData[jsonResult[category][facility]["status"]]["color"]
        + '-text">' + displayData[jsonResult[category][facility]["status"]]["icon"]
        + '</i></strong></li>';
        $(".potato").hide();
        $(".anti-potato").show();
        $("#facilityList").append(htmlOut);
      }
    }
  }, error: function() {
    timer = 4;
    Materialize.toast("Whoops, something went wrong. We'll try again in 5 seconds.", 4000);
  }});
}

function tickTimer() {
  if(timer <= 0) {
    updateData();
    timer = 29;
  } else {
    timer -= 1;
  }
  var timerPercentage = 100 - ((timer * 100) / 29);
  var timerPercentageText = timerPercentage + "%";
  $("#progress-bar").css("width", timerPercentageText);
}

$(document).ready(function() {
  updateData();
  timer = 29;
  var cycleTimer = setInterval(function() {tickTimer();}, 1000);
});
