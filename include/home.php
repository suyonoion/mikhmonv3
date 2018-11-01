<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
if(!isset($_SESSION["mikhmon"])){
  header("Location:../admin.php?id=login");
}else{
// load session MikroTik
$session = $_GET['session'];

// load config
include('./config.php');
$iphost=explode('!',$data[$session][1])[1]; 
$userhost=explode('@|@',$data[$session][2])[1];
$passwdhost=explode('#|#',$data[$session][3])[1]; 
$iface=explode('(',$data[$session][8])[1];  
$maxtx=explode(')',$data[$session][9])[1]; 
$maxrx=explode('=',$data[$session][10])[1];

// routeros api

include_once('../lib/routeros_api.class.php');
include_once('../lib/formatbytesbites.php');
$API = new RouterosAPI();
$API->debug = false;
$API->connect( $iphost, $userhost, decrypt($passwdhost));


// get MikroTik system clock
$getclock = $API->comm("/system/clock/print");
$clock = $getclock[0];

// get system resource MikroTik
$getresource = $API->comm("/system/resource/print");
$resource = $getresource[0];
// get routeboard info
$getrouterboard = $API->comm("/system/routerboard/print");
$routerboard = $getrouterboard[0];

// get & counting hotspot active
  $counthotspotactive = $API->comm("/ip/hotspot/active/print", array(
    "count-only" => ""));
  if($counthotspotactive < 2 ){$hunit = "item";
  }elseif($counthotspotactive > 1){
  $hunit = "items";
  }
// get & counting hotspot users
  $countallusers = $API->comm("/ip/hotspot/user/print", array(
    "count-only" => ""));
  if($countallusers < 2 ){$uunit = "item";
  }elseif($countallusers > 1){
  $uunit = "items";}

// get traffic ether
  $getinterface = $API->comm("/interface/print");
  $interface = $getinterface[$iface-1]['name'];
  $getinterfacetraffic = $API->comm("/interface/monitor-traffic", array(
    "interface" => "$interface",
    "once" => "",
    ));
  $tx = formatBites($getinterfacetraffic[0]['tx-bits-per-second'],1);
  $rx = formatBites($getinterfacetraffic[0]['rx-bits-per-second'],1);
  if($maxtx == "" || $maxtx == "0"){$mxtx = formatBites(100000000,0); $maxtx = "100000000";}else{$mxtx = formatBites($maxtx,0); $maxtx = $maxtx;}
  if($maxrx == "" || $maxrx == "0"){$mxrx = formatBites(100000000,0); $maxrx = "100000000";}else{$mxrx = formatBites($maxrx,0); $maxrx = $maxrx;}
}
?>
    
<div id="reloadHome">

    <div class="row">
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
            <div class="box-group-icon"><i class="fa fa-calendar"></i></div>
              <div class="box-group-area">
                <span >System Date & Time<br>
                <?php echo $clock['time'];?> <?php echo $clock['date'];?><br>
                Uptime <?php echo formatDTM($resource['uptime']);?></span>
              </div>
            </div>
          </div>
        </div>
      <div class="col-4">
        <div class="box bmh-75 box-bordered">
          <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-info-circle"></i></div>
              <div class="box-group-area">
                <span >
        Board Name : <?php echo $resource['board-name'];?><br/>
        Model : <?php echo $routerboard['model']?><br/>
        Router OS : <?php echo $resource['version']?>
                </span>
              </div>
            </div>
          </div>
        </div>
    <div class="col-4">
      <div class="box bmh-75 box-bordered">
        <div class="box-group">
          <div class="box-group-icon"><i class="fa fa-server"></i></div>
              <div class="box-group-area">
			  <div class="baris">
  <div class="kolom" style="font-size:11px;">
  <ul style="padding-left:10px;">
    <li>Free Memory <?php echo formatBytes($resource['free-memory'],2)?></li>
	<li>Free HDD <?php echo formatBytes($resource['free-hdd-space'],2)?></li>
	</ul>
  </div>
  <div class="kolom" align="center">
  <div style="border-left:1px solid #fff;margin-left:7px">
    <div align="center">CPU Load</div>
    <div align="center"><span style="font-size:36px;font-weight:bold;"><?php echo $resource['cpu-load']?><span style="font-size:12px;"> %</span> </span></div>
	</div>
  </div>
</div>
                </div>
              </div>
            </div>
          </div>           
        </div>

        <div class="row">
          <div class="col-8">
            <div class="card">
              <div class="card-header"><h3><i class="fa fa-wifi"></i> Hotspot</h3></div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-3 col-box-6">
                      <div class="box bg-primary bmh-75">
                        <a href="./app.php?hotspot=active&session=<?php echo $session;?>">
                          <div>
                            <h1><?php echo $counthotspotactive;?>
                              <span style="font-size: 15px;"><?php echo $hunit;?></span>
                            </h1>
                          </div>
                          <div>
                            <i class="fa fa-laptop"></i> Hotspot Active
                          </div>
                        </a>
                      </div>
                    </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-success bmh-75">
                      <a href="./app.php?hotspot=users&profile=all&session=<?php echo $session;?>">
                        <div>
                            <h1><?php echo $countallusers;?>
                              <span style="font-size: 15px;"><?php echo $uunit;?></span>
                            </h1>
                          </div>
                          <div>
                            <i class="fa fa-users"></i> Hotspot Users
                          </div>
                      </a>
                    </div>
                  </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-warning bmh-75">
                      <a href="./app.php?hotspot-user=add&session=<?php echo $session;?>">
                        <div>
                          <h1><i class="fa fa-user-plus"></i>
                              <span style="font-size: 15px;">Add</span>
                          </h1>
                        </div>
                        <div>
                            <i class="fa fa-user-plus"></i> Hotspot User
                        </div>
                      </a>
                    </div>
                  </div>
                  <div class="col-3 col-box-6">
                    <div class="box bg-danger bmh-75">
                      <a href="./app.php?hotspot-user=generate&session=<?php echo $session;?>">
                        <div>
                          <h1><i class="fa fa-user-plus"></i>
                              <span style="font-size: 15px;">Generate</span>
                          </h1>
                        </div>
                        <div>
                            <i class="fa fa-user-plus"></i> Hotspot User
                        </div>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card">
              <div class="card-header"><h3><i class="fa fa-area-chart"></i> Traffic</h3></div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-12">
                      <div class="box bmh-75 box-bordered">
                        <div style="margin-bottom: 10px;"><h3><b>Interface  :  </b><font color="#33cc33"> <?php echo $interface;?></font></h3><hr>
						<div class="baris">
<div class="kolom"><span>TX</span></div>
<div class="kolom"><span>RX</span></div>
</div>
						</div>
                            
<center>
<div class="baris">
<div class="kolom">					
<!-- coba lingkaran -->
<div id="trafik-tx" data-percent="<?php echo $getinterfacetraffic[0]['tx-bits-per-second']/$maxtx*100;?>">
<p class="progress-description"><?php echo $tx." /<br> ".$mxtx;?></p>
<p style="font-size: 17px;color:#ffc107;"><b>
<?php $txpersen = $getinterfacetraffic[0]['tx-bits-per-second']/$maxtx*100;echo round($txpersen);?> %</b></p>
</div>
<script type="text/javascript">
var el = document.getElementById('trafik-tx'); // get canvas

var options = {
    percent:  el.getAttribute('data-percent') || 25,
    size: el.getAttribute('data-size') || 150,
    lineWidth: el.getAttribute('data-line') || 10,
    rotate: el.getAttribute('data-rotate') || 0
}

var canvas = document.createElement('canvas');
    
if (typeof(G_vmlCanvasManager) !== 'undefined') {
    G_vmlCanvasManager.initElement(canvas);
}

var ctx = canvas.getContext('2d');
canvas.width = canvas.height = options.size;

el.appendChild(canvas);

ctx.translate(options.size / 2, options.size / 2); // change center
ctx.rotate((-1 / 2 + options.rotate / 180) * Math.PI); // rotate -90 deg

//imd = ctx.getImageData(0, 0, 240, 240);
var radius = (options.size - options.lineWidth) / 2;

var drawCircle = function(color, lineWidth, percent) {
		percent = Math.min(Math.max(0, percent || 1), 1);
		ctx.beginPath();
		ctx.arc(0, 0, radius, 0, Math.PI * 2 * percent, false);
		ctx.strokeStyle = color;
        ctx.lineCap = 'round'; // butt, round or square
		ctx.lineWidth = lineWidth
		ctx.stroke();
};

drawCircle('#ffffff9e', options.lineWidth, 100 / 100);
drawCircle('#33cc33', options.lineWidth, options.percent / 100);
</script>
</div>
<div class="kolom">
<!-- coba lingkaran -->
<div id="trafik-rx" data-percent="<?php echo $getinterfacetraffic[0]['rx-bits-per-second']/$maxrx*100;?>">
<p class="progress-description"><?php echo $rx." /<br> ".$mxrx;?></p>
<p style="font-size: 17px;color:#ffc107;"><b>
<?php $rxpersen = $getinterfacetraffic[0]['rx-bits-per-second']/$maxrx*100;echo round($rxpersen);?> %</b></p>
</div>
<script type="text/javascript">
var el = document.getElementById('trafik-rx'); // get canvas

var options = {
    percent:  el.getAttribute('data-percent') || 25,
    size: el.getAttribute('data-size') || 150,
    lineWidth: el.getAttribute('data-line') || 10,
    rotate: el.getAttribute('data-rotate') || 0
}

var canvas = document.createElement('canvas');
    
if (typeof(G_vmlCanvasManager) !== 'undefined') {
    G_vmlCanvasManager.initElement(canvas);
}

var ctx = canvas.getContext('2d');
canvas.width = canvas.height = options.size;

el.appendChild(canvas);

ctx.translate(options.size / 2, options.size / 2); // change center
ctx.rotate((-1 / 2 + options.rotate / 180) * Math.PI); // rotate -90 deg

//imd = ctx.getImageData(0, 0, 240, 240);
var radius = (options.size - options.lineWidth) / 2;

var drawCircle = function(color, lineWidth, percent) {
		percent = Math.min(Math.max(0, percent || 1), 1);
		ctx.beginPath();
		ctx.arc(0, 0, radius, 0, Math.PI * 2 * percent, false);
		ctx.strokeStyle = color;
        ctx.lineCap = 'round'; // butt, round or square
		ctx.lineWidth = lineWidth
		ctx.stroke();
};

drawCircle('#ffffff9e', options.lineWidth, 100 / 100);
drawCircle('#33cc33', options.lineWidth, options.percent / 100);
</script>
</div>
</div>
</center>


                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <div class="col-4">
            <div class="card">
              <div class="card-header">
                <h3><i class="fa fa-align-justify"></i> Hotspot Log</h3></div>
                  <div class="card-body">
                    <div class="bg-dark" style="overflow: auto; width:100%; height:395px; font-size:11px; border:0;" disabled>
<!-- Start Rainbow Table -->				
	<table>
		<thead>
			<tr>
				<th style="text-align:center;">Notif</th>
				<th style="text-align:center;">Times</th>
				<th style="text-align:center;">Messages</th>
			</tr>
		</thead>
		<tbody>
<?php
// move hotspot log to disk
  $getlogging = $API->comm("/system/logging/print", array(
    "?prefix" => "->",));
  $logging = $getlogging[0];
  if($logging['prefix'] == "->"){}else{
  $API->comm("/system/logging/add", array("action" => "disk","prefix" => "->","topics" => "hotspot,info,debug",));
  }
// get hotspot log
  $getlog = $API->comm("/log/print", array(
    "?topics" => "hotspot,info,debug",));
  $log = array_reverse($getlog);
  $TotalReg = count($getlog);

$cari_masuk = "trying to log in";
$cari_keluar = "logged out";
$cari_gagal_masuk = "login failed";
for ($i=0; $i<$TotalReg; $i++){
	echo "<tr>";
	echo "<td style='width:25px;white-space:nowrap;'><i>";
			if (strpos($log[$i]['message'], $cari_masuk) !== false) {echo "<img src='/img/masuk.png' width='25px' height='25px' style='vertical-align: middle;'/><span style='vertical-align: middle;'> Masuk</span>";} 
			if (strpos($log[$i]['message'], $cari_keluar) !== false){echo "<img src='/img/keluar.png' width='25px' height='25px' style='vertical-align: middle;'/><span style='vertical-align: middle;'> Keluar</span>";}
			if (strpos($log[$i]['message'], $cari_gagal_masuk) !== false){echo "<img src='/img/gagal.png' width='25px' height='25px' style='vertical-align: middle;'/><span style='vertical-align: middle;'> Gagal</span>";}
	echo "</i></td>";
	echo "<td>" . $log[$i]['time'];echo "</td>";
	echo "<td>" . $log[$i]['message'];echo "</td>";
	echo "</tr>";
	}
?>
						</tbody>
	</table>
	<!-- End Rainbow Table -->
                  </div>
                </div>
              </div>
            </div>
</div>
</div>


