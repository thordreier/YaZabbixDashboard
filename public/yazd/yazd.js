function initpage() {
    updateHostGroups();
    window.setInterval(updateHostGroups, 120000);
    window.setInterval(updateProblems, 19000);
}

function jsonError() {
    $('#main').html('Error loading JSON data...');
}

function alignSite() {
    $('.groupbox').masonry({
        itemSelector: '.hostbox'
    });
}

function updateHostGroups() {
    $.ajax({
        type: 'GET',
        url: 'hostgroups.json',
        success: function(data) {
            var html = "";
            html += "<div id='clocks'></div>";
            html += "<br />";
            for (var hostGroup of data['hostgroups']) {
                html += "<div class='title'>" + hostGroup['name'] + "</div>";
                html += "<div class='groupbox'>";
                for (host of hostGroup['hosts']) {
                    html += "<div class='hostbox' id='h" + host['id'] + "'>";
                    html += "<div class='title'>" + host['name'] + "</div>";
                    html += "<div class='hostid'>" + host['id'] + "</div>";
                    html += "<div class='hostproblems' id='d" + host['id'] + "'></div>";
                    html += "</div>";
                }
                html += "</div>";
                html += "<br />";
            }
            $('#main').html(html);
            updateProblems();
        },
        error: function() {
            jsonError();
        }
    });
}

function updateProblems() {
    $.ajax({
        type: 'GET',
        url: 'hostswithproblems.json',
        success: function(data) {
            $('.hostbox').removeClass('ok nok1 nok2 nok3 nok4 nok5').addClass('ok');
            $('.hostproblems').html('');
            for (var host of data['hosts']) {
                $('#h'+host['hostid']).removeClass('ok').addClass('nok'+host['severity']);
                htmlDescription = "";
                for (var problem of host['problems']) {
                    htmlDescription += "<div class='description'>";
                    htmlDescription += "<div class='smallsquare nok" + problem['severity'] + "'></div>";
                    htmlDescription += problem['name'];
                    htmlDescription += "</div>";
                }
                $('#d'+host['hostid']).html(htmlDescription);
            }
            clocksHtml = "";
            for (var clock of data['clocks']) {
                clocksHtml += "<div class='clock'>";
                clocksHtml += "<div class='clockname'>" + clock['name'] + "</div>";
                clocksHtml += "<div class='clocktime'>" + clock['time'] + "</div>";
                clocksHtml += "</div>";
            }
            $('#clocks').html(clocksHtml);
            alignSite();
        },
        error: function() {
            jsonError();
        }
    });
}
