<?php
$appName = 'My Protected Application';
$logFile = '/tmp/shib-access-error.log';
$contactEmail = 'admin@example.cz';

$serverVars = $_SERVER;
$timeString = date('c', time());
$uniqueId = uniqid();

$msg = sprintf("%s [%s] Access Error:\n-----\n", $timeString, $uniqueId);
foreach ($serverVars as $key => $value) {
    $msg .= sprintf("    [%s] --> [%s]\n", $key, $value);
}
$msg .= "-----\n";

if (file_put_contents($logFile, $msg, FILE_APPEND) === false) {
    error_log(sprintf("Cannot write to file '%s'", $logFile));
}

?><!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css" />
<title>Authorization Failed</title>
</head>

<body>


    <nav class="navbar navbar-default" role="navigation">

        <div class="navbar-header">
            <a class="navbar-brand" href="/"><?php echo $appName; ?></a>

            <!-- Collapsed menu -->
            <button type="button" data-toggle="collapse" data-target=".navbar-collapse" class="navbar-toggle pull-left"
                style="padding-top: 4px; padding-bottom: 4px;">
                <span style="display: inline-block; margin-top: 5px;"> <span class="icon-bar"></span> <span class="icon-bar"></span> <span
                    class="icon-bar"></span>
                </span> <span style="display: inline-block; padding-top: 2px; vertical-align: top;"> &nbsp;Menu </span>
            </button>
        </div>
    </nav>
    
<div class="container">
    <h1>Access error</h1>

    <p>
    Thank you for helping us diagnose the problem.
    </p>
    <p>The error has been assigned a reference string <code><?php echo $uniqueId; ?></code>.
    Please send this reference in an email to <a href="mailto:<?php echo $contactEmail; ?>?Subject=<?php echo "Access error ID $uniqueId"?>"><?php echo $contactEmail?></a>.
    </p>
    <p>
    If you are interrested in logged information, you can see it after clicking the button below.
    </p>
    <pre id="info" style="display: none">
    <?php echo $msg; ?>
    </pre>
    <p>
        <a class="btn btn-danger btn-lg" onClick="showInformation()">Show information</a>
    </p>
</div>

<script>
function showInformation() {
    document.getElementById("info").style.display = 'block';
}
</script>

</body>
</html>
