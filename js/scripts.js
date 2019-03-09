////////////////////////////////////////////////////////////////////////////////
//
////////////////////////////////////////////////////////////////////////////////
function setScore(elementid){
    /* make an AJAX call to the php script */
    var value=document.getElementById(elementid).value;
    var urlstr = '?mode=setScore&elementid='+elementid+'&score='+value+'&norender';
    console.log(urlstr);
    //document.getElementById("reloadID").innerHTML = 'setMyScore';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            //console.log('setMyScore');
            document.getElementById("msg").innerHTML = 'URL: '+urlstr;
            document.getElementById("reloadID").innerHTML = 'Reload Needed';
        }
    };
    xhttp.open("GET", urlstr, true);
    xhttp.send();
}
////////////////////////////////////////////////////////////////////////////////
// Consider modifying this to work like the newer routine above.
////////////////////////////////////////////////////////////////////////////////
function setTeamName(element,elementid,poolid,teamid){
    /* make an AJAX call to the php script */
    var id = element.id;
    var value=document.getElementById(elementid).value;
    console.log('AJAX setTeamName(' + elementid + ',' + poolid + ',' + teamid + ',' + value + ')');

    var urlstr = '?mode=setTeamName&team='+teamid+'&pool='+poolid+'&name='+value+'&norender';

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("msg").innerHTML = 'URL: '+urlstr;
            document.getElementById("reloadID").innerHTML = '<a href="?mode=reload" class="reload-button">Reload</a>';
        }
    };
    xhttp.open("GET", urlstr, true);
    xhttp.send();
}
