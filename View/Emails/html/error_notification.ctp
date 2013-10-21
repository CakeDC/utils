<h1><?php echo __('utils', 'An Error happened'); ?>:</h1>

<br />

<?php echo __('utils', 'Code: %s', h($code)); ?><br />
<?php echo __('utils', 'File: %s', h($file)); ?><br />
<?php echo __('utils', 'Line: %d', h($line)); ?><br />

<hr><br />

<h2> <?php echo __('utils', 'Description'); ?>:</h2>
<?php echo $description; ?>

<hr><br />

<h2><?php echo __('utils', 'Context'); ?>:</h2>
<?php echo $context; ?>

<hr><br />

<h2><?php echo __('utils', 'Trace'); ?>:</h2>
<pre>
	<?php echo $trace; ?>
</pre>

<hr><br />

<h2><?php echo __('utils', 'Session Data'); ?>:</h2>
<pre>
	<?php echo print_r($session); ?>
</pre>

<hr><br />

<h2><?php echo __('utils', 'Server Data'); ?>:</h2>
<pre>
	<?php echo print_r($server); ?>
</pre>

<hr><br />

<h2><?php echo __('utils', 'Request Data'); ?>:</h2>
<pre>
	<?php echo print_r($request); ?>
</pre>