/**
 * Created by Marko on 01.09.2016.
 */
$().ready(function () {

    if($('#positions > div').length < 1) return;

    objRun.itemsTotal = $('#positions > div').length;
    objRun.currentIndex = $('#positions > div').index($('#positions > div.current').eq(0));

    // Store data into a cookie
    var blnSetCookie = true;
    if (getCookie() !== null) {
        var oc = getCookie();
        if (oc.token == objRun.token) {
            blnSetCookie = false;
        }
    }

    if (blnSetCookie) {
        // first delete old cookie if it is from another session
        deleteCookie();

        var objCookie = {
            positions: {},
            token: objRun.token,
            lastPosX: 0,
            lastPosY: 0,
            finished: false,
            startTime: objRun.startTime,
            endTime: 0,
            allDataSavedToServer: false
        };
        $('#positions > div').each(function () {
            var posId = $(this).attr('data-pos-id');
            objCookie['positions']['pos-' + posId] = {
                id: posId,
                passed: ($(this).attr('data-passed') == 'false') ? false : true,
                logTime: ($(this).attr('data-logtime') == '0') ? 0 : $(this).attr('data-logtime'),
                savedToServer: ($(this).attr('data-saved-to-server') == 'true') ? true : false,
                posX: ($(this).attr('data-log-posx') == '0') ? 0 : $(this).attr('data-log-posx'),
                posY: ($(this).attr('data-log-posy') == '0') ? 0 : $(this).attr('data-log-posy')
            };
        });
        setCookie(objCookie);
    }
    // End Cookie

    // Click Event
    $('#btnValidatePosition').click(function () {

        objRun.button = $(this);
        var currentItem = $('#positions > div').eq(objRun.currentIndex);
        objRun.targetPosX = currentItem.attr('data-posx');
        objRun.targetPosY = currentItem.attr('data-posy');
        objRun.currentPosId = currentItem.attr('data-pos-id');

        // GPS Request
        navigator.geolocation.getCurrentPosition(function (objGPS) {
            // Success
            var crd = objGPS.coords;

            objRun.gpsTimestamp = Math.round(objGPS.timestamp / 1000);

            // Convert int Lv95 Coordinates
            var aPos = convertVgs84ToLv95(crd.longitude, crd.latitude);
            objRun.posX = Math.round(aPos[0]);
            objRun.posY = Math.round(aPos[1]);
            objRun.lastPosX = aPos[0];
            objRun.lastPosY = aPos[1];

            writeSystemMsg('Deine Position ist: ' + aPos[0] + ' // ' + aPos[1]);

            // Start Cookie
            var oCookie = getCookie();
            oCookie['lastPosX'] = aPos[0];
            oCookie['lastPosY'] = aPos[1];
            setCookie(oCookie);
            // End Cookie

            // Get Distance to target (pythagoras)
            var a = Math.abs(objRun.posX - objRun.targetPosX);
            var a2 = Math.pow(a, 2);
            var b = Math.abs(objRun.posY - objRun.targetPosY);
            var b2 = Math.pow(b, 2);
            var c2 = a2 + b2;
            var c = Math.pow(c2, 0.5);
            objRun.distanceToTarget = Math.floor(c);


            // Tolerance 60m
            if (objRun.distanceToTarget < 60) {
                // Start Cookie
                var oCookie = getCookie();
                oCookie['positions']['pos-' + objRun.currentPosId]['passed'] = true;
                oCookie['positions']['pos-' + objRun.currentPosId]['logTime'] = objRun.gpsTimestamp;
                oCookie['positions']['pos-' + objRun.currentPosId]['posX'] = objRun.posX;
                oCookie['positions']['pos-' + objRun.currentPosId]['posY'] = objRun.posY;
                setCookie(oCookie);
                // End Cookie

                /** adapt row attributes **/
                $('#positions > div.current').addClass('passed');
                $('#positions > div.current').removeClass('current');
                $('#position-' + objRun.currentPosId).attr('data-logTime', objRun.gpsTimestamp);
                $('#position-' + objRun.currentPosId).attr('data-passed', 'true');
                $('#position-' + objRun.currentPosId).attr('data-log-posx', objRun.posX);
                $('#position-' + objRun.currentPosId).attr('data-log-posy', objRun.posY);

                objRun.currentIndex = objRun.currentIndex + 1;
                $('#positions > div').eq(objRun.currentIndex).addClass('current');
                writeSystemMsg('Super! Du hast es geschafft! Du befindest dich ' + objRun.distanceToTarget + ' m vom gesuchten Posten entfernt. Mache dich auf zum naechsten Posten!');
            } else {
                writeSystemMsg('Du bist leider noch zu weit weg. Der Posten befindet ' + objRun.distanceToTarget + ' m Luftlinie von hier.');
            }

            // If run is completed
            if (objRun.currentIndex == objRun.itemsTotal) {

                objRun.endTime = objRun.gpsTimestamp;

                // Start Cookie
                var oCookie = getCookie();
                oCookie.finished = true;
                oCookie.endTime = objRun.gpsTimestamp;
                setCookie(oCookie);
                // End Cookie

                objRun.button.remove();
            }

        }, function (err) {
            // GPS Connection Error
            $('#connectionError').html('ERROR(' + err.code + '): ' + err.message);
        }, {
            // GPS options
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 1
        });
    });





    // If there was no internet connection data were savedi in cookies.
    // Check every 3s for unsaved positions and send them to the server, if there is internet connection
    var interval = window.setInterval(function () {
        if (getCookie() !== null) {
            var oCookie = getCookie();
            var allPositionsSavedToServer = true;

            $.each(oCookie.positions, function (key, value) {
                if (value.savedToServer === false) {
                    allPositionsSavedToServer = false;
                    if (value.posX > 0 && value.posY > 0 && value.passed === true) {

                        // Request C
                        var url = objRun.url + '?token=' + objRun.token + '&posId=' + value.id + '&passed=true&posX=' + value.posX + '&posY=' + value.posY + '&logTime=' + value.logTime;
                        $.getJSON(url, function (data) {
                            if (data.success == 'true') {
                                // Start Cookie
                                var oCookie = getCookie();
                                oCookie['positions']['pos-' + value.id]['savedToServer'] = true;
                                setCookie(oCookie);
                                // End Cookie

                                $('#connectionError').html('Keine St&ouml;rung.');

                            } else {
                                $('#connectionError').html('<div>Im Moment kann nicht dem Server verbunden werden. Damit die Daten am Ende gespeichert werden k&ouml;nnen, ist es wichtig, einen Ort aufzusuchen, wo du eine Verbindung mit dem Internet herstellen kannst.</div>');
                            }
                        }).fail(function () {
                            $('#connectionError').html('<div>Im Moment kann nicht dem Server verbunden werden. Damit die Daten am Ende gespeichert werden k&ouml;nnen, ist es wichtig, einen Ort aufzusuchen, wo du eine Verbindung mit dem Internet herstellen kannst.</div>');
                        });
                        // End Request C
                    }
                }

            });

            // If all positions are done, send the endtime to the server
            if (oCookie.allDataSavedToServer === false) {

                if (allPositionsSavedToServer) {
                    var url2 = objRun.url + '?token=' + objRun.token + '&finished=true&endTime=' + oCookie.endTime;
                    $.getJSON(url2, function (data) {
                        if (data.success == 'true') {
                            var oCookie = getCookie();
                            oCookie['allDataSavedToServer'] = true;
                            writeSystemMsg('Du hast es geschafft. Der Ol ist zu Ende!');
                            setCookie(oCookie);
                            // End Cookie
                            clearInterval(interval);
                            $('#connectionError').html('Keine St&ouml;rung.');
                        } else {
                            $('#connectionError').html('<div>Im Moment kann nicht dem Server verbunden werden. Damit die Daten am Ende gespeichert werden k&ouml;nnen, ist es wichtig, einen Ort aufzusuchen, wo du eine Verbindung mit dem Internet herstellen kannst.</div>');
                        }
                    }).fail(function () {
                        $('#connectionError').html('<div>Im Moment kann nicht dem Server verbunden werden. Damit die Daten am Ende gespeichert werden k&ouml;nnen, ist es wichtig, einen Ort aufzusuchen, wo du eine Verbindung mit dem Internet herstellen kannst.</div>');
                    });
                }
            }
        }
    }, 3000);
});



/** FUNCTIONS FUNCTIONS FUNCTIONS FUNCTIONS FUNCTIONS */

 /**
 * 
 * @param msg
 */
function writeSystemMsg(msg) {
    $('<div>' + msg + '</div>').prependTo($('#runInfo'));
}

/**
 * Convert Coordinates from vgs84 to lv95
 * @param posX
 * @param posY
 * @returns {*[]}
 */
function convertVgs84ToLv95(posX, posY) {
    var phi = posY * 3600;
    var lambda = posX * 3600;

    var phii = (phi - 169028.66) / 10000;
    var lambdai = (lambda - 26782.5) / 10000;


    var x = 600072.37
        + (211455.93 * lambdai)
        - (10938.51 * lambdai * phii)
        - (0.36 * lambdai * Math.pow(phii, 2))
        - (44.54 * Math.pow(lambdai, 3));

    var y = 200147.07
        + (308807.95 * phii)
        + (3745.25 * Math.pow(lambdai, 2))
        + (76.63 * Math.pow(phii, 2))
        - (194.56 * Math.pow(lambdai, 2) * phii)
        + (119.79 * Math.pow(phii, 3));

    return [Math.round(x) + 2000000, Math.round(y) + 1000000];
}

/**
 * Set Cookie
 * @param newCookie
 */
function setCookie(newCookie) {
    var key = 'ol';
    var oCookie = getCookie(key);
    if (oCookie !== null) {
        $.extend(true, oCookie, newCookie);
    } else {
        var oCookie = newCookie;
    }
    oCookie = JSON.stringify(oCookie);
    var expires = new Date();
    expires.setTime(expires.getTime() + (1 * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + oCookie + ';expires=' + expires.toUTCString();
}

/**
 * Get the cookie an return it as an object
 * @returns {null}
 */
function getCookie() {
    var key = 'ol';
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? JSON.parse(keyValue[2]) : null;
}

/**
 * Delete the cookie
 */
function deleteCookie() {
    var key = 'ol';
    document.cookie = key + '=; expires=Thu, 01 Jan 1970 00:00:01 GMT;';

}

