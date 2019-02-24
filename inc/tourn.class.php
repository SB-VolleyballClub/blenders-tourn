<?php
/*

function addMatchDef,numTeams,t0,t1,ref,numGames

$this->addMatchDef(4,1,3,4,2);
$this->addMatchDef(4,2,4,3,2);
$this->addMatchDef(4,1,4,2,2);
$this->addMatchDef(4,2,3,4,2);
$this->addMatchDef(4,3,4,1,2);
$this->addMatchDef(4,1,2,3,2);

Match  Ref   Team    vs     Team
1  (4)  1      vs      3
2  (3)  2      vs      4
3  (2)  1      vs      4
4  (4)  2      vs      3
5  (1)  3      vs      4
6  (3)  1      vs      2

*/
class poolLayout {
    function __construct($numTeams){
        $this->layout = array();
        if ($numTeams == 4){
            $this->addMatchDef(4,1,3,4,2);
            $this->addMatchDef(4,2,4,3,2);
            $this->addMatchDef(4,1,4,2,2);
            $this->addMatchDef(4,2,3,4,2);
            $this->addMatchDef(4,3,4,1,2);
            $this->addMatchDef(4,1,2,3,2);
        }
    }
    function addMatchDef($numTeams,$t0,$t1,$ref,$numGames){
        // skipped the numTeams option for now...
        // need to add a scores in here
        $tmpa = array('t0' => $t0,'t1' => $t1, 'ref' => $ref, 'numGames' => $numGames );
        for($g=0;$g<$tmpa['numGames'];$g++){
            for($t=0;$t<2;$t++){
                $scorek='score-g'.$g.'t'.$t;
                $tmpa[$scorek] = '0';
            }
        }
        $this->layout[] = $tmpa;
    }
}
class team {
    function __construct($id,$poolid){
        $this->id = $id;
        $this->poolid = $poolid;
        $this->name = 'NA';
        $this->wins = 0;
        $this->losses = 0;
        $this->winRatio = 0.0;
    }
    function __sleep(){
        return array('wins','losses','winRatio','name','id','poolid');
    }
    function render(){
        $elementid = 'p'.$this->poolid.'t'.$this->id;
        $this->buf ='<tr>';
        $this->buf .='<td>';
        $this->buf .= $this->id;
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= '<form><input id="' . $elementid . '" type="text" value="' . $this->name . '" onBlur="setTeamName(this,\'' . $elementid . '\',' . $this->poolid . ',' . $this->id . ')"></input></form>';
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= $this->wins;
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= $this->losses;
        $this->buf .='</td>';
        $this->buf .='<td>';
        $this->buf .= $this->winRatio;
        $this->buf .='</td>';
        $this->buf .='</tr>';

        return $this->buf;
    }
    function html(){
        $this->render();
        return $this->buf;
    }
    function setName($name){
        print "team::setName($name)<br>";
        $this->name = $name;
    }
}
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
    function updateWinLoss(){
        // consider assigning elementid in has????
        //print "updating win loss info<br>\n";
        // we are at the pool level, so we can just go through the layout grid
        // Just sweep through and assign...  maybe build up a temp buff and then assign?
        // maybe flag issues as well? (incomplete, unrealistic scores 8-28, 8-10)
        foreach($this->layout as $k => &match){


        }
    }
    function renderTeams(){
        $this->buf .= '<div class="teams-container">';
        $this->buf .= '<table class="team-table">';
        $this->buf .= '<tr>';
        $this->buf .= '<td>#</td>';
        $this->buf .= '<td>Name</td>';
        $this->buf .= '<td>W</td>';
        $this->buf .= '<td>L</td>';
        $this->buf .= '<td>W/L Ratio</td>';
        $this->buf .= '</tr>';
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
    function addTeam(){
        //print "Adding Team to Poolid:" . $this->id . "<br>";
        $this->teams[] = new team((count($this->teams)+1),$this->id);
        $plg = new poolLayout(count($this->teams));
        $this->layout = $plg->layout;
    }
    function html(){
        return $this->render();
        //return $this->buf;
    }
    function setTeamName($teamnum,$name){
        print "pool::setTeamName($teamnum,$name)<br>";
    if (isset($this->teams[$teamnum])) $this->teams[$teamnum]->setName($name);
    }
}
class tourn {
    function __construct(){
        $this->counter = 0;
        $this->pools = array();
    }
    function __sleep(){
        return array('pools');
    }
    function incrementCounter(){
        $this->counter++;
    }
    function render(){
        $this->buf = '';
        $this->top();
        $this->content();
        $this->bottom();
        return $this->buf;
    }
    function top(){
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
    function bottom(){
        $this->buf .= '    </div> <!-- end content-wrapper -->
</body>
</html>';
    }
    function content(){
        $this->buf .= 'Counter: ' . $this->counter;
        $this->buf .= '
    <a href="?mode=reset">Reset</a>
    <a href="?mode=addPool">Add Pool</a>
    <a href="?mode=reload">Reload</a>
';
        $this->buf .= '<div class="pools-container">';
        $this->buf .= 'Pools Container: ' . count($this->pools);
        foreach($this->pools as $pool){
            $this->buf .= $pool->render();
        }
        $this->buf .= '</div> <!-- end pools-container div';
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
                print "tourn::setScore($poolnum,$teamnum,$name) called<br>";
                //$this->setScore($poolnum,$teamnum,$name);
                $this->setScoreFromElem($elementid,$_GET['score']);
            }
        }
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
        print "tourn::setScoreFromElem($elem)<br>";
        foreach($this->pools as $p => &$pool){
            foreach($pool->layout as $m => &$match){
                for($g = 0; $g < $match['numGames']; $g++){
                    for($t=0;$t<2;$t++){
                        $elementid='p'.$p.'m'.$m.'g'.$g.'t'.$t;
                        if($elem == $elementid){
                            $scorek='score-g'.$g.'t'.$t;
                            $match[$scorek] = $score;
                            print "$elem Found match!!  Just need to set score now<br>";
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
    function addTeam($poolnum){
        $poolnum--;
        if (! isset($this->pools[$poolnum])) return false;
        // validate that $poolnum is viable
        $this->pools[$poolnum]->addTeam();
        return true;
    }
    function html(){
        $this->render();
        return $this->buf;
    }
}
 ?>
