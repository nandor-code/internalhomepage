$(document).ready(function()
{
    // bring in custom configs
    var config = new homePageConfig();

    var table = document.getElementById("services");

    var srvs = JSON.parse(services);
    var i;
    for( i = 0; i < srvs.length; i++ )
    {
        //console.log(srvs[i].name + " " + srvs[i].url +  " " + srvs[i].icon );
        var row = table.insertRow(-1);
        row.classList.add("service-item");
        var icon = row.insertCell(-1);
        var link = row.insertCell(-1);
        icon.classList.add("service-data-img");
        icon.innerHTML = '<img class="favico" src="' + srvs[i].icon + '">';
        link.classList.add("service-data-link");
        link.innerHTML = '<A target="_blank" class="service-link" HREF="' + srvs[i].url + '">' + srvs[i].name + '</A>';
    }

    table.style.width = config.serviceWidth;

    document.title = config.title;

    var header = document.getElementById("header");
    header.innerHTML = config.title;

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
