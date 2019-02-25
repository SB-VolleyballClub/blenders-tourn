<?php
////////////////////////////////////////////////////////////////////////////////
class poolLayout {
    function __construct($numTeams){
        $this->layout = array();
        if ($numTeams == 3){
            $this->addMatchDef(3,1,3,2,2,25);
            $this->addMatchDef(3,2,3,1,2,25);
            $this->addMatchDef(3,1,2,3,2,25);
        }
        if ($numTeams == 4){
            $this->addMatchDef(4,1,3,4,2,25);
            $this->addMatchDef(4,2,4,3,2,25);
            $this->addMatchDef(4,1,4,2,2,25);
            $this->addMatchDef(4,2,3,4,2,25);
            $this->addMatchDef(4,3,4,1,2,25);
            $this->addMatchDef(4,1,2,3,2,25);
        }
        if ($numTeams == 5){
            $this->addMatchDef(5,1,2,3,2,15);
            $this->addMatchDef(5,3,4,2,2,15);
            $this->addMatchDef(5,1,5,4,2,15);
            $this->addMatchDef(5,2,3,5,2,15);
            $this->addMatchDef(5,4,5,1,2,15);
            $this->addMatchDef(5,1,3,2,2,15);
            $this->addMatchDef(5,2,5,4,2,15);
            $this->addMatchDef(5,1,4,3,2,15);
            $this->addMatchDef(5,3,5,1,2,15);
            $this->addMatchDef(5,4,2,5,2,15);
        }
    }
    function addMatchDef($numTeams,$t0,$t1,$ref,$numGames,$numPoints = 25){
        // skipped the numTeams option for now...
        // need to add a scores in here
        $tmpa = array('t0' => $t0,'t1' => $t1, 'ref' => $ref, 'numGames' => $numGames, 'points' => $numPoints );
        for($g=0;$g<$tmpa['numGames'];$g++){
            for($t=0;$t<2;$t++){
                $scorek='score-g'.$g.'t'.$t;
                $tmpa[$scorek] = '0';
            }
        }
        $this->layout[] = $tmpa;
    }
}
////////////////////////////////////////////////////////////////////////////////
class team {
    public static function tableHeader(){
        $b = '';
        $b .= '<tr>';
        $b .= '<td>#</td>';
        $b .= '<td>Name</td>';
        $b .= '<td>W</td>';
        $b .= '<td>L</td>';
        $b .= '<td>% W:L</td>';
        $b .= '<td>Stat</td>';
        $b .= '</tr>';
        return $b;
    }
    function __construct($id,$poolid,$name = 'NA'){
        $this->id = $id;
        $this->poolid = $poolid;
        $this->name = $name;
        $this->wins = 0;
        $this->losses = 0;
        $this->ratio = 0.0;
        $this->status = 'I';
    }
    function __sleep(){
        return array('wins','losses','ratio','name','id','poolid','status');
    }
    function render($editable = true){
        $elementid = 'p'.$this->poolid.'t'.$this->id;
        $this->buf ='<tr>';
        $this->buf .='<td>';
        $this->buf .= $this->id;
        $this->buf .='</td>';
        $this->buf .='<td>';
        if ($editable){
            $this->buf .= '<form><input id="' . $elementid . '" type="text" value="' . $this->name . '" onBlur="setTeamName(this,\'' . $elementid . '\',' . $this->poolid . ',' . $this->id . ')"></input></form>';
        }
        else {
            $this->buf .= $this->name;
        }
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= $this->wins;
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= $this->losses;
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= $this->ratio;
        $this->buf .='</td>';

        // if status string is ONLY WL, then color is green, else red
        $statusclass = (preg_match('/^[WL]*$/',$this->status)) ? 'complete' : 'incomplete';
        $this->buf .='<td>';
        $this->buf .= "<span class=\"$statusclass\">" . $this->status . "</span>\n";
        $this->buf .='</td>';
        $this->buf .='</tr>';

        return $this->buf;
    }
    // function html(){
    //     $this->render();
    //     return $this->buf;
    // }
    function setName($name){
        print "team::setName($name)<br>";
        $this->name = $name;
    }
}
////////////////////////////////////////////////////////////////////////////////
class pool {
    function __construct($id){
        $this->id = $id;
        $this->teams = array();
        $this->layout = array();
    }
    function __sleep(){
        return array('teams','id','layout');
    }
    function __wakeup(){
        $this->updateWinLoss();
    }
    function checkScore($a,$b){
        // never really implemented.
        // the thought is that two of the checks are VERY similar, just with winners reversed
    }
    function updateWinLoss(){
        // consider assigning elementid in has????
        //print "updating win loss info<br>\n";
        // we are at the pool level, so we can just go through the layout grid
        // Just sweep through and assign...  maybe build up a temp buff and then assign?
        // maybe flag issues as well? (incomplete, unrealistic scores 8-28, 8-10)
        $status = array();

        // indexed by teamindex - initialize with zeros
        $teampts = array();
        $oppopts = array();
        $wcnts = array();
        $lcnts = array();
        for($i=0;$i<count($this->teams);$i++){
            $teampts[$i] = 0;
            $teampts[$i] = 0;
            $oppopts[$i] = 0;
            $wcnts[$i] = 0;
            $lcnts[$i] = 0;
            $status[$i] = '';
        }
        foreach($this->layout as $k => &$match){
            $t[$k] = array();
            $maxpts = $match['points'];
            $t1index = $match['t0']-1;
            $t2index = $match['t1']-1;

            // start by categorizing the score
            // (ie: incomplete (I), error (E), unfinished? (U),tied (T), zero (Z))
            for($g=0;$g<2;$g++){
                $teampts[$t1index] += $match['score-g'.$g.'t0'];
                $teampts[$t2index] += $match['score-g'.$g.'t1'];
                $oppopts[$t1index] += $match['score-g'.$g.'t1'];
                $oppopts[$t2index] += $match['score-g'.$g.'t0'];

                //$this->checkScore($match['score-g'.$g.'t0'],$match['score-g'.$g.'t1']);

                if ($match['score-g'.$g.'t0'] == 0 && $match['score-g'.$g.'t1'] == 0){
                    $status[$t1index] .= 'z';
                    $status[$t2index] .= 'z';
                }
                elseif ($match['score-g'.$g.'t0'] > $match['score-g'.$g.'t1']){
                    if ($match['score-g'.$g.'t0'] >= $maxpts){
                        if ( ($match['score-g'.$g.'t0'] - $match['score-g'.$g.'t1']) >= 2){
                            $status[$t1index] .= 'W';
                            $status[$t2index] .= 'L';
                            $wcnts[$t1index]++;
                            $lcnts[$t2index]++;
                            $wpts[$t1index] += $match['score-g'.$g.'t0'];
                            $lpts[$t2index] += $match['score-g'.$g.'t1'];
                        }
                        else {
                            $status[$t1index] .= 'E';
                            $status[$t2index] .= 'E';
                        }
                    }
                    else {
                        $status[$t1index] .= 'I';
                        $status[$t2index] .= 'I';
                    }
                }
                elseif ($match['score-g'.$g.'t1'] > $match['score-g'.$g.'t0']){
                    if ($match['score-g'.$g.'t1'] >= $maxpts){
                        if ( ($match['score-g'.$g.'t1'] - $match['score-g'.$g.'t0']) >= 2){
                            $status[$t2index] .= 'W';
                            $status[$t1index] .= 'L';
                            $wcnts[$t2index]++;
                            $lcnts[$t1index]++;
                        }
                        else {
                            $status[$t2index] .= 'E';
                            $status[$t1index] .= 'E';
                        }
                    }
                    else {
                        $status[$t2index] .= 'I';
                        $status[$t1index] .= 'I';
                    }
                }
                elseif ($match['score-g'.$g.'t0'] == $match['score-g'.$g.'t1']) {
                    $status[$t1index] .= 'T';
                    $status[$t2index] .= 'T';
                }
                else {
                    $status[$t1index] .= 'X';
                    $status[$t2index] .= 'X';
                }
            }

        }
        foreach($status as $sk => $s){
            $this->teams[$sk]->status = $s;
        }
        // calculate W:L ratio
        for($i=0;$i<count($this->teams);$i++){
            $this->teams[$i]->wins = $wcnts[$i];
            $this->teams[$i]->losses = $lcnts[$i];
            if($oppopts[$i] > 0){
                $ratio = floatval($teampts[$i])/floatval($oppopts[$i]);
            }
            else {
                $ratio = 0.0;
            }
            $this->teams[$i]->ratio = sprintf("%6.3f",$ratio);
        }
    }
    function renderTeams(){
        $this->buf .= '<div class="teams-container">';
        $this->buf .= '<table class="team-table">';

        $this->buf .= team::tableHeader();
        foreach($this->teams as $team){
            $this->buf .= $team->render();
        }
        $this->buf .= '</table>';
        $this->buf .= '</div><!-- end teams-container div -->';
    }
    function renderPoolGrid(){
        $this->buf .= '<div class="poolgrid-container">';
        $this->buf .= '<table class="poolgrid-table">';
        $this->buf .= '<tr>';
        $this->buf .= '<td>Ref</td>';
        $this->buf .= '<td>T1</td>';
        $this->buf .= '<td>T2</td>';
        $this->buf .= '<td>T1 Score</td>';
        $this->buf .= '<td>T2 Score</td>';
        $this->buf .= '<td>T1 Score</td>';
        $this->buf .= '<td>T2 Score</td>';
        $this->buf .= '</tr>';

        // need to check if this exists
        $m = 0;
        foreach($this->layout as $lo){
            $this->buf .= '<tr>';
            $this->buf .= '<td>';
            $this->buf .= '(' . $lo['ref'] . ')';
            $this->buf .= '</td>';
            $this->buf .= '<td>';
            $this->buf .= $lo['t0'];
            $this->buf .= '</td>';
            $this->buf .= '<td>';
            $this->buf .= $lo['t1'];
            $this->buf .= '</td>';
            for($g = 0;$g < $lo['numGames'];$g++){
                for($t=0;$t < 2; $t++){
                    $scorek='score-g'.$g.'t'.$t;
                    $score = $lo[$scorek];
                    $elementid='p'. ($this->id -1) .'m'.$m.'g'.$g.'t'.$t;
                    // need to come up with an id...
                    // p#m#t#g#
                    // pool, match (row), teamnum, gamenum
                    $this->buf .= '<td>';
                    //$this->buf .= '<form><input id="'.$elementid.'" type="text" size="6" onBlur="setScore(this,\'' . $elementid . '\',' . $p . ',' . $m . ',' . $g . ',' . $t . ')" ></input></form>';
                    $this->buf .= '<form><input id="'.$elementid.'" type="text" size="6" value="' . $score . '" onBlur="setScore(\'' . $elementid . '\')" ></input></form>';
                    $this->buf .= '</td>';
                }
            }
            $this->buf .= '</tr>';
            $m++;
        }

        $this->buf .= '</table>';
        $this->buf .= '</div><!-- end poolgrid-container div -->';
    }
    function setScore($m,$g,$t){
        print "pool::setScore($m,$g,$t)<br>";
    }
    function render(){
        $this->buf = '<div class="pool-container">';
        $this->buf .= '<div class="pool-container-left">';
        $this->buf .= "Pool: " . $this->id . "&nbsp;&nbsp;";
        $this->buf .= '<a href="?pool=' . $this->id . '&mode=addTeam">Add Team</a>';
        $this->renderTeams();
        $this->buf .= '</div><!-- end pool-container-left div -->';
        $this->buf .= '<div class="pool-container-right">';
        $this->renderPoolGrid();
        $this->buf .= '</div><!-- end pool-container-right div -->';
        $this->buf .= '</div><!-- end pool-container div -->';
        return $this->buf;
    }
    function addTeam($name = 'NA'){
        //print "Adding Team to Poolid:" . $this->id . "<br>";
        $this->teams[] = new team((count($this->teams)+1),$this->id,$name);
        $plg = new poolLayout(count($this->teams));
        $this->layout = $plg->layout;
    }
    // function html(){
    //     return $this->render();
    //     //return $this->buf;
    // }
    function setTeamName($teamnum,$name){
        print "pool::setTeamName($teamnum,$name)<br>";
        if (isset($this->teams[$teamnum])) $this->teams[$teamnum]->setName($name);
    }
}
////////////////////////////////////////////////////////////////////////////////
class tourn {
    function __construct(){
        $this->counter = 0;
        $this->sorted = array();
        $this->pools = array();
        $this->botBuf = '';
    }
    function __sleep(){
        return array('pools','sorted');
    }
    function __wakeup(){
        $this->sortResults();
    }
    function incrementCounter(){
        $this->counter++;
    }
    function render(){
        $this->buf = '';
        $this->render_top();
        $this->render_content();
        $this->render_bottom();

        return $this->buf;
    }
    function render_top(){
        $this->buf .= '<!doctype html>';
        $this->buf .= '
<html lang="en">
<head>
    <meta charset="utf-8">

    <title>The HTML5 Herald</title>
    <meta name="description" content="Blenders Tournament">
    <meta name="author" content="Aaron Martin">

    <link rel="stylesheet" href="./css/styles.css?v=1.0">
</head>
<body>
    <script src="js/scripts.js"></script>
    <div id="msg"></div>
    <div id="content-wrapper">
';

    }
    function render_bottom(){
        $this->buf .= '    </div> <!-- end content-wrapper -->
</body>
</html>';
    }
    function render_pools(){
        $this->buf .= '<div class="pools-container">';
        //$this->buf .= 'Pools Container: ' . count($this->pools);
        foreach($this->pools as $pool){
            $this->buf .= $pool->render();
        }
        $this->buf .= '</div> <!-- end pools-container div -->';
    }
    function render_sorted(){
        $this->buf .= '<div id="teams-sorted">';
        //$this->buf .= "Teams - Sorted<br>\n";
        $this->buf .= '<table id="teams-sorted">';

        $this->buf .= team::tableHeader();
        foreach($this->sorted as $st){
            $this->buf .= $st->render(false);
        }
        $this->buf .= '</table> <!-- end table -->';
        $this->buf .= '</div> <!-- end teams-sorted div -->';
    }
    function render_content(){
        //$this->buf .= 'Counter: ' . $this->counter;
        $this->buf .= '
    <a href="?mode=reset" class="reset-button">Clear All</a>
    <a href="?mode=addPool" class="add-pool-button">+ Pool</a>
    <a href="?mode=reload" class="reload-button">Reload</a>
';

        $this->render_pools();
        $this->render_sorted();
        $this->buf .= '
        <a href="?mode=testData" class="wide-button">Load Example</a>
        <a href="?mode=demoData" class="wide-button">Load Demo</a>
        <a href="?mode=export" class="add-pool-button">Export</a>
        ';

        $this->buf .= $this->botBuf;
        //print $this->botBuf;
    }
    function processGet(){
        //mode=setName&teamid=1&pool=2&name="Hey There"
        if(isset($_GET['mode'])){
            //print "tourn::mode is set<br>";
            $mode = $_GET['mode'];
            if ($mode == 'addPool'){
                $this->addPool();
            }
            if ($mode == 'addTeam' && isset($_GET['pool'])){
                $this->addTeam(intval($_GET['pool']));
            }
            if ($mode == 'setTeamName' && isset($_GET['name']) &&  isset($_GET['pool']) && isset($_GET['team'])){
                $name = $_GET['name'];
                $poolnum = intval($_GET['pool']) - 1;
                $teamnum = intval($_GET['team']) - 1;
                print "tourn::setTeamName($poolnum,$teamnum,$name) called<br>";
                $this->setTeamName($poolnum,$teamnum,$name);
            }
            if ($mode == 'setScore' && isset($_GET['elementid']) &&  isset($_GET['score'])){
                $elementid = $_GET['elementid'];
                //print "tourn::setScore($poolnum,$teamnum,$name) called<br>";
                //$this->setScore($poolnum,$teamnum,$name);
                $this->setScoreFromElem($elementid,$_GET['score']);
            }
            if ($mode == 'testData'){
                $this->loadTestData();
            }
            if ($mode == 'demoData'){
                $this->loadDemoData();
            }
            if ($mode == 'export'){
                $this->export();
            }
        }
    }
    function export(){
        $buf = '<pre>';
        foreach($this->pools as $p => $pool){
            $buf .= "\$this->addPool();\n";
            foreach($pool->teams as $t => $team){
                $pn = $p+1;
                $buf .= "\$this->addTeam($pn,'$team->name');\n";
            }
            foreach($pool->layout as $m => $match){
                for($g=0;$g<$match['numGames'];$g++){
                    for($t=0;$t<2;$t++){
                        $skey = 'p'.$p.'m'.$m.'g'.$g.'t'.$t;
                        $scorek='score-g'.$g.'t'.$t;
                        //$score = $match[$scorek];
                        $buf .= "\$this->setScoreFromElem('$skey',$match[$scorek]);\n";
                    }
                }
            }
        }
        $buf .= "</pre>";
        $this->botBuf = $buf;
    }
    function loadDemoData(){

    }
    function loadTestData(){
        $this->addPool();
        $this->addTeam(1,'Pineapple Diggers');
        $this->addTeam(1,'Groovy Guavas');
        $this->addTeam(1,'Dominating Dates');
        $this->addTeam(1,'Bananas gone Bananas');
        $this->setScoreFromElem('p0m0g0t0',23);
        $this->setScoreFromElem('p0m0g0t1',25);
        $this->setScoreFromElem('p0m0g1t0',18);
        $this->setScoreFromElem('p0m0g1t1',25);
        $this->setScoreFromElem('p0m1g0t0',25);
        $this->setScoreFromElem('p0m1g0t1',16);
        $this->setScoreFromElem('p0m1g1t0',25);
        $this->setScoreFromElem('p0m1g1t1',14);
        $this->setScoreFromElem('p0m2g0t0',8);
        $this->setScoreFromElem('p0m2g0t1',25);
        $this->setScoreFromElem('p0m2g1t0',15);
        $this->setScoreFromElem('p0m2g1t1',25);
        $this->setScoreFromElem('p0m3g0t0',25);
        $this->setScoreFromElem('p0m3g0t1',23);
        $this->setScoreFromElem('p0m3g1t0',27);
        $this->setScoreFromElem('p0m3g1t1',25);
        $this->setScoreFromElem('p0m4g0t0',25);
        $this->setScoreFromElem('p0m4g0t1',18);
        $this->setScoreFromElem('p0m4g1t0',15);
        $this->setScoreFromElem('p0m4g1t1',25);
        $this->setScoreFromElem('p0m5g0t0',25);
        $this->setScoreFromElem('p0m5g0t1',12);
        $this->setScoreFromElem('p0m5g1t0',18);
        $this->setScoreFromElem('p0m5g1t1',25);
        $this->addPool();
        $this->addTeam(2,'Strawberry Spikers');
        $this->addTeam(2,'Blueberry Blockers');
        $this->addTeam(2,'Blackberry Blasters');
        $this->addTeam(2,'Rasberry');
        $this->addTeam(2,'Papaya');
        $this->setScoreFromElem('p1m0g0t0',15);
        $this->setScoreFromElem('p1m0g0t1',8);
        $this->setScoreFromElem('p1m0g1t0',9);
        $this->setScoreFromElem('p1m0g1t1',15);
        $this->setScoreFromElem('p1m1g0t0',20);
        $this->setScoreFromElem('p1m1g0t1',18);
        $this->setScoreFromElem('p1m1g1t0',15);
        $this->setScoreFromElem('p1m1g1t1',9);
        $this->setScoreFromElem('p1m2g0t0',15);
        $this->setScoreFromElem('p1m2g0t1',3);
        $this->setScoreFromElem('p1m2g1t0',15);
        $this->setScoreFromElem('p1m2g1t1',8);
        $this->setScoreFromElem('p1m3g0t0',15);
        $this->setScoreFromElem('p1m3g0t1',17);
        $this->setScoreFromElem('p1m3g1t0',15);
        $this->setScoreFromElem('p1m3g1t1',11);
        $this->setScoreFromElem('p1m4g0t0',15);
        $this->setScoreFromElem('p1m4g0t1',11);
        $this->setScoreFromElem('p1m4g1t0',15);
        $this->setScoreFromElem('p1m4g1t1',9);
        $this->setScoreFromElem('p1m5g0t0',15);
        $this->setScoreFromElem('p1m5g0t1',6);
        $this->setScoreFromElem('p1m5g1t0',8);
        $this->setScoreFromElem('p1m5g1t1',15);
        $this->setScoreFromElem('p1m6g0t0',14);
        $this->setScoreFromElem('p1m6g0t1',16);
        $this->setScoreFromElem('p1m6g1t0',15);
        $this->setScoreFromElem('p1m6g1t1',6);
        $this->setScoreFromElem('p1m7g0t0',11);
        $this->setScoreFromElem('p1m7g0t1',15);
        $this->setScoreFromElem('p1m7g1t0',15);
        $this->setScoreFromElem('p1m7g1t1',13);
        $this->setScoreFromElem('p1m8g0t0',11);
        $this->setScoreFromElem('p1m8g0t1',15);
        $this->setScoreFromElem('p1m8g1t0',15);
        $this->setScoreFromElem('p1m8g1t1',13);
        $this->setScoreFromElem('p1m9g0t0',15);
        $this->setScoreFromElem('p1m9g0t1',13);
        $this->setScoreFromElem('p1m9g1t0',12);
        $this->setScoreFromElem('p1m9g1t1',15);
        $this->addPool();
        $this->addTeam(3,'Oranges');
        $this->addTeam(3,'Apples');
        $this->addTeam(3,'Lemons');
        $this->setScoreFromElem('p2m0g0t0',25);
        $this->setScoreFromElem('p2m0g0t1',17);
        $this->setScoreFromElem('p2m0g1t0',18);
        $this->setScoreFromElem('p2m0g1t1',25);
        $this->setScoreFromElem('p2m1g0t0',28);
        $this->setScoreFromElem('p2m1g0t1',26);
        $this->setScoreFromElem('p2m1g1t0',18);
        $this->setScoreFromElem('p2m1g1t1',25);
        $this->setScoreFromElem('p2m2g0t0',18);
        $this->setScoreFromElem('p2m2g0t1',25);
        $this->setScoreFromElem('p2m2g1t0',16);
        $this->setScoreFromElem('p2m2g1t1',25);
    }
    function setTeamName($poolnum,$teamnum,$name){
        print "tourn::setTeamName($poolnum,$teamnum,$name)<br>";
        if (isset($this->pools[$poolnum])) $this->pools[$poolnum]->setTeamName($teamnum,$name);
    }
    function setScore($p,$m,$g,$t){
        print "tourn::setScore($p,$m,$g,$t)<br>";
        if (isset($this->pools[$p])) $this->pools[$p]->setScore($m,$t,$g);
    }
    function setScoreFromElem($elem,$score){
        //print "tourn::setScoreFromElem($elem)<br>";
        foreach($this->pools as $p => &$pool){
            foreach($pool->layout as $m => &$match){
                for($g = 0; $g < $match['numGames']; $g++){
                    for($t=0;$t<2;$t++){
                        $elementid='p'.$p.'m'.$m.'g'.$g.'t'.$t;
                        if($elem == $elementid){
                            $scorek='score-g'.$g.'t'.$t;
                            $match[$scorek] = $score;
                            //print "$elem Found match!!  Just need to set score now<br>";
                        }
                    }
                }
            }
        }
        //if (isset($this->pools[$p])) $this->pools[$p]->setScore($m,$t,$g);
    }
    function addPool(){
        //print "Adding Pool<br>\n";
        $id = count($this->pools) + 1;
        $this->pools[] = new pool($id);
    }
    function addTeam($poolnum,$name = 'NA'){
        $poolnum--;
        if (! isset($this->pools[$poolnum])) return false;
        // validate that $poolnum is viable
        $this->pools[$poolnum]->addTeam($name);
        return true;
    }
    // function html(){
    //     $this->render();
    //     return $this->buf;
    // }
    function sortResults(){
        $this->sorted = array();
        foreach($this->pools as $pool){
            $this->sorted = array_merge($this->sorted,$pool->teams);
        }
        usort($this->sorted,"my_cmp");


        // sort by wins - highest
        // sort by losses - lowest
        // any ties (W-L same for teams) are settled by point differential

        // This display will basically be a repeat of all the team list display, just merged and sorted

    }
}
function my_cmp($a,$b){
    if($a->ratio == $b->ratio) return 0;
    return ($a->ratio > $b->ratio) ? -1 : 1;

}
 ?>
