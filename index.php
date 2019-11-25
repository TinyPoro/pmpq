<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	<meta name="description" content="">
	<meta name="author" content="">

	<title>TPBank</title>

	<!-- Bootstrap core CSS -->
	<link href="css/bootstrap.min.css" rel="stylesheet">

	<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
	<link href="css/ie10-viewport-bug-workaround.css" rel="stylesheet">

	<!-- Custom styles for this template -->
	<link href="css/tpbank.css?v=124" rel="stylesheet">

	<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->
</head>

<body>

<div class="container">

	<div class="page-content">

		<form method="post" target="my-iframe" action="step2.php?task=upload" enctype="multipart/form-data">
			<div class="form-group row">
				<label class="col-md-2">Type</label>
				<div class="col-md-2">
					<input type="radio" name="funcType" id="step2" value="step2.php?task=upload" checked="checked">
					<label for="step2">USER >< MATRIX</label>
				</div>
				<div class="col-md-2">
					<input type="radio" name="funcType" id="rolefcc" value="rolefcc_step2.php?task=upload"> <label
						for="rolefcc">ROLE >< MATRIX</label>
				</div>

				<div class="col-md-1">
					<input type="radio" name="funcType" id="crs" value="crs.php?task=upload"> <label
						for="crs">CRS</label>
				</div>

				<div class="col-md-1">
					<input type="radio" name="funcType" id="crs" value="vista.php?task=upload"> <label for="crs">Vista</label>
				</div>

                <div class="col-md-1">
                    <input type="radio" name="funcType" id="diff" value="diff.php?task=upload"> <label for="diff">Diff</label>
                </div>
			</div>
			<div class="form-group row">
				<label for="inpFile" class="col-md-2">Upload file</label>
				<div class="col-md-2">
					<input type="file" id="inpFile"
					       accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" name="inpFile">
				</div>
				<div class="col-md-2">
					<button type="submit" class="btn btn-default">Upload</button>
				</div>
			</div>
		</form>

		<iframe name="my-iframe" id="my-iframe" style="">

		</iframe>
	</div>

</div><!-- /.container -->


<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery.min.js"><\/script>')</script>
<script src="js/bootstrap.min.js"></script>
<!-- IE10 viewport hack for Surface/desktop Windows 8 bug -->
<script src="js/ie10-viewport-bug-workaround.js"></script>

<script>
	function setFormAction() {
		var act = $('input[name=funcType]:checked').val();
		console.log(act);
		$('form').attr('action', act);
	}
	$(document).ready(function () {
		setFormAction();
		$('input[name="funcType"]').change(setFormAction);
	});
</script>
</body>
</html>
