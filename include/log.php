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

// hide all error
error_reporting(0);
if(!isset($_SESSION["mikhmon"])){
  header("Location:../admin.php?id=login");
}else{

  $getlog = $API->comm("/log/print", array(
	  "?topics" => "hotspot,info,debug"));
	$log = array_reverse($getlog);
	$TotalReg = count($getlog);
}
?>
<div class="row">
<div class="col-12">
<div class="card">
<div class="card-header">
    <h3><i class=" fa fa-align-justify"></i>  Log</h3>
</div>
<div class="card-body">
       
<div style="max-width: 350px;">
     <input id="filterTable" type="text" class="form-control" placeholder="Search.."> 
</div>
<div style="padding: 5px; max-height: 75vh;" class="mr-t-10 overflow">
<table class="table table-sm" id="dataTable" >
	<tbody>
<?php
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
</div>
</div>
</div>
</div>
</div>
