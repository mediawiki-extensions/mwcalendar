<!--DAILY BEGIN-->
<?php
echo $_POST['group'];
?>


<form name="myform" method="post">
<input type=radio name=group value=Daily onclick="javascript:this.form.submit()" />Daily<br>
<input type=radio name=group value=Weekly onclick="javascript:this.form.submit()"/>Weekly<br>
<input type=radio name=group value=Yearly onclick="javascript:this.form.submit()"/>Yearly<br>
</form>

<!--DAILY BEGIN-->