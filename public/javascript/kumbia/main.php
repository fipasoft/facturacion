$Kumbia = new Object(); 
$Kumbia.path = "<?= urldecode($_REQUEST['path']) ?>"; 
$Kumbia.controller = "<?= $_REQUEST['controller'] ?>";
$Kumbia.action = "<?= $_REQUEST['action'] ?>";
$Kumbia.id = "<?= $_REQUEST['id'] ?>";