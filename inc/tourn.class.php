<?php
/*

function t1,t2,ref,games

1,3,4,2
2,4,3,2
1,4,2,2
2,3,4,2
3,4,1,2
1,2,3,2

Match  Ref   Team    vs     Team
1  (4)  1      vs      3
2  (3)  2      vs      4
3  (2)  1      vs      4
4  (4)  2      vs      3
5  (1)  3      vs      4
6  (3)  1      vs      2

*/
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
    }
    function __sleep(){
        return array('teams','id');
    }
    function renderTeams(){
        $this->buf .= '<div class="table-container">';
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
        $this->buf .= '</div><!-- end table-container div -->';
    }
    function render(){
        $this->buf = '<div class="pool-container">';
        $this->buf .= "Pool id: " . $this->id . "&nbsp;&nbsp;";
        $this->buf .= '<a href="?pool=' . $this->id . '&mode=addTeam">Add Team</a>';
        $this->renderTeams();
        $this->buf .= '</div><!-- end pool-container div -->';
        return $this->buf;
    }
    function addTeam(){
        //print "Adding Team to Poolid:" . $this->id . "<br>";
        $this->teams[] = new team((count($this->teams)+1),$this->id);
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
        }
    }
    function setTeamName($poolnum,$teamnum,$name){
        print "tourn::setTeamName($poolnum,$teamnum,$name)<br>";
        if (isset($this->pools[$poolnum])) $this->pools[$poolnum]->setTeamName($teamnum,$name);
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
