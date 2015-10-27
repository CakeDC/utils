<?php echo __d('utils', 'An Error happened'); ?>:


<?php echo __d('utils', 'Code: %s', $code); ?>

<?php echo __d('utils', 'File: %s', $file); ?>

<?php echo __d('utils', 'Line: %d', $line); ?>


<?php echo __d('utils', 'Description'); ?>:

<?php echo $description; ?>


<?php echo __d('utils', 'Context'); ?>:

<?php print_r($context); ?>


<?php echo __d('utils', 'Trace'); ?>:

<?php echo $trace; ?>


<?php echo __d('utils', 'Session Data'); ?>:

<?php print_r($session); ?>


<?php echo __d('utils', 'Server Data'); ?>:

<?php print_r($server); ?>


<?php echo __d('utils', 'Request Data'); ?>:

<?php print_r($request); ?>

