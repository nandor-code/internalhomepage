<?php

$config = array(
		'host' => 'http://san-mbp-001.ntsj.com:5005'
);

$set = $_GET['setstate'];
$get = $_GET['getstate'];

if( isset($set) )
{
	header("Content-Type: application/json");
	
	$s = file_put_contents( "/tmp/house_music", $set);

	$response = array(
   	     'success' => $s,
	    );
	
	echo json_encode($response);
	
	return;
}

if( isset($get) )
{
	header("Content-Type: application/json");
	
	$json = json_decode( file_get_contents( $config['host'] . "/" . $get . "/state" ) );
	$s = $json->playbackState == "PLAYING";
	
	$response = array(
   	     'playing' => $s,
	    );
	
	echo json_encode($response);
	
	return;
}
?>

<html lang="en">
	<title>Sonos Controller</title>
	<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/meyer-reset/2.0/reset.min.css'><script src='https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js'></script>
	
    <link id="sonosStyle" rel='stylesheet' href='/css/sonos.css'>
	<h1 class="logo">SONOS</h1>
	<body>
<?php

	$sonosJson = json_decode( file_get_contents( $config['host'] . "/zones" ) );

	echo "<ul class='tree'>\n";
	$cb = 1;
	echo '<li>';
	echo '<input type="checkbox" checked="checked" id="c' . $cb .'" />';
	echo '<label class="tree_label" for="c'.$cb.'">House Zones</label>';
	print "<ul>\n";
	foreach ( $sonosJson as $key => $c )
	{
		$playing = $c->coordinator->state->playbackState == "PLAYING" ? true : false;
		
		print "<li>\n";
		if( count($c->members) > 1 )
		{
			$cb++;
			echo '<input type="checkbox" checked="checked" id="c' . $cb .'" />';
			echo '<label class="tree_label" for="c'.$cb.'">' . $c->coordinator->roomName . "</label>" . getPlayText($c);
			echo $playing ? "<button value='1' id='toggleSonos' onclick='toggleSonos(" . getSonosUrl( $config['host'], false, $c->coordinator->roomName ) . ");' class=button>Playing</button>" : 
						    "<button value='1' id='toggleSonos' onclick='toggleSonos(" . getSonosUrl( $config['host'], true, $c->coordinator->roomName ) . ");' class=button_stopped>Stopped</button>";
			echo '<ul>';
			foreach ( $c->members as $key=> $m )
			{
				if( $m->uuid != $m->coordinator )
				{
					echo '<li><span class="tree_label">' . $m->roomName . '</span></li>';
				}
			}
			echo '</ul>';

		}
		else
		{
			echo '<span class="tree_label">' . $c->coordinator->roomName . getPlayText($c) . '</span>';
			echo $playing ? "<button value='1' id='toggleSonos' onclick='toggleSonos(" . getSonosUrl( $config['host'], false, $c->coordinator->roomName ) . ");' class=button>Playing</button>" : 
						    "<button value='1' id='toggleSonos' onclick='toggleSonos(" . getSonosUrl( $config['host'], true, $c->coordinator->roomName ) . ");' class=button_stopped>Stopped</button>";
		}
		echo "</li>\n";
	}
	echo "</ul>\n";
?>

    </body>
</html>

<script>
function toggleSonos( url, play, zone )
{
	var setUrl = window.location.href + "?setstate=";
	setUrl += play ? "1" : "0";
	
	console.log( setUrl );
	getUrl( setUrl, function( resp )
	{
		console.log( resp );
		
		getUrl( url, function(resp) 
		{
			console.log( resp );
			waitForState( play, zone );
		});
	});
}

function waitForState( play, zone )
{
	getUrl( window.location.href + "?getstate=" + zone, function( resp )
	{
		var state = JSON.parse(resp);
		console.log(state);
		if( state.playing == play )
		{
			location.reload(true);
		}
		else
		{
			setTimeout( function() { waitForState( play, zone ); }, 500 );
		}
	});
}


function getUrl(url, callback)
{
    var xhttp = new XMLHttpRequest();

    xhttp.open("GET", url, true);
    xhttp.onreadystatechange = function ()
    {
        if(xhttp.readyState === 4)
        {
            if(xhttp.status === 200 || xhttp.status == 0)
            {
                var response = xhttp.responseText;
                callback( response );
            }
        }
    }
    xhttp.send(null);
}
</script>


<?php
function getPlayText($coord)
{
	$ret = "<button class='playText'>";
	if( $coord->coordinator->state->currentTrack->type == "line_in" )
	{
		$ret .= 'TV';
	}
	else if( $coord->coordinator->state->playbackState == "STOPPED" )
	{
		$ret .= 'None';
	}
	else
	{
		$ret .= '<img style="max-height: 25px; max-width: 25px; float: left; margin-left: 5%" src="' . $coord->coordinator->state->currentTrack->absoluteAlbumArtUri . '">'. $coord->coordinator->state->currentTrack->title . ' on ' . $coord->coordinator->state->currentTrack->stationName;
	}
	
	$ret .= "</button>";
	
	return $ret;
}

function getSonosUrl($host,$play,$zone)
{
	$ret = "\"" . $host . "/" . $zone;
	
	if( $play )
	{
		$ret .= "/play\"";
	}
	else
	{
		$ret .= "/pause\"";
	}
	
	$ret .= $play ? ", true" : ", false";
	$ret .= ", \"$zone\"";
	
	return $ret;
}
?>
