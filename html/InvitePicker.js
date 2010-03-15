var inviteDivID = "notifypicker";
var mainFieldName;

function displayInviteList(field, selectlist){
	mainFieldName = field;
	
	var element = document.getElementsByName (mainFieldName).item(0);

	var x = element.offsetLeft;
	var y = element.offsetTop + element.offsetHeight ;

	// deal with elements inside tables and such
	var parent = element;
	while (parent.offsetParent) {
		parent = parent.offsetParent;
		x += parent.offsetLeft;
		y += parent.offsetTop;
	}

	if (!document.getElementById(inviteDivID)) {
		var newNode = document.createElement("div");
		newNode.setAttribute("id", inviteDivID);
		newNode.setAttribute("class", "dpDiv");
		newNode.setAttribute("style", "visibility: hidden;");
		document.body.appendChild(newNode);
	}

	var pickerDiv = document.getElementById(inviteDivID);
	pickerDiv.style.position = "absolute";
	pickerDiv.style.left = x + "px";
	pickerDiv.style.top = y + "px";
	pickerDiv.style.visibility = (pickerDiv.style.visibility == "visible" ? "hidden" : "visible");
	pickerDiv.style.display = (pickerDiv.style.display == "block" ? "none" : "block");
	pickerDiv.style.zIndex = 10000;	
	
	html = "<table border=0 cellpadding=0 cellspacing=0 >"
	+ "<th bgcolor=gray align=left >&nbsp;Email notify to:</th>"
	+ "<tr><td colspan=2>" + selectlist + "</td></tr>"
	+ "<tr><td bgcolorx=gray align=right><a href='#' onclick=\"hideInvitePicker()\">(close)&nbsp;</a></td></tr>"
	+ "</table>";
	
	document.getElementById(inviteDivID).innerHTML = html;
}

function selectedListItem(){
	main = document.getElementsByName (mainFieldName).item(0);

	var pickerDiv = document.getElementById(inviteDivID);
	
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
	
	var dropdownIndex = document.getElementById('selectNotify').selectedIndex;
	if(dropdownIndex > -1){
		var dropdownValue = document.getElementById('selectNotify')[dropdownIndex].text;
		main.value += dropdownValue + "\n";
	}
	
	main.focus();
}

function hideInvitePicker(){
	var pickerDiv = document.getElementById(inviteDivID);
	if(pickerDiv){
		pickerDiv.style.visibility = "hidden";
		pickerDiv.style.display = "none";
	}
}








