<!DOCTYPE html>
<!-- SPDX-License-Identifier: GPL-3.0-or-later -->
<html lang="en">
<head>
    <title>NetData Dashboard</title>
    <meta name="application-name" content="netdata">

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <meta property="og:locale" content="en_US" />
    <meta property="og:image" content="https://cloud.githubusercontent.com/assets/2662304/22945737/e98cd0c6-f2fd-11e6-96f1-5501934b0955.png"/>
    <meta property="og:url" content="http://my-netdata.io/"/>
    <meta property="og:type" content="website"/>
    <meta property="og:site_name" content="netdata"/>
    <meta property="og:title" content="netdata - real-time performance monitoring, done right!"/>
    <meta property="og:description" content="Stunning real-time dashboards, blazingly fast and extremely interactive. Zero configuration, zero dependencies, zero maintenance." />
    
</head>
<script>
// this section has to appear before loading dashboard.js
// Select a theme.
// uncomment on of the two themes:
// var netdataTheme = 'default'; // this is white
var netdataTheme = 'slate'; // this is dark
</script>

<!--
    Load dashboard.js
    to host this HTML file on your web server,
    you have to load dashboard.js from the netdata server.
    So, pick one the two below
    If you pick the first, set the server name/IP.
    The second assumes you host this file on /usr/share/netdata/web
    and that you have chown it to be owned by netdata:netdata
-->
<?php
	$jsonData = file_get_contents("../config/netdata.json");
	$jsonData = trim($jsonData, "\0");
	$netdataObjs = json_decode( $jsonData );
	echo '<script type="text/javascript" src="' . $netdataObjs[0]->url . '/dashboard.js"></script>';
?>

<script>
// Set options for TV operation
// This has to be done, after dashboard.js is loaded
// destroy charts not shown (lowers memory on the browser)
NETDATA.options.current.destroy_on_hide = true;
// set this to false, to always show all dimensions
NETDATA.options.current.eliminate_zero_dimensions = true;
// lower the pressure on this browser
NETDATA.options.current.concurrent_refreshes = false;
// if the tv browser is too slow (a pi?)
// set this to false
NETDATA.options.current.parallel_refresher = true;
// always update the charts, even if focus is lost
// NETDATA.options.current.stop_updates_when_focus_is_lost = false;
// Since you may render charts from many servers and any of them may
// become offline for some time, the charts will break.
// This will reload the page every RELOAD_EVERY minutes
var RELOAD_EVERY = 5;
setTimeout(function(){
    location.reload();
}, RELOAD_EVERY * 60 * 1000);
</script>
<body>

<div id='servers' style="width: 100%; text-align: center; display: inline-block;">

    <div style="width: 100%; height: 24vh; text-align: center; display: inline-block;">
        <div style="width: 100%; height: 15px; text-align: center; display: inline-block;">
            <b>CPU On all servers</b>
        </div>
        <div style="width: 100%; height: calc(100% - 15px); text-align: center; display: inline-block;">
            <br/>
<?php
	foreach( $netdataObjs as $key => $nd )
	{
        print'<div data-netdata="system.cpu" 
                    data-host="' . $nd->url . '" 
                    data-title="CPU usage of ' . $nd->name . '"
                    data-chart-library="dygraph"
                    data-width="49%" 
                    data-height="100%" 
                    data-after="-300" 
                    data-dygraph-valuerange="[0, 100]" 
                    ></div>';
	}
?>
        </div>
    </div>


    <div style="width: 100%; height: 24vh; text-align: center; display: inline-block;">
        <div style="width: 100%; height: 15px; text-align: center; display: inline-block;">
            <b>Disk I/O on all servers</b>
        </div>
        <div style="width: 100%; height: calc(100% - 15px); text-align: center; display: inline-block;">
<?php
    foreach( $netdataObjs as $key => $nd )
    {
        print '<div data-netdata="system.io"
                    data-host="' . $nd->url . '" 
                    data-common-max="io"
                    data-common-min="io"
                    data-title="I/O on ' . $nd->name . '"
                    data-chart-library="dygraph"
                    data-width="49%"
                    data-height="100%"
                    data-after="-300"
                    ></div>';
    }
?>
        </div>
    </div>


    <div style="width: 100%; height: 24vh; text-align: center; display: inline-block;">
        <div style="width: 100%; height: 15px; text-align: center; display: inline-block;">
            <b>IPv4 traffic on all servers</b>
        </div>
        <div style="width: 100%; height: calc(100% - 15px); text-align: center; display: inline-block;">
<?php
    foreach( $netdataObjs as $key => $nd )
    {
        print '<div data-netdata="system.net"
                    data-host="' . $nd->url . '"
                    data-common-max="traffic"
                    data-common-min="traffic"
                    data-title="Network traffic on ' . $nd->name . '"
                    data-chart-library="dygraph"
                    data-width="49%"
                    data-height="100%"
                    data-after="-300"
                    ></div>';
    }
?>
        </div>
    </div>

    <div style="width: 100%; height: 23vh; text-align: center; display: inline-block;">
        <div style="width: 100%; height: 15px; text-align: center; display: inline-block;">
            <b>Netdata statistics on all servers</b>
        </div>
        <div style="width: 100%; max-height: calc(100% - 15px); text-align: center; display: inline-block;">
<?php
    foreach( $netdataObjs as $key => $nd )
    {
       print '<div style="width: 49%; height:100%; align: center; display: inline-block;">
                ' . $nd->name . '
                <br/>
                <div data-netdata="netdata.requests"
                        data-host="' . $nd->url . '"
                        data-common-max="netdata-requests"
                        data-decimal-digits="0"
                        data-title="Chart Refreshes/s"
                        data-chart-library="gauge"
                        data-width="20%"
                        data-height="100%"
                        data-after="-300"
                        data-points="300"
                        ></div>
                <div data-netdata="netdata.clients"
                        data-host="' . $nd->url . '"
                        data-common-max="netdata-clients"
                        data-decimal-digits="0"
                        data-title="Sockets"
                        data-chart-library="gauge"
                        data-width="20%"
                        data-height="100%"
                        data-after="-300"
                        data-points="300"
                        data-colors="#AA5500"
                        ></div>
                <div data-netdata="netdata.net"
                        data-dimensions="in"
                        data-common-max="netdata-net-in"
                        data-decimal-digits="0"
                        data-host="' . $nd->url . '"
                        data-title="Requests Traffic"
                        data-chart-library="easypiechart"
                        data-width="15%"
                        data-height="100%"
                        data-after="-300"
                        data-points="300"
                        ></div>
                <div data-netdata="netdata.net"
                        data-dimensions="out"
                        data-common-max="netdata-net-out"
                        data-decimal-digits="0"
                        data-host="' . $nd->url . '"
                        data-title="Chart Data Traffic"
                        data-chart-library="easypiechart"
                        data-width="15%"
                        data-height="100%"
                        data-after="-300"
                        data-points="300"
                        ></div>
			</div>';
    }
?>
        </div>
    </div>
</div>
</body>
</html>
