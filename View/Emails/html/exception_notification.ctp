<h1><?php echo __d('utils', 'An Exception happened'); ?>:</h1>

<br />

<h2><?php echo __d('utils', 'Message'); ?>:</h2>
<pre>
<?php echo $message; ?>
</pre>

<hr><br />

<h2><?php echo __d('utils', 'Session Data'); ?>:</h2>
<pre>
<?php print_r($session); ?>
</pre>

<hr><br />

<h2><?php echo __d('utils', 'Server Data'); ?>:</h2>
<pre>
<?php print_r($server); ?>
</pre>

<hr><br />

<h2><?php echo __d('utils', 'Request Data'); ?>:</h2>
<pre>
<?php print_r($request); ?>
</pre>