
<?php
$content =  $report->getContent();
$parser = new Templateparser();
$content = $parser->parse($application->getId(),$application->getFormId(), $application->getEntryId(), $content);
echo html_entity_decode($content);
?>

<script language="javascript">
   window.print();
</script>