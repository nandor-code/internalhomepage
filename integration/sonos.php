<?php

$config = json_decode(file_get_contents("../config/sonos.json"), true);

$set = $_GET['setstate'];
$setpre = $_GET['setpreset'];
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

if( isset($setpre) )
{
	header("Content-Type: application/json");
	
	$s = file_put_contents( "/tmp/house_preset", $setpre);

	$response = array(
   	     'success' => $s,
	    );
	
	echo json_encode($response);
	
	return;
}

if( isset($get) )
{
	header("Content-Type: application/json");
	$statereply = file_get_contents( $config['host'] . "/" . rawurlencode($get) . "/state" );
	if($statereply === FALSE) {
		$error = error_get_last();
		$response = array (
			'playing' => 'ERROR',
			'error_message' => $error['message'],
		);
		echo json_encode($response);
		die;
	}
	$json = json_decode( $statereply );
	$s = $json->playbackState == "PLAYING" || $json->playbackState == "TRANSITIONING";
	
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
	<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.js"></script>
    <link id="sonosStyle" rel='stylesheet' href='/css/sonos.css'>
	<h1 class="logo">SONOS</h1>
	<body>
	<div class='main-container'>
	<h2 class="heading">Zone Controller</h2>
<?php

	$sonosJson = json_decode( file_get_contents( $config['host'] . "/zones" ) );

	$presetsJson = json_decode( file_get_contents( $config['host'] . "/preset" ) );
	
	$currentPreset = trim( file_get_contents( "/tmp/house_preset" ) );
	
	echo "<ul class='tree'>\n";
	$cb = 1;
	echo '<li>';
	echo '<input type="checkbox" checked="checked" id="c' . $cb .'" />';
	echo '<label class="tree_label" for="c'.$cb.'">House Zones</label>';
	print "<ul>\n";
	foreach ( $sonosJson as $key => $c )
	{
		print "<li>\n";
		if( count($c->members) > 1 )
		{
			$cb++;
			echo '<input type="checkbox" checked="checked" id="c' . $cb .'" />';
			echo '<label class="tree_label" for="c'.$cb.'">' . $c->coordinator->roomName . "</label>" . getPlayText($config, $c);
			echo "<button value='0' id='toggle" . $c->coordinator->roomName . "' onclick='toggleSonos(" . getSonosUrl( $config['host'], $c->coordinator->roomName ) . ");' class=button>Playing</button>";
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
			echo '<span class="tree_label">' . $c->coordinator->roomName . '</span>' . getPlayText($config, $c);
			echo "<button value='0' id='toggle" . $c->coordinator->roomName . "' onclick='toggleSonos(" . getSonosUrl( $config['host'], $c->coordinator->roomName ) . ");' class=button>Playing</button>";
		}
		echo "</li>\n";
	}
	echo "</ul>\n";
	echo "</li>\n";
	echo "</ul>\n";

?>

	<h2 class="heading">House Music Playlist</h2>
		<div class="container">
			<ul class="list">
<?php
				$i = 1;
				foreach ( $presetsJson as $key => $p )
				{
					echo '<li class="list__item">';;
					echo '<input type="radio" onchange="setPreset(\'' . $config['host'] . '\', \'' . $p . '\');" class="radio-btn" name="choice" id="' . $i . '-opt" ' . ($p == $currentPreset ? "checked" : "" ) . '>';
					echo '<label for="' . $i . '-opt" class="label">' . $p . '</label>';
					echo '</li>';
					$i++;
				}
?>
			</ul>
		</div>
	</div>
    </body>
</html>

<script>

$(document).ready(function()
{
<?php
	foreach ( $sonosJson as $key => $c )
	{
		print "checkPlaying(\"" . $config['host'] . "\", \"" . $c->coordinator->roomName . "\", 10000);\n";
	}
?>
});

function toggleSonos( host, play, zone )
{
	//console.log("toggleSonos entered");
	//console.log(play);
	var setUrl = window.location.href + "?setstate=";
	setUrl += play;
	
	// console.log( setUrl );
	getUrl( setUrl, function( resp )
	{
		// console.log( resp );
		
		var url = host + "/" + zone + (play == 1 ?"/play":"/pause");
		// console.log(url);
		
		getUrl( url, function(resp) 
		{
			// console.log( resp );
			waitForState( host, play, zone );
		});
	});
}

function sonosNext( host, zone )
{
	// console.log("sonosNext entered");
	var url = host + "/" +  zone + "/next";
	getUrl( url, function( resp )
	{
		// console.log( resp );
		setTimeout( function() { checkPlaying( host, zone, -1 ); }, 1000 );
		
	});
}

function setPreset( host, preset )
{
	// console.log("setPreset entered");
	var setUrl = window.location.href + "?setpreset=" + preset;
	getUrl( setUrl, function( resp )
	{
		var preSetUrl = host + "/preset/" + preset;
		// console.log( resp );
		
		getUrl( preSetUrl, function( resp )
		{
			// console.log( resp );
		});
	});
}

function waitForState( host, play, zone )
{
	// console.log("waitForState entered");
	// console.log(zone);
	getUrl( window.location.href + "?getstate=" + zone, function( resp )
	{
		var state = JSON.parse(resp);
		// console.log(state);
		if( state.playing == play )
		{
			setTimeout( function() { checkPlaying( host, zone, -1 ); }, 10 );
		}
		else
		{
			setTimeout( function() { waitForState( host, play, zone ); }, 500 );
		}
	});
}

function checkPlaying( host, zone, repeatTime )
{
	// console.log("checkPlaying entered");
	var url = host + "/" + zone + "/state";
	// console.log(url);
	
	getUrl( url, function( resp )
	{
		var state = JSON.parse(resp);
		// console.log(state);
		if( repeatTime > 0 )
		{
			setTimeout( function() { checkPlaying( host, zone, repeatTime ); }, repeatTime );
		}
		
		updateSonosButton( zone, state );
	});
}

function updateSonosButton( zone, state )
{
	// console.log("updateSonosButton entered");
	var updateObj = document.getElementById(zone);
	
	if( state.currentTrack.type === "line_in" )
	{
		updateObj.innerHTML = "TV";
	}
	else if( state.playbackState === "STOPPED" || state.playbackState === "PAUSED_PLAYBACK" )
	{
		updateObj.innerHTML = "None";
	}
	else
	{
        var stationName = "";

        if( state.currentTrack.stationName.length > 0 )
        {
            stationName = " on " + state.currentTrack.stationName;
        }

		updateObj.innerHTML = "<img class=\"albumArt\" src=\"" + state.currentTrack.absoluteAlbumArtUri + "\">" + state.currentTrack.title + stationName;
	}
	
	var updateObj = document.getElementById("toggle" + zone);
	
	if( state.playbackState === "PLAYING" || state.playbackState === "TRANSITIONING" )
	{
		updateObj.innerHTML = "Playing";
		updateObj.value = 0;
		updateObj.classList.add("button");
		updateObj.classList.remove("button_stopped");
	}
	else
	{
		updateObj.innerHTML = "Stopped";
		updateObj.value = 1;
		updateObj.classList.remove("button");
		updateObj.classList.add("button_stopped");
	}
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
function getPlayText($config, $coord)
{
	$ret = "<button onclick='sonosNext(\"" . $config['host'] . "\",\"" . $coord->coordinator->roomName . "\");' id='";
	$ret .=	$coord->coordinator->roomName;
	$ret .= "' class='playText'>";
	
	$ret .= "</button>";
	
	return $ret;
}

function getSonosUrl($host,$zone)
{
	$ret = "\"" . $host . "\"";
	$ret .= ", this.value";
	$ret .= ", \"$zone\"";
	
	return $ret;
}
?>
