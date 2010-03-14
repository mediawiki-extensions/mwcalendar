var pickerDivID = "notifypicker";
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

	if (!document.getElementById(pickerDivID)) {
		var newNode = document.createElement("div");
		newNode.setAttribute("id", pickerDivID);
		newNode.setAttribute("class", "dpDiv");
		newNode.setAttribute("style", "visibility: hidden;");
		document.body.appendChild(newNode);
	}

	var pickerDiv = document.getElementById(pickerDivID);
	pickerDiv.style.position = "absolute";
	pickerDiv.style.left = x + "px";
	pickerDiv.style.top = y + "px";
	pickerDiv.style.visibility = (pickerDiv.style.visibility == "visible" ? "hidden" : "visible");
	pickerDiv.style.display = (pickerDiv.style.display == "block" ? "none" : "block");
	pickerDiv.style.zIndex = 10000;	
	
	html = "<table border=1 cellpadding=0 cellspacing=0 ><th bgcolor=gray align=left >&nbsp;Email notify to:</th><tr><td>" + selectlist + "</td></tr></table>";
	
	document.getElementById(pickerDivID).innerHTML = html;
}

function selectedListItem(){
	main = document.getElementsByName (mainFieldName).item(0);

	var pickerDiv = document.getElementById(pickerDivID);
	
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
	
	var dropdownIndex = document.getElementById('selectNotify').selectedIndex;
	var dropdownValue = document.getElementById('selectNotify')[dropdownIndex].text;

	main.value += dropdownValue + "\n";
	
	main.focus();
}

function hidePicker(){
	var pickerDiv = document.getElementById(pickerDivID);
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
}












