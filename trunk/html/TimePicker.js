var timePickerDivID = "timepicker";
var mainFieldName;
var refFieldName;
var fieldTime='';

function selectTime(main, ref){
	mainFieldName = main;
	refFieldName = ref;
	
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
	
	document.getElementById(timePickerDivID).innerHTML = buildTimeSelect();
}

function buildTimeSelect(){

	html = "<SELECT id='selectTime' name='selectTime' size=8 onClick='selectedTime()'>";
	
	hour = 0;
	hourVal = 12;
	minVal = '00';
	am_pm = 'AM';
	
	bValidTime = false;
	
	arr1 = fieldTime.split(":");
	if(arr1[1]){
		setHour = arr1[0];
		
		arr2 = arr1[1].split(" ");
		
		if(arr2[1]){
			setMin = arr2[0];
			setAmPm = arr2[1];
			
			bValidTime = true;// if we're here, then there was a valid HH:MM AM/PM time
		}
	}
	
	
	while (hour <= 23){	
		selected= "";
	
		html += "<OPTION>" + hourVal + ":" + minVal + " " + am_pm + "</OPTION>";			
		
		if(bValidTime){
			if( (setHour == hourVal ) && (setMin >= minVal) && (setAmPm == am_pm) ) {
				if( setMin <= (parseInt(minVal)+30) ){
					selected = " selected=true ";					
					html += "<OPTION" + selected + ">" + setHour + ":" + setMin + " " + setAmPm + "</OPTION>";	
					//debugger;
				}
			}
		}
			
		if(minVal == '30'){
			hour++;
			hourVal = (hour > 12 ? (hour-12) : hour);
			minVal = '00';
			am_pm = (hour >= 12 ? 'PM' : 'AM');
		}
		else{
			minVal = '30';
		}	
	}

	html += "</SELECT>";
	
	return html;
}

function selectedTime(){
	main = document.getElementsByName (mainFieldName).item(0);
	ref = document.getElementsByName (refFieldName).item(0);

	var pickerDiv = document.getElementById(timePickerDivID);
	
	pickerDiv.style.visibility = "hidden";
	pickerDiv.style.display = "none";
	
	var dropdownIndex = document.getElementById('selectTime').selectedIndex;
	var dropdownValue = document.getElementById('selectTime')[dropdownIndex].text;

	main.value = dropdownValue;
	
	if(ref){
		ref.value = dropdownValue;	
	}
		
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
//debugger;	
	if(allday.checked){
		field1.disabled=false;
		field2.disabled=false;	
	}
	else{
		field1.disabled=true;
		field2.disabled=true;		
	}
}












