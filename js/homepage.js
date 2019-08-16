var srvs;
var config;

$(document).ready(function()
{
    // bring in custom configs
    config = new homePageConfig();

    var table = document.getElementById("services");

    var button = document.getElementById("toggleAdv");

    buildTable( table, button );

    table.style.width = config.serviceWidth;

    document.title = config.title;

    var header = document.getElementById("header");
    header.innerHTML = config.title;

    var allRows = document.getElementsByClassName("button");
    for (var i = 0; i < allRows.length; i++) 
    {
        allRows[i].style.color = config.advTextColor;
        allRows[i].style.backgroundColor = config.advButtonColor;
    }
    var allRows = document.getElementsByClassName("head");
    for (var i = 0; i < allRows.length; i++) 
    {
        allRows[i].style.color = config.headerColor;
    }
    var allRows = document.getElementsByClassName("service-item");
    for (var i = 0; i < allRows.length; i++) 
    {
        allRows[i].style.backgroundColor = config.serviceBGColor;
        allRows[i].style.height = config.serviceHeight;
    }
    var allRows = document.getElementsByClassName("service-link");
    for (var i = 0; i < allRows.length; i++) 
    {
        allRows[i].style.color = config.serviceLinkColor;
    }
    document.body.style.backgroundImage = 'url("' + config.bgImage + '")';
    document.body.style.backgroundColor = config.bgColor;
});

function setAdvButton( button )
{
    if( config.advDefault )
    {
        toggleAdv( button );
    }
}

// 0 = Baisc
// 1 = Adv 
function toggleAdv(advButton)
{
    if( advButton.value == 1 ) // in adv mode
    {
        advButton.value = 0;
        advButton.innerHTML = "Mode: Basic";
    }
    else
    {
        advButton.value = 1;
        advButton.innerHTML = "Mode: Advanced";
    }

    var table = document.getElementById("services");
    var i;
    for( i = 0; i < table.rows.length; i++ )
    {
        if( advButton.value == 0 ) // basic mode
        {
            table.rows[i].style.visibility = srvs[i].adv ? 'collapse' : 'visible';
        }
        else
        {
            table.rows[i].style.visibility = 'visible';
        }
    }
}

function buildTable(table, advButton )
{
    getUrl(config.servicesJson, function(resp) 
    {
        srvs = JSON.parse(resp);
        var i;

		for( i = 0; i < srvs.length; i++ )
		{
			var row = table.insertRow(-1);
			row.classList.add("service-item");
			var icon = row.insertCell(-1);
			var link = row.insertCell(-1);
			icon.classList.add("service-data-img");
			icon.innerHTML = '<img class="favico" src="' + srvs[i].icon + '">';
			link.classList.add("service-data-link");
			link.innerHTML = '<A target="_blank" class="service-link" HREF="' + srvs[i].url + '">' + srvs[i].name + '</A>';
			row.value = srvs[i].name;
			table.rows[i].style.visibility = srvs[i].adv ? 'collapse' : 'visible';
		}
    
        addListeners();

        setAdvButton( advButton );
    });

}

function addListeners()
{
    var button = document.getElementById("toggleAdv");

    button.addEventListener('mouseenter', e => {
        //console.log( "mousein" );
        button.style.backgroundColor = config.advHighlightColor;
    });

    button.addEventListener('mouseleave', e => {
        //console.log( "mouseout" );
        button.style.backgroundColor = config.advButtonColor;
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
