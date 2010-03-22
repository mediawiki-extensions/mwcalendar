var summaryDivID = "eventsummary";
var _posX;
var _posY;

function EventSummary(ctrl,event,comment){
	var element = document.getElementsByName (ctrl).item(0);

/* 	 x = event.clientX + document.documentElement.scrollLeft +15;
	 y = event.clientY + document.documentElement.scrollTop; */

	 x = _posX + document.documentElement.scrollLeft +15;
	 y = _posY + document.documentElement.scrollTop;	 
	 
	if (!document.getElementById(summaryDivID)) {
		var newNode = document.createElement("div");
		newNode.setAttribute("id", summaryDivID);
		newNode.setAttribute("class", "dpDiv");
		newNode.setAttribute("style", "visibility: hidden;");
		document.body.appendChild(newNode);
	}

	// move the datepicker div to the proper x,y coordinate and toggle the visiblity
	var pickerDiv = document.getElementById(summaryDivID);
	pickerDiv.style.position = "absolute";
	pickerDiv.style.left = x + "px";
	pickerDiv.style.top = y + "px";
	pickerDiv.style.visibility = (pickerDiv.style.visibility == "visible" ? "hidden" : "visible");
	pickerDiv.style.display = (pickerDiv.style.display == "block" ? "none" : "block");
	pickerDiv.style.zIndex = 10000;	
	
	html = "<table border=1 bordercolor=#808080 cellpadding=0 cellspacing=0><td><table width=125px border=0 cellpadding=2 cellspacing=0><th nowrapx bgcolor=#C0C0C0 align=left >" + event + "</th><tr><td>" + comment + "</td></tr>";
	html += "</table></td></table>";
	
	document.getElementById(summaryDivID).innerHTML = html;
}

function ClearEventSummary(){
	var pickerDiv = document.getElementById(summaryDivID);
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
}

document.onmousemove = function(evt) {
	if (typeof evt == 'undefined') { 
		myEvent = window.event; 
	} else {
		myEvent = evt;
	} 
	_posX = myEvent.clientX;
	_posY = myEvent.clientY;
}
