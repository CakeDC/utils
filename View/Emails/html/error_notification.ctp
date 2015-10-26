<h1><?php echo __d('utils', 'An Error happened'); ?>:</h1>

<br />

<?php echo __d('utils', 'Code: %s', h($code)); ?><br />
<?php echo __d('utils', 'File: %s', h($file)); ?><br />
<?php echo __d('utils', 'Line: %d', h($line)); ?><br />

<hr><br />

<h2> <?php echo __d('utils', 'Description'); ?>:</h2>
<?php echo $description; ?>

<hr><br />

<h2><?php echo __d('utils', 'Context'); ?>:</h2>
<?php print_r($context); ?>

<hr><br />

<h2><?php echo __d('utils', 'Trace'); ?>:</h2>
<pre>
	<?php echo $trace; ?>
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