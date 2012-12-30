<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Contribution Statement Generator</title>
</head>

<body>

<p>Please enter the person_id of the individual you want to run a contribution report for: </p>
<form id="form1" name="form1" method="post" action="/cstatements_proc">
<lable for="action">Action:</label>
<select name="action" id="action">
	<option value="F">Add to Database</option>
    <option value="D">Download</option>
</select><br />
<label for="person_id">Person ID:</label>
<input type="text" name="person_id" id="person_id" /><br />
<label for="year">Year</label>
<select name="year" id="year">
	<?php
	for($i=(date('Y')-5);$i<=date('Y'); $i++){
		echo("<option value=$i>$i</option>");	
	}
	?>
</select><br />
<label for="debug">Output SQL Only?</label>
<input type="checkbox" name="debug" id="debug" /><br />
<label for="runNum">Run Number:</label>
<input type="input" name="runNum" id="runNum" /><br />
<input name="Submit" type="submit" value="Submit" />

</form>



<p>Remember that you will need to attach the resulting contribution statement to all adults in the family.</p>
</body>
</html>
