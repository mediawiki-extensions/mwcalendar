var ModalDialogWindow;
var ModalDialogInterval;
var ModalDialog = new Object;

ModalDialog.value = '';
ModalDialog.eventhandler = '';
ModalDialog.targetField = '';
 

function ModalDialogMaintainFocus()
{
  try
  {
    if (ModalDialogWindow.closed)
     {
        window.clearInterval(ModalDialogInterval);
        eval(ModalDialog.eventhandler);       
        return;
     }
    //ModalDialogWindow.focus(); 
  }
  catch (everything) {   }
}
        
 function ModalDialogRemoveWatch()
 {
    ModalDialog.value = '';
    ModalDialog.eventhandler = '';
 }
        
 function ModalDialogShow(Title,BodyText)
 {

   ModalDialogRemoveWatch();
   ModalDialog.eventhandler = "handler()";
/*
   var args='width=350,height=350,left=325,top=300,toolbar=0,';
       args+='location=0,status=0,menubar=0,scrollbars=1,resizable=0';  
	   */
   var args='width=350,height=350,toolbar=0,';
       args+='location=0,status=0,menubar=0,scrollbars=1,resizable=0'; 

   ModalDialogWindow=window.open("","",args); 
   ModalDialogWindow.document.open(); 
   ModalDialogWindow.document.write('<html>');
   ModalDialogWindow.document.write('<head>'); 
   ModalDialogWindow.document.write('<title>' + Title + '</title>');
   ModalDialogWindow.document.write('<script' + ' language=JavaScript>');
   ModalDialogWindow.document.write('function CloseForm(Response) ');
   ModalDialogWindow.document.write('{ ');
   ModalDialogWindow.document.write('var index = document.frm_selected.selected.selectedIndex;');  
   ModalDialogWindow.document.write('var selected = document.frm_selected.selected.options[index].text;'); 
   ModalDialogWindow.document.write(' window.opener.ModalDialog.value = selected; ');
   ModalDialogWindow.document.write(' window.close(); ');
   ModalDialogWindow.document.write('} ');
   ModalDialogWindow.document.write('</script' + '>');        
   ModalDialogWindow.document.write('</head>');   
   ModalDialogWindow.document.write('<body>');
   ModalDialogWindow.document.write('<table border=0 width="95%" align=center cellspacing=0 cellpadding=2>');
   ModalDialogWindow.document.write('<tr><td align=center>' + BodyText + '</td></tr>');
   ModalDialogWindow.document.write('<tr><td align=center><input type=button value=&nbsp;add&nbsp; name=inviteok onClick=javascript:CloseForm("Me!") /></td></tr>');
   ModalDialogWindow.document.write('</table>');
   ModalDialogWindow.document.write('</body>');
   ModalDialogWindow.document.write('</html>'); 
   ModalDialogWindow.document.close(); 
   ModalDialogWindow.focus(); 
  ModalDialogInterval = window.setInterval("ModalDialogMaintainFocus()",5);

 }

  function displayInviteList(BodyText, field)
  {
	 ModalDialog.targetField = field;
     ModalDialogShow("Choose invites",BodyText);
  }

 function handler()
 {
   value = document.getElementById(ModalDialog.targetField).value;
   
   if(ModalDialog.value == "") { return; }
   
   document.getElementById(ModalDialog.targetField).value = value + ModalDialog.value + ",";
   ModalDialogRemoveWatch();
 }
 
 
 
 
 
 
 
 
 
 
 
 
 

