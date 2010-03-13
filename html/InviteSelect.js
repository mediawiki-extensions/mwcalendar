var timePickerDivID = "notifypicker";
var mainFieldName;
var refFieldName;
var fieldTime='';

function displayInviteList(field, selectlist){
	mainFieldName = field;
	//refFieldName = ref;
	
	var element = document.getElementsByName (mainFieldName).item(0);
	fieldTime = element.value.toUpperCase();

	var x = element.offsetLeft;
	var y = element.offsetTop + element.offsetHeight ;

	// deal with elements inside tables and such
	var parent = element;
	while (parent.offsetParent) {
		parent = parent.offsetParent;
		x += parent.offsetLeft;
		y += parent.offsetTop;
	}
	
	// the datepicker table will be drawn inside of a <div> with an ID defined by the
	// global timePickerDivID variable. If such a div doesn't yet exist on the HTML
	// document we're working with, add one.
	if (!document.getElementById(timePickerDivID)) {
		// don't use innerHTML to update the body, because it can cause global variables
		// that are currently pointing to objects on the page to have bad references
		//document.body.innerHTML += "<div id='" + timePickerDivID + "' class='dpDiv'></div>";
		var newNode = document.createElement("div");
		newNode.setAttribute("id", timePickerDivID);
		newNode.setAttribute("class", "dpDiv");
		newNode.setAttribute("style", "visibility: hidden;");
		document.body.appendChild(newNode);
	}

	// move the datepicker div to the proper x,y coordinate and toggle the visiblity
	var pickerDiv = document.getElementById(timePickerDivID);
	pickerDiv.style.position = "absolute";
	pickerDiv.style.left = x + "px";
	pickerDiv.style.top = y + "px";
	pickerDiv.style.visibility = (pickerDiv.style.visibility == "visible" ? "hidden" : "visible");
	pickerDiv.style.display = (pickerDiv.style.display == "block" ? "none" : "block");
	pickerDiv.style.zIndex = 10000;	
	
	html = "<table border=1 cellpadding=0 cellspacing=0 ><th bgcolor=gray align=left >&nbsp;Email notify to:</th><tr><td>" + selectlist + "</td></tr></table>";
	
	document.getElementById(timePickerDivID).innerHTML = html;//buildTimeSelect();
}

function selectedNotify(){
	main = document.getElementsByName (mainFieldName).item(0);

	var pickerDiv = document.getElementById(timePickerDivID);
	
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
	
	var dropdownIndex = document.getElementById('selectNotify').selectedIndex;
	var dropdownValue = document.getElementById('selectNotify')[dropdownIndex].text;

	main.value += dropdownValue + "\n";
	
	main.focus();
}

function hidePicker(){
	//main = document.getElementsByName (field).item(0);
	var pickerDiv = document.getElementById(timePickerDivID);
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
}

function setTimeFields(allDayChk,time1,time2){
	allday = document.getElementsByName (allDayChk).item(0);
	field1 = document.getElementsByName (time1).item(0);
	field2 = document.getElementsByName (time2).item(0);

	if(allday.checked){
		field1.disabled=false;
		field2.disabled=false;	
	}
	else{
		field1.disabled=true;
		field2.disabled=true;		
	}
}












