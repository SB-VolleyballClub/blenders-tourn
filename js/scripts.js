function setTeamName(element,elementid,poolid,teamid){
    /* make an AJAX call to the php script */
    var id = element.id;
    var value=document.getElementById(elementid).value;
    // var value = id.value;
    console.log('AJAX setTeamName(' + elementid + ',' + poolid + ',' + teamid + ',' + value + ')');
    //console.log('AJAX setTeamName(' + teamid + ',' + poolid + ',' + value + ',' + elementid + ',' + id + ')');

    //var target = event.target || event.srcElement;
    var urlstr = 'index.php?mode=setTeamName&team='+teamid+'&pool='+poolid+'&name='+value;

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            //document.getElementById("msg").innerHTML = this.responseText; */
            document.getElementById("msg").innerHTML = 'URL:'+urlstr;
            // var value=document.getElementById(elementid).value;
        }
    };
    xhttp.open("GET", urlstr, true);
    xhttp.send();

}
